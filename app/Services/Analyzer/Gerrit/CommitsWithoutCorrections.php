<?php

namespace App\Services\Analyzer\Gerrit;

use App\Project;
use App\Person;
use App\Services\Analyzer\CommitCountTitle;

class CommitsWithoutCorrections extends AbstractAnalyzer
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
		return 'Liczba commitow, ktore nie musialy byc poprawiane';
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
                if($commit->created >= $from)
				{
					$allVerificationPassed = true;
					foreach ($commit->verified as $ver) {
						if($ver->verified_value == -1)
						{
							$allVerificationPassed = false;
							break;
						}
					}
					
					//print_r($commit);exit;
					$passedWithoutCorrections = false;
					if(($commit->status == "SUBMITTED" || $commit->status == "MERGED") && $allVerificationPassed){
						$passedWithoutCorrections = true;
					}
						
					
					if(!isset($results[$commit->owner->_account_id])){
						$results[$commit->owner->_account_id] = [
							'username' => $commit->owner->username,
							'commit_without_corrections' => 0,
						];
					}
					
					$selfReviewApproval = false;
					foreach ($commit->codeReviews as $rev) {
						if($rev->reviewer->email == $commit->owner->email && $rev->review_value == 1){
							$selfReviewApproval = true;
						}
					}
					
					if($passedWithoutCorrections && !$selfReviewApproval){
						$results[$commit->owner->_account_id]['commit_without_corrections']++;
						//print_r($commit); echo "<br/><br/>";
					}
					
					
						
					
					
				} 
            }
                
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
