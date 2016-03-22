<?php

namespace App\Services\Analyzer\Gerrit;

use App\Project;
use App\Person;
use App\Services\Analyzer\CommitCountTitle;

class ReviewsPerCommit extends AbstractAnalyzer
{
	public function getLabel($results)
	{
		/*return sprintf('%s (%d)', (string)$this, array_sum(array_map(function($item){
			return count($item['commits']);
		}, $results)));*/
                
                return "";
	}

	public function __toString()
	{
		return 'Liczba zmian per commit';
	}

	protected function decode($result)
	{
		return json_decode(substr($result, 4));
	}
        
        public function analyze(Project $project, $from, $to)
	{         
            $result = \App\Commit::where('project', $project->getAttribute('name'))
                                ->where('updated', '>=', $from)
                                ->where('updated', '<=', $to)->get();
            
            $results = [];
                
            foreach ($result as $commit) {
                
                $revisions = [];
                
                
                
                foreach ($commit->revisions as $revision) {
                    $revisions[$revision->revision_id] = [
                        'id' => $revision->revision_id,
                        'owner_id' => $revision->uploader_id,
                        'owner_email' => $revision->uploader->email,
                        'create_date' => $revision->created
                    ];
                }
                
                $allVerificationPassed = true;
                foreach ($commit->verified as $ver) {
                    if($ver->verified_value == -1)
                    {
                        $allVerificationPassed = false;
                        break;
                    }
                }
                
                //print_r($commit->verified);exit;
                
                $results[$commit->_number] = [
                    'id' => $commit->commit_id,
                    'owner_id' => $commit->owner_id,
                    'create_date' => $commit->created,
                    'update_date' => $commit->updated,
                    'status' => $commit->status,
                    'first_verification_passed' => $allVerificationPassed,
                    'revisions' => $revisions,
                ];
                     
                
            }
//print_r($results);exit;
            
                
            //print_r($results);exit;
            return $results;
	}


	public function getResults($results, Project $project)
	{
		return view('review._list', ['results' => $results, 'analyzer' => $this, 'project' => $project]);
	}

	public function getContent($result, Project $project)
	{
		return view('review.gerrit.changes._reviews_per_user', ['result' => $result, 'project' => $project]);
	}
}
