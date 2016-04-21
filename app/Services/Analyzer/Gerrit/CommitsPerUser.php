<?php

namespace App\Services\Analyzer\Gerrit;

use App\Project;
use App\Commit;
use App\Person;
use App\Services\Analyzer\CommitCountTitle;

class CommitsPerUser extends AbstractAnalyzer
{
    public function getLabel($results)
    {
        return sprintf('%s (%d)', (string)$this, array_sum(array_map(function($item){
                return count($item['commits']);
        }, $results)));
    }

    public function __toString()
    {
        return 'Liczba zmian';
    }

    protected function decode($result)
    {
        return json_decode(substr($result, 4));
    }
    
    public function analyze(Project $project, $from, $to)
    {
       //echo "echo from CommitsPerUser";
        //$this->collectDataForReview($project, $from, $to);
        
        $result = \App\Commit::where('project', $project->getAttribute('name'))
                                ->where('updated', '>=', $from)
                                ->where('updated', '<=', $to)->get();

        $results = [];

        foreach ($result as $commit) {
			
			//jesli commit ma create date < $from to trzeba sprawdzic kiedy był jakis jego revision
			//jesli revision date < from tzn ze komentarz nzalazl sie tu przez jakas zmiane np wysatwienie review
			//wiec nei powinnismy go punktowac dla tego kto go stworzyl
			//a jesli revision date > from tzn ze trzeba zapunktowac w patchsetach tego kto stworzyl revision 
			//(ale nadal nikogo w commitach)
			
			//punktujemy w commitach tylko jesli create date > from
			if($commit->created >= $from)
			{
				
				if (!isset($results[$commit->owner->_account_id])) {                   
					$results[$commit->owner->_account_id] = [
						'username' => $commit->owner->username,
						'name' => $commit->owner->name,
						'email' => $commit->owner->email,
						'avatar' => (object) ['url' => $commit->owner->avatars->first()->url, 
											  'height' => $commit->owner->avatars->first()->height],
						'commits' => [],
						'abaddoned_count' => 0,
						'circumspect_count' => 0,
					];
				}

				$results[$commit->owner->_account_id]['commits'][$commit->_number] = $commit->subject;
				
				if($commit->status == "ABANDONED"){
					$results[$commit->owner->_account_id]['abaddoned_count']++;
				}

				$anyWithPositiveVerified = false;
				$anyWithNegativeVerified = false;
				foreach ($commit->verified as $ver) {
					if($ver->verified_value == 1){
						$anyWithPositiveVerified = true;
					} else if($ver->verified_value == -1){
						$anyWithNegativeVerified = true;
					}
				}

				if($anyWithPositiveVerified && !$anyWithNegativeVerified && $commit->status == "MERGED"){
					$results[$commit->owner->_account_id]['circumspect_count']++;
				}		
					
			}
        }

        $results = array_filter($results, function($item){
            return count($item['commits']) > 0;
        });



        foreach ($results as &$result) {
			//do rankingu nie bierzemy abandoned commitow
            $result['count'] = count($result['commits']) - $result['abaddoned_count'];
        }

        usort($results, function($a, $b){
            return $b['count'] - $a['count'];
        });
        
        //print_r($results);exit;
        return $results;
    }

    public function analyze_old(Project $project, $from, $to)
    {
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
        
        print_r($results);exit;
        return $results;
    }

    public function getResults($results, Project $project)
    {
        return view('review._list', ['results' => $results, 'analyzer' => $this, 'project' => $project]);
    }

    public function getContent($result, Project $project)
    {
        return view('review.gerrit.changes._commits_per_user', ['result' => $result, 'project' => $project]);
    }
}
