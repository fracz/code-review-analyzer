<?php

namespace App\Services\Analyzer\Gerrit;

use App\Project;
use App\Services\Analyzer\CommitCountTitle;

class CommentsGiven extends AbstractAnalyzer
{
    public function getLabel($results)
    {
        return sprintf('%s (%d)', (string)$this, array_sum(array_map(function($item){
            return array_sum(array_map(function($subitem){
                return count($subitem['comments']);
            }, $item['commits']));
        }, $results)));
    }

    public function __toString()
    {
        return 'Liczba napisanych komentarzy';
    }

    protected function decode($result)
    {
        return json_decode(substr($result, 4));
    }

    public function analyze(Project $project, $from, $to)
    {
        //echo "echo from CommentsGiven";exit;
        //echo  $project->getAttribute('name');exit;
        //$this->collectDataForReview($project, $from, $to);
        
        $result = \App\Commit::where('project', $project->getAttribute('name'))
                                ->where('updated', '>=', $from)
                                ->where('updated', '<=', $to)->get();

        $results = [];

        foreach ($result as $commit) {
            foreach ($commit->revisions as $revision) {
                $commentList = $revision->comments;

                foreach ($commentList as $comment) {
					if($comment->updated > $from){
						if($commit->owner->email != $comment->author->email){
							if (!isset($results[$comment->author->_account_id])) {
								$results[$comment->author->_account_id] = [
									'username' => $comment->author->username,
									'name' => $comment->author->name,
									'email' => $comment->author->email,
									'avatar' => (object) ['url' => $comment->author->avatars->first()->url, 
													'height' => $comment->author->avatars->first()->height],
									'count' => 1,
									'commits' => [],
									'rank' => 0,
								];
							} else {
								$results[$comment->author->_account_id]['count'] += 1;
							}
						
						
							if (!isset($results[$comment->author->_account_id]['commits'][$commit->_number])) {
								$results[$comment->author->_account_id]['commits'][$commit->_number] = [
									'subject' => $commit->subject,
									'comments' => [],
								];
							}
							
							$results[$comment->author->_account_id]['commits'][$commit->_number]['comments'][] = [
								'to' => [
									'name' => $commit->owner->name,
									'username' => $commit->owner->username,
									'username' => $commit->owner->email,
								],
								'revision' => $revision->_number,
								'date' => \DateTime::createFromFormat('Y-m-d H:i:s+', $comment->updated),
								'text' => $comment->message
							];
						
							$results[$comment->author->_account_id]['rank'] += $this->codeInComment($comment->message);
						}
					}
                }
            }
        }

        usort($results, function($a, $b){
                return $b['count'] - $a['count'];
        });

        foreach ($results as &$result) {
                $result['commits'] = array_filter($result['commits'], function($item){
                        return !empty($item['comments']);
                });

                foreach ($result['commits'] as &$commit) {
                        usort($commit['comments'], function ($a, $b){
                                return $a['date']->getTimestamp() - $b['date']->getTimestamp();
                        });
                }
        }

        //print_r($results);exit;
        return $results;
    }
    
    public function analyze_old(Project $project, $from, $to)
    {
        echo "echo from CommentsGiven";exit;
        $this->collectDataForReview($project, $from, $to);

        $uri = '/a/changes/?q=project:'.$project->getAttribute('name');
        $uri .= ' -is:draft ((status:merged)OR(status:open))';
        $uri .= ' after:'.$from.' before:'.$to;
        $uri .= '&o=ALL_REVISIONS&o=DETAILED_ACCOUNTS&o=LABELS';

        $result = $this->fetch($project, $uri);
        $results = [];

        foreach ($result as $commit) {
            foreach ($commit->revisions as $revision => $data) {
                $uri = '/a/changes/'.$commit->id.'/revisions/'.$revision.'/comments/';
                $commentList = $this->fetch($project, $uri);

                foreach ($commentList as $file => $comments) {
                    foreach ($comments as $comment) {
                        if (!isset($results[$comment->author->_account_id])) {
                            $results[$comment->author->_account_id] = [
                                'username' => $comment->author->username,
                                'name' => $comment->author->name,
                                'avatar' => current($comment->author->avatars),
                                'count' => 1,
                                'commits' => [],
                            ];
                        } else {
                            $results[$comment->author->_account_id]['count'] += 1;
                        }

                        if (!isset($results[$comment->author->_account_id]['commits'][$commit->_number])) {
                            $results[$comment->author->_account_id]['commits'][$commit->_number] = [
                                'subject' => $commit->subject,
                                'comments' => [],
                            ];
                        }

                        $results[$comment->author->_account_id]['commits'][$commit->_number]['comments'][] = [
                            'to' => [
                                'name' => $commit->owner->name,
                                'username' => $commit->owner->username,
                            ],
                            'revision' => $data->_number,
                            'date' => \DateTime::createFromFormat('Y-m-d H:i:s+', $comment->updated),
                            'text' => $comment->message,
                        ];
                    }
                }
            }
        }

        usort($results, function($a, $b){
                return $b['count'] - $a['count'];
        });

        foreach ($results as &$result) {
                $result['commits'] = array_filter($result['commits'], function($item){
                        return !empty($item['comments']);
                });

                foreach ($result['commits'] as &$commit) {
                        usort($commit['comments'], function ($a, $b){
                                return $a['date']->getTimestamp() - $b['date']->getTimestamp();
                        });
                }
        }

        return $results;
    }

    public function getResults($results, Project $project)
    {
        return view('review._list', ['results' => $results, 'analyzer' => $this, 'project' => $project]);
    }

    public function getContent($result, Project $project)
    {
        return view('review.gerrit.comments._given', ['result' => $result, 'project' => $project]);
    }

    public function codeInComment($comment){

        $value = 0.0;

        foreach(preg_split("/((\r?\n)|(\r\n?))/", $comment) as $line){

            if (substr($line, 0, 1) === ' ' or substr($line, 0, 2) === '> ') {
                $value += 10;
            }

        }
        return $value;
    }

}
