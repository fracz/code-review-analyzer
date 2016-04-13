<?php

namespace App\Services\Analyzer\Gerrit\Badges;


class NightOwlBadge extends AbstractBadge
{

    public function __construct()
    {
        parent::__construct(
            "Night Owl",
            "Made a change/patchset between 1am-5am",
            "?", "<i class=\"fa fa-code\" style=\"color:teal\"></i>",
			"NightOwl"
        );
	}
		
	public function checkBadge($data, $email)
	{
		$commitsPerUser = $data["all_commits_per_user"];
		
		foreach ($commitsPerUser as $key => $commit) {
			 
			 foreach($commit['commits'] as $comKey => $com){
				 $detailedData = $data['reviews_per_commit'][$comKey];
				 
				 foreach($detailedData['revisions'] as $rev){
					 $date = $rev['create_date'];
					 $date_exploded = explode(" ", $date);
					 $hours = $date_exploded[1];
					 $hours_exploded = explode(":", $hours);
					 $hour = $hours_exploded[0];
					 
					 if(($hour == "01" || $hour == "02" || $hour == "03" || $hour == "04") && $rev['owner_email'] == $email){
						 $this->times++;
					 }
				 }
			 }
		}
	}
}