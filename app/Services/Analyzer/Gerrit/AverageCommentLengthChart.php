<?php

namespace App\Services\Analyzer\Gerrit;

use App\Project;
use App\Services\Analyzer\StringTitle;

class AverageCommentLengthChart extends AbstractAnalyzer
{
    use StringTitle;

    public function __toString()
    {
        return 'Średnia długość komentarza - wykres';
    }

    protected function decode($result)
    {
        return json_decode(substr($result, 4));
    }

    public function analyze(Project $project, $from, $to)
    {
        //echo "echo from AverageCommentLengthChart";exit;
        //$this->collectDataForReview($project, $from, $to);
        
        $result = \App\Commit::where('project', $project->getAttribute('name'))
                                ->where('updated', '>=', $from)
                                ->where('updated', '<=', $to)->get();

        $results = [
            'x' => 'x',
            'columns' => [
                ['x'],
                ['user'],
                ['average'],
            ],
            'names' => [
                'user' => 'Użytkownik',
                'average' => 'Średnia',
            ],
            'type' => 'bar',
            'types' => [
                'average' => 'line',
            ],
        ];

        $labels = [];
        $values = [];

        foreach ($result as $commit) {
            foreach ($commit->revisions as $revision) {
                $comments = $revision->comments;

                foreach ($comments as $message) {
					if($message->updated > $from){
						if($commit->owner->email != $message->author->email){
							if (!in_array($message->author->name, $labels)) {
								$labels[$message->author->_account_id] = $message->author->name;
								$values[$message->author->_account_id] = [
									'count' => 0,
									'value' => 0,
								];
							}

							$values[$message->author->_account_id]['count'] += 1;
							$values[$message->author->_account_id]['value'] += strlen($message->message);
						}
					}
                }
            }
        }

        if (count($values) > 0) {
            $average = round(array_sum(array_map(
                    function ($value){
                        return $value['value'] / $value['count'];
                    },
                    $values
                )) / count($values), 2);

            foreach ($values as $user => $value) {
                $results['columns'][0][] = $labels[$user];
                $results['columns'][1][] = round($value['value'] / $value['count'], 2);
                $results['columns'][2][] = $average;
            }
        }

        //print_r($results);exit;
        return $results;
    }
    
    public function analyze_old(Project $project, $from, $to)
    {
        echo "echo from AverageCommentLengthChart";exit;
        $this->collectDataForReview($project, $from, $to);

        $uri = '/a/changes/?q=project:'.$project->getAttribute('name');
        $uri .= ' -is:draft ((status:merged)OR(status:open))';
        $uri .= ' after:'.$from.' before:'.$to;
        $uri .= '&o=ALL_REVISIONS&o=DETAILED_ACCOUNTS&o=LABELS';

        $result = $this->fetch($project, $uri);
        $results = [
            'x' => 'x',
            'columns' => [
                ['x'],
                ['user'],
                ['average'],
            ],
            'names' => [
                'user' => 'Użytkownik',
                'average' => 'Średnia',
            ],
            'type' => 'bar',
            'types' => [
                'average' => 'line',
            ],
        ];

        $labels = [];
        $values = [];

        foreach ($result as $commit) {
            foreach ($commit->revisions as $revision => $data) {
                $uri = '/a/changes/'.$commit->id.'/revisions/'.$revision.'/comments/';
                $comments = (array)$this->fetch($project, $uri);

                foreach ($comments as $comment) {
                    foreach ($comment as $message) {
                        if (!in_array($message->author->name, $labels)) {
                            $labels[$message->author->_account_id] = $message->author->name;
                            $values[$message->author->_account_id] = [
                                'count' => 0,
                                'value' => 0,
                            ];
                        }

                        $values[$message->author->_account_id]['count'] += 1;
                        $values[$message->author->_account_id]['value'] += strlen($message->message);
                    }
                }
            }
        }

        if (count($values) > 0) {
            $average = round(array_sum(array_map(
                    function ($value){
                        return $value['value'] / $value['count'];
                    },
                    $values
                )) / count($values), 2);

            foreach ($values as $user => $value) {
                $results['columns'][0][] = $labels[$user];
                $results['columns'][1][] = round($value['value'] / $value['count'], 2);
                $results['columns'][2][] = $average;
            }
        }

        return $results;
    }


    public function getResults($results, Project $project)
    {
        return view('review.gerrit.statistics._average_comment_length_chart', ['results' => $results]);
    }

    public function getContent($result, Project $project)
    {
        return '';
    }
}
