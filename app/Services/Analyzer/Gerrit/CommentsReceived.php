<?php

namespace App\Services\Analyzer\Gerrit;

use App\Project;
use App\Services\Analyzer\CommitCountTitle;

class CommentsReceived extends AbstractAnalyzer
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
        return 'Liczba otrzymanych komentarzy';
    }

    protected function decode($result)
    {
        return json_decode(substr($result, 4));
    }
    
    public function analyze(Project $project, $from, $to)
    {
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
                    'commits' => [],
					'most_comments_per_change' => 0,
                ];
            }

            $results[$commit->owner->_account_id]['commits'][$commit->_number] = [
                'subject' => $commit->subject,
                'comments' => [],
            ];

            foreach ($commit->revisions as $revision) {
                $comments = $revision->comments;

                foreach ($comments as $message) { 
					if($message->updated > $from){
						if($commit->owner->email != $message->author->email){
							$results[$commit->owner->_account_id]['count'] += 1;
							$results[$commit->owner->_account_id]['commits'][$commit->_number]['comments'][$message->comment_id] = [
								'from' => [
									'name' => $message->author->name,
									'username' => $message->author->username,
									'email' => $message->author->email,
								],
								'revision' => $revision->_number,
								'date' => \DateTime::createFromFormat('Y-m-d H:i:s+', $message->updated),
								'text' => $message->message,
							];
						}
					}
                }
            }
        }
		
		foreach ($results as &$result) {
			$mostCommentsPerChange = 0;
			
			foreach ($result['commits'] as $commit) {
				if($mostCommentsPerChange < count($commit['comments']))
					$mostCommentsPerChange = count($commit['comments']);
			}
			
			$result['most_comments_per_change'] = $mostCommentsPerChange;
			
			//echo $result['email']. " ".$mostCommentsPerChange."<br/>";
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
                    'commits' => [],
                ];
            }

            $results[$commit->owner->_account_id]['commits'][$commit->_number] = [
                'subject' => $commit->subject,
                'comments' => [],
            ];

            foreach ($commit->revisions as $revision => $data) {
                $uri = '/a/changes/'.$commit->id.'/revisions/'.$revision.'/comments/';
                $comments = (array)$this->fetch($project, $uri);

                foreach ($comments as $comment) {
                    foreach ($comment as $message) {
                        $results[$commit->owner->_account_id]['count'] += 1;
                        $results[$commit->owner->_account_id]['commits'][$commit->_number]['comments'][$message->id] = [
                            'from' => [
                                'name' => $message->author->name,
                                'username' => $message->author->username,
                            ],
                            'revision' => $data->_number,
                            'date' => \DateTime::createFromFormat('Y-m-d H:i:s+', $message->updated),
                            'text' => $message->message,
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

        print_r($results);exit;
        return $results;
    }

    public function getResults($results, Project $project)
    {
        return view('review._list', ['results' => $results, 'analyzer' => $this, 'project' => $project]);
    }

    public function getContent($result, Project $project)
    {
        return view('review.gerrit.comments._received', ['result' => $result, 'project' => $project]);
    }
}
