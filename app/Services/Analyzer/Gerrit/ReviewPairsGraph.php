<?php

namespace App\Services\Analyzer\Gerrit;

use App\Project;
use App\Services\Analyzer\StringTitle;

class ReviewPairsGraph extends AbstractAnalyzer
{
    use StringTitle;

    public function __toString()
    {
        return 'Pary sprawdzające - graf';
    }

    protected function decode($result)
    {
        return json_decode(substr($result, 4));
    }
    
    public function analyze(Project $project, $from, $to)
    {
        //echo "echo from ReviewPairsGraph";exit;
        //$this->collectDataForReview($project, $from, $to);

        $result = \App\Commit::where('project', $project->getAttribute('name'))
                            ->where('updated', '>=', $from)
                            ->where('updated', '<=', $to)->get();

        $results = [];
        $nodes = [];
        $edges = [];

        foreach ($result as $commit) {
            if (!in_array($commit->owner->_account_id, $nodes)) {
                $nodes[] = $commit->owner->_account_id;
                $edges[$commit->owner->_account_id] = [];
                $results[] = [
                    'group' => 'nodes',
                    'data' => [
                        'id' => 'N'.$commit->owner->_account_id,
                        'label' => $commit->owner->name,
                        'image' => $commit->owner->avatars->first()->url,
                    ],
                ];
            }

            foreach ($commit->revisions as $revision) {
                $comments = $revision->comments;

                if (empty($comments)) {
                    $codeReviews = $commit->codeReviews;
                    foreach ($codeReviews as $codeReview) {
                        $reviewer = $codeReview->reviewer;
                        //print_r($reviewer);echo "<br/><br/><br/>";
                        if (!isset($edges[$commit->owner->_account_id][$reviewer->_account_id])) {
                            $edges[$commit->owner->_account_id][$reviewer->_account_id] = [];
                        }

                        $edges[$commit->owner->_account_id][$reviewer->_account_id][] = $commit->_number;   
                    }
                }

                foreach ($comments as $message) {
                    // Skip themselves
                    if ($message->author->_account_id == $commit->owner->_account_id) {
                        continue;
                    }

                    if (!isset($edges[$commit->owner->_account_id][$message->author->_account_id])) {
                        $edges[$commit->owner->_account_id][$message->author->_account_id] = [];
                    }

                    $edges[$commit->owner->_account_id][$message->author->_account_id][] = $commit->_number;
                }
            }
        }

        foreach ($edges as $source => $targets) {
            foreach ($targets as $target => $commits) {
                $results[] = [
                    'group' => 'edges',
                    'data' => [
                        'id' => 'E'.$source.'_'.$target,
                        'weight' => count(array_unique($commits)),
                        'source' => 'N'.$source,
                        'target' => 'N'.$target,
                    ],
                ];
            }
        }

        //sprint_r($results);exit;
        return $results;
    }

    public function analyze_old(Project $project, $from, $to)
    {
        //echo "echo from ReviewPairsGraph";exit;
        $this->collectDataForReview($project, $from, $to);

        $uri = '/a/changes/?q=project:'.$project->getAttribute('name');
        $uri .= ' -is:draft ((status:merged)OR(status:open))';
        $uri .= ' after:'.$from.' before:'.$to;
        $uri .= '&o=ALL_REVISIONS&o=DETAILED_ACCOUNTS&o=LABELS';

        $result = $this->fetch($project, $uri);
        $results = [];
        $nodes = [];
        $edges = [];

        foreach ($result as $commit) {
            if (!in_array($commit->owner->_account_id, $nodes)) {
                $nodes[] = $commit->owner->_account_id;
                $edges[$commit->owner->_account_id] = [];
                $results[] = [
                    'group' => 'nodes',
                    'data' => [
                        'id' => 'N'.$commit->owner->_account_id,
                        'label' => $commit->owner->name,
                        'image' => current($commit->owner->avatars)->url,
                    ],
                ];
            }

            foreach ($commit->revisions as $revision => $data) {
                $uri = '/a/changes/'.$commit->id.'/revisions/'.$revision.'/comments/';
                $comments = (array)$this->fetch($project, $uri);

                if (empty($comments)) {
                    $codeReview = $commit->labels->{'Code-Review'};
                    foreach ($codeReview as $reviewer) {
                    if ($reviewer instanceof \stdClass) {
                        print_r($reviewer);echo "<br/><br/><br/>";
                            if (!isset($edges[$commit->owner->_account_id][$reviewer->_account_id])) {
                                $edges[$commit->owner->_account_id][$reviewer->_account_id] = [];
                            }

                            $edges[$commit->owner->_account_id][$reviewer->_account_id][] = $commit->_number;
                        }
                    }
                }

                foreach ($comments as $comment) {
                    foreach ($comment as $message) {
                        // Skip themselves
                        if ($message->author->_account_id == $commit->owner->_account_id) {
                            continue;
                        }

                        if (!isset($edges[$commit->owner->_account_id][$message->author->_account_id])) {
                            $edges[$commit->owner->_account_id][$message->author->_account_id] = [];
                        }

                        $edges[$commit->owner->_account_id][$message->author->_account_id][] = $commit->_number;
                    }
                }
            }
        }

        foreach ($edges as $source => $targets) {
            foreach ($targets as $target => $commits) {
                $results[] = [
                    'group' => 'edges',
                    'data' => [
                        'id' => 'E'.$source.'_'.$target,
                        'weight' => count(array_unique($commits)),
                        'source' => 'N'.$source,
                        'target' => 'N'.$target,
                    ],
                ];
            }
        }

        print_r($results);exit;
        return $results;
    }

    public function getResults($results, Project $project)
    {
        return view('review.gerrit.review_pairs._graph', ['results' => $results]);
    }

    public function getContent($result, Project $project)
    {
        return '';
    }
}
