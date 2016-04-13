<?php

namespace App\Services\Analyzer\Gerrit\Badges;


class SelfReviewerBadge extends AbstractBadge
{

    public function __construct()
    {
        parent::__construct(
            "Self Reviewer",
            "Approved own change",
            "?", "<i class=\"fa fa-code\" style=\"color:teal\"></i>",
			"SelfReviewer"
        );
	}
		
	public function checkBadge($data, $email)
	{
		$commitsPerUser = $data["all_commits_per_user"];
		
		foreach ($commitsPerUser as $key => $commit) {
			 
			 foreach($commit['commits'] as $comKey => $com){
				 $detailedData = $data['reviews_per_commit'][$comKey];
				 
				 foreach($detailedData['code_reviews'] as $rev){
					 
					 if($rev['owner_email'] == $email && $commit['email'] == $email){
						 $this->times++;
					 }
				 }
			 }
		}
	}
}