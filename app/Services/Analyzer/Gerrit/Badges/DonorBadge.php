<?php

namespace App\Services\Analyzer\Gerrit\Badges;


class DonorBadge extends AbstractBadge
{

    public function __construct()
    {
        parent::__construct(
            "Donor",
            "Made a patchset in change of someone else",
            "?", "<i class=\"fa fa-code\" style=\"color:teal\"></i>",
			"Donor"
        );
	}
		
	public function checkBadge($data, $email)
	{
		$commitsPerUser = $data["all_commits_per_user"];
		
		foreach ($commitsPerUser as $key => $commit) {
			 
			 foreach($commit['commits'] as $comKey => $com){
				 $detailedData = $data['reviews_per_commit'][$comKey];
				 
				 foreach($detailedData['revisions'] as $rev){
					 
					 if($rev['owner_email'] == $email && $rev['owner_email'] != $commit['email']){
						 $this->times++;
					 }
				 }
			 }
		}
	}
}