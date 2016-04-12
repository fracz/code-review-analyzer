<?php

namespace App\Services\Analyzer\Gerrit;

use App\Project;
use App\Services\Analyzer\StringTitle;

class ReviewPairs extends AbstractAnalyzer
{
    use StringTitle;

    public function __toString()
    {
        return 'Pary sprawdzające';
    }

    protected function decode($result)
    {
        return json_decode(substr($result, 4));
    }
    
    public function analyze(Project $project, $from, $to)
    {
        //echo "echo from ReviewPairs";exit;
        //$this->collectDataForReview($project, $from, $to);
            
        $result = \App\Commit::where('project', $project->getAttribute('name'))
                            ->where('updated', '>=', $from)
                            ->where('updated', '<=', $to)->get();

        $results = [];

        foreach ($result as $commit) {
            if (!isset($results[$commit->owner->_account_id])) {
                    $results[$commit->owner->_account_id] = [
                        'username' => $commit->owner->username,
                        'name' => $commit->owner->name,
                        'email' => $commit->owner->email,
                        'avatar' => (object) ['url' => $commit->owner->avatars->first()->url, 
                                                'height' => $commit->owner->avatars->first()->height],
                        'count' => 0,
                        'commits' => 0,
                        'pairs' => [],
                    ];
            }

            foreach ($commit->revisions as $revision) {
                $comments = $revision->comments;

                foreach ($comments as $message) {
					
					if($message->updated > $from){
						// Skip themselves
						if ($message->author->_account_id == $commit->owner->_account_id) {
							continue;
						}

						if (!isset($results[$commit->owner->_account_id]['pairs'][$message->author->_account_id])) {
							$results[$commit->owner->_account_id]['pairs'][$message->author->_account_id] = [
								'count' => 0,
								'username' => $message->author->username,
								'name' => $message->author->name,
								'email' => $message->author->email,
								'commits' => [],
							];
						}

						$results[$commit->owner->_account_id]['pairs'][$message->author->_account_id]['count'] += 1;

						if (!isset($results[$commit->owner->_account_id]['pairs'][$message->author->_account_id]['commits'][$commit->_number])) {
							$results[$commit->owner->_account_id]['pairs'][$message->author->_account_id]['commits'][$commit->_number] = [
								'subject' => $commit->subject,
								'revisions' => [],
							];
						}

						$results[$commit->owner->_account_id]['pairs'][$message->author->_account_id]['commits'][$commit->_number]['revisions'][] = $revision->_number;
					}
                }
            }
        }

        foreach ($results as &$result) {
            $result['pairs'] = array_filter($result['pairs'], function($item){
                return $item['count'] > 0;
            });

            $result['count'] = count($result['pairs']);

            usort($result['pairs'], function ($a, $b){
                return $b['count'] - $a['count'];
            });

            foreach ($result['pairs'] as &$pair) {
                $result['commits'] += count($pair['commits']);

                foreach ($pair['commits'] as &$commit) {
                    $commit['revisions'] = array_unique($commit['revisions']);
                }
            }
        }

        $results = array_filter($results, function($item){
            return $item['count'] > 0;
        });

        usort($results, function($a, $b){
            return $b['count'] - $a['count'];
        });

        //print_r($results);exit;
        return $results;
    }


    public function analyze_old(Project $project, $from, $to)
    {
        echo "echo from ReviewPairs";exit;
        $this->collectDataForReview($project, $from, $to);

        $uri = '/a/changes/?q=project:'.$project->getAttribute('name');
        $uri .= ' -is:draft ((status:merged)OR(status:open))';
        $uri .= ' after:'.$from.' before:'.$to;
        $uri .= '&o=ALL_REVISIONS&o=DETAILED_ACCOUNTS&o=LABELS';

        $result = $this->fetch($project, $uri);
        $results = [];

        foreach ($result as $commit) {
            if (!isset($results[$commit->owner->_account_id])) {
                    $results[$commit->owner->_account_id] = [
                        'username' => $commit->owner->username,
                        'name' => $commit->owner->name,
                        'avatar' => current($commit->owner->avatars),
                        'count' => 0,
                        'commits' => 0,
                        'pairs' => [],
                    ];
            }

            foreach ($commit->revisions as $revision => $data) {
                $uri = '/a/changes/'.$commit->id.'/revisions/'.$revision.'/comments/';
                $comments = (array)$this->fetch($project, $uri);

                foreach ($comments as $comment) {
                    foreach ($comment as $message) {
                        // Skip themselves
                        if ($message->author->_account_id == $commit->owner->_account_id) {
                            continue;
                        }

                        if (!isset($results[$commit->owner->_account_id]['pairs'][$message->author->_account_id])) {
                            $results[$commit->owner->_account_id]['pairs'][$message->author->_account_id] = [
                                'count' => 0,
                                'username' => $message->author->username,
                                'name' => $message->author->name,
                                'commits' => [],
                            ];
                        }

                        $results[$commit->owner->_account_id]['pairs'][$message->author->_account_id]['count'] += 1;

                        if (!isset($results[$commit->owner->_account_id]['pairs'][$message->author->_account_id]['commits'][$commit->_number])) {
                            $results[$commit->owner->_account_id]['pairs'][$message->author->_account_id]['commits'][$commit->_number] = [
                                'subject' => $commit->subject,
                                'revisions' => [],
                            ];
                        }

                        $results[$commit->owner->_account_id]['pairs'][$message->author->_account_id]['commits'][$commit->_number]['revisions'][] = $data->_number;
                    }
                }
            }
        }

        foreach ($results as &$result) {
            $result['pairs'] = array_filter($result['pairs'], function($item){
                return $item['count'] > 0;
            });

            $result['count'] = count($result['pairs']);

            usort($result['pairs'], function ($a, $b){
                return $b['count'] - $a['count'];
            });

            foreach ($result['pairs'] as &$pair) {
                $result['commits'] += count($pair['commits']);

                foreach ($pair['commits'] as &$commit) {
                    $commit['revisions'] = array_unique($commit['revisions']);
                }
            }
        }

        $results = array_filter($results, function($item){
            return $item['count'] > 0;
        });

        usort($results, function($a, $b){
            return $b['count'] - $a['count'];
        });

        return $results;
    }

    public function getResults($results, Project $project)
    {
        return view('review._list', ['results' => $results, 'analyzer' => $this, 'project' => $project]);
    }

    public function getContent($result, Project $project)
    {
        return view('review.gerrit.review_pairs._results', ['result' => $result, 'project' => $project]);
    }
}
