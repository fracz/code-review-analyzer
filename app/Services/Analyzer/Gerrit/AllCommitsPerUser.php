<?php

namespace App\Services\Analyzer\Gerrit;

use App\Project;
use App\Commit;
use App\Person;
use App\Services\Analyzer\CommitCountTitle;

class AllCommitsPerUser extends AbstractAnalyzer
{
    public function getLabel($results)
    {
        return sprintf('%s (%d)', (string)$this, array_sum(array_map(function($item){
                return count($item['commits']);
        }, $results)));
    }

    public function __toString()
    {
        return 'Liczba wszystkich zmian';
    }

    protected function decode($result)
    {
        return json_decode(substr($result, 4));
    }
    
    public function analyze(Project $project, $from, $to)
    {
		//lista pomocnicza - badge moga z niej korzystac
		//nie uwzglednia daty wystawienia commitu
		
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
					'commits' => [],
				];
			}

			$results[$commit->owner->_account_id]['commits'][$commit->_number] = $commit->subject;
			
        }

        $results = array_filter($results, function($item){
            return count($item['commits']) > 0;
        });



        foreach ($results as &$result) {
            $result['count'] = count($result['commits']);
        }

        usort($results, function($a, $b){
            return $b['count'] - $a['count'];
        });
        
        //print_r($results);exit;
        return $results;
    }

    public function getResults($results, Project $project)
    {
        return "";
    }

    public function getContent($result, Project $project)
    {
        return "";
    }
}
