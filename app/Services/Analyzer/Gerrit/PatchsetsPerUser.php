<?php

namespace App\Services\Analyzer\Gerrit;

use App\Project;
use App\Commit;
use App\Person;
use App\Services\Analyzer\CommitCountTitle;

class PatchsetsPerUser extends AbstractAnalyzer
{
    public function getLabel($results)
    {
        return sprintf('%s (%d)', (string)$this, array_sum(array_map(function($item){
                return count($item['patchsets']);
        }, $results)));
    }

    public function __toString()
    {
        return 'Liczba patchsetow';
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
            
            $patchsetNumber = 0;
            foreach ($commit->revisions as $revision) {
				
				if($revision->created >= $from){
					if (!isset($results[$revision->uploader->_account_id])) { 				
						$results[$revision->uploader->_account_id] = [
							'username' => $revision->uploader->username,
							'name' => $revision->uploader->name,
							'email' => $revision->uploader->email,
							'avatar' => (object) ['url' => $revision->uploader->avatars->first()->url, 
												  'height' => $revision->uploader->avatars->first()->height],
							'patchsets' => [],
							'count_without_first_patchset' => 0,
						];
					}
					
					if($patchsetNumber != 0){
						$results[$revision->uploader->_account_id]['count_without_first_patchset']++;
					}

					$results[$revision->uploader->_account_id]['patchsets'][$revision->revision_id] = $revision->revision_id;
					$patchsetNumber++;
				}
            }
        }

        $results = array_filter($results, function($item){
            return count($item['patchsets']) > 0;
        });

        foreach ($results as &$result) {
            $result['count'] = count($result['patchsets']);
            $result['count_without_first_patchset'] = $result['count_without_first_patchset'];
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
