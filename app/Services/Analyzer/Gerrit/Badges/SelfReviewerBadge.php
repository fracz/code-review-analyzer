<?php

namespace App\Services\Analyzer\Gerrit\Badges;


class SelfReviewerBadge extends AbstractBadge
{

    public function __construct()
    {
        parent::__construct(
            "Self Reviewer",
            "Approve own change",
            "?", "<i class=\"fa fa-street-view\" style=\"color:red\"></i>",
			"SelfReviewer"
        );
	}
		
	public function checkBadge($data, $email)
	{
		$commitsPerUser = $data["all_commits_per_user"];
		$commitAlreadyUsed = [];
		
		foreach ($commitsPerUser as $key => $commit) {
			 
			 foreach($commit['commits'] as $comKey => $com){
				 $detailedData = $data['reviews_per_commit'][$comKey];
				 
				 $onlySelfReview = false;
				 foreach($detailedData['code_reviews'] as $rev){

					 if($commit['email'] == $email && $rev['owner_email'] == $email && $rev['value'] == 1){
						$onlySelfReview = true;
					 }
				 
					 if($commit['email'] == $email && $rev['owner_email'] != $email && $rev['value'] == 1){
						 $onlySelfReview = false;
						 break;
					 }
					 
				 }

				 if($onlySelfReview && $detailedData['status'] == "MERGED")
					 $this->times++;
			 }
		}
	}
}