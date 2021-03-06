<?php

namespace App\Services\Analyzer\Gerrit;

use App\Project;
use App\Services\Analyzer\StringTitle;

class AverageCommentLength extends AbstractAnalyzer
{
    use StringTitle;

    public function __toString()
    {
        return 'Średnia długość komentarza';
    }

    protected function decode($result)
    {
        return json_decode(substr($result, 4));
    }
    
    public function analyze(Project $project, $from, $to)
    {
        //echo "echo from AverageCommentLength";exit;
//        $this->collectDataForReview($project, $from, $to);
        
        $result = \App\Commit::where('project', $project->getAttribute('name'))
                                ->where('updated', '>=', $from)
                                ->where('updated', '<=', $to)->get();

        //print_r($result);
        $results = [];

        foreach ($result as $commit) {
            foreach ($commit->revisions as $revision) {
                $comments = $revision->comments;
				
                foreach ($comments as $message) {
					if($message->updated > $from){
						if($commit->owner->email != $message->author->email){
							if (!isset($results[$message->author->_account_id])) {
							
								$results[$message->author->_account_id] = [
									'username' => $message->author->username,
									'name' => $message->author->name,
									'email' => $message->author->email,
									'avatar' => (object) ['url' => $message->author->avatars->first()->url, 
													'height' => $message->author->avatars->first()->height],
									'average' => 0,
									'count' => 0,
									'value' => 0,
									'calculated_value' => 0,
									'rank' => 0,
								];
							}

							$results[$message->author->_account_id]['count'] += 1;
							$results[$message->author->_account_id]['value'] += strlen($message->message);
							$results[$message->author->_account_id]['calculated_value'] += $this->getValueFromMessage($message);
						}
					}
                }
            }
        }
        $results = array_filter($results, function($item){
            return $item['count'] > 0;
        });

        foreach ($results as &$result) {
            $result['average'] = $result['value']/$result['count'];
            $result['rank'] += $result['calculated_value']/$result['count'];
            unset($result['value']);
        }

        usort($results, function($a, $b){
            $isMore = $b['average'] > $a['average'];
            $isEqual = $b['average'] == $a['average'];
            return $isMore ? 1 : ($isEqual ? 0 : -1);
        });



        return $results;
    }

    public function analyze_old(Project $project, $from, $to)
    {
        echo "echo from AverageCommentLength";exit;
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
                $comments = (array)$this->fetch($project, $uri);

                foreach ($comments as $comment) {
                    //print_r($uri);exit;
                    foreach ($comment as $message) {
                        if (!isset($results[$message->author->_account_id])) {
                            $results[$message->author->_account_id] = [
                                'username' => $message->author->username,
                                'name' => $message->author->name,
                                'avatar' => current($message->author->avatars),
                                'average' => 0,
                                'count' => 0,
                                'value' => 0,
                                'rank' => 0,
                            ];
                        }

                        $results[$message->author->_account_id]['count'] += 1;
                        $results[$message->author->_account_id]['value'] += strlen($message->message);

                    }
                }
            }
        }

        $results = array_filter($results, function($item){
            return $item['count'] > 0;
        });

        foreach ($results as &$result) {
            $result['average'] = $result['value']/$result['count'];
            unset($result['value']);
            $results[$message->author->_account_id]['rank'] += $this->getValueFromMessage($message->message)/$result['count'];
        }

        usort($results, function($a, $b){
            $isMore = $b['average'] > $a['average'];
            $isEqual = $b['average'] == $a['average'];
            return $isMore ? 1 : ($isEqual ? 0 : -1);
        });

        return $results;
    }

    public function getResults($results, Project $project)
    {
        return view('review._list', ['results' => $results, 'analyzer' => $this, 'project' => $project]);
    }

    public function getContent($result, Project $project)
    {
        return view('review.gerrit.statistics._average_comment_length', ['result' => $result]);
    }

    public function getValueFromMessage($message){
        if(strlen($message->message) > 50)
            return 50;
        else
            return strlen($message->message);
    }

}
