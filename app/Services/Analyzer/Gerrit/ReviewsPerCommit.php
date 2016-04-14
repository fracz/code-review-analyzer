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
				$all_revisions = [];
				$codeREviews = [];
                
                
                foreach ($commit->revisions as $revision) {
					
					$all_revisions[$revision->revision_id] = [
						'id' => $revision->revision_id,
						'owner_id' => $revision->uploader_id,
						'owner_email' => $revision->uploader->email,
						'create_date' => $revision->created,
						'rebased' => $revision->rebased,
						'from_current_perion' => false
					];
						
					if($revision->created >= $from){
						$revisions[$revision->revision_id] = [
							'id' => $revision->revision_id,
							'owner_id' => $revision->uploader_id,
							'owner_email' => $revision->uploader->email,
							'create_date' => $revision->created,
							'rebased' => $revision->rebased
						];
						
						$all_revisions[$revision->revision_id]['from_current_perion'] = true;
					}
                }
				
				foreach ($commit->codeReviews as $codeRev) {
					if($codeRev->review_date >= $from){
						array_push($codeREviews, [
							'value' => $codeRev->review_value,
							'owner_email' => $codeRev->reviewer->email,
							'_revision_number' => $codeRev->_revision_number,
							'review_date' => $codeRev->review_date,
						]);
					}
                }
                
                $allVerificationPassed = true;
                $howManyBadVerificationsForThatCommit = 0;
                foreach ($commit->verified as $ver) {
                    
                    if($ver->verified_value == -1)
                    {
                        $allVerificationPassed = false;
                        $howManyBadVerificationsForThatCommit++;
                    }
                }

                $passedWithoutCorrections = false;
                if(($commit->status == "SUBMITTED" || $commit->status == "MERGED") && $allVerificationPassed && $commit->created >= $from){
					$passedWithoutCorrections = true;
				}
                    
				
				
				$badCodeReviewCount = 0;
				foreach ($commit->codeReviews as $rev) {
					//if($commit->)
					if($rev->review_value == -1){
						$badCodeReviewCount++;
					}
				}
                
                //print_r($commit->verified);exit;
                
                $results[$commit->_number] = [
                    'id' => $commit->commit_id,
                    'owner_id' => $commit->owner_id,
					'owner_email' => $commit->owner->email,
                    'create_date' => $commit->created,
                    'update_date' => $commit->updated,
                    'status' => $commit->status,
                    'first_verification_passed' => ($allVerificationPassed) ? 'true' : 'false',
                    'passed_without_corrections' => ($passedWithoutCorrections) ? 'true' : 'false',
                    'bad_reviews_count' => $howManyBadVerificationsForThatCommit,
					'bad_code_review_count' => $badCodeReviewCount,
                    'revisions' => $revisions,
					'all_revisions' => $all_revisions,
					'code_reviews' => $codeREviews,
                ];
                
                     
                
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
