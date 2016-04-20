<?php

namespace App\Services\Analyzer\Gerrit\Badges;


class NightOwlBadge extends AbstractBadge
{

    public function __construct()
    {
        parent::__construct(
            "Night Owl",
            "Work between 1am and 5am",
            "?", "<i class=\"fa fa-moon-o\" style=\"color:blue\"></i>",
			"NightOwl"
        );
	}
		
	public function checkBadge($data, $email)
	{
		$commitsPerUser = $data["all_commits_per_user"];
		$days_already_signed = [];
		
		foreach ($commitsPerUser as $key => $commit) {
			 
			 foreach($commit['commits'] as $comKey => $com){
				 $detailedData = $data['reviews_per_commit'][$comKey];
				 
				 foreach($detailedData['revisions'] as $rev){
					 $date = $rev['create_date'];
					 $date_exploded = explode(" ", $date);
					 
					 /*if($detailedData['id'] == "dissby~master~Iea1a75c81bca23963b06a935ab5edbe01a985dba"){
						 print_r($rev);echo "<br/><Br/>";
					 }*/
					 
					 if(!in_array($date_exploded[0], $days_already_signed))
					 {
						 $hours = $date_exploded[1];
						 $hours_exploded = explode(":", $hours);
						 $hour = $hours_exploded[0];
						 
						 if(($hour == "01" || $hour == "02" || $hour == "03" || $hour == "04") && $rev['owner_email'] == $email){
							 array_push($days_already_signed, $date_exploded[0]);
							 $this->times++;
						 } 
					 }
				 }
				 
				 foreach($detailedData['code_reviews'] as $review){
						$dateString = $review["review_date"];
						$date_exploded = explode(" ", $dateString);
					 
						 if(!in_array($date_exploded[0], $days_already_signed))
						 {
							 $hours = $date_exploded[1];
							 $hours_exploded = explode(":", $hours);
							 $hour = $hours_exploded[0];
							 
							 if(($hour == "01" || $hour == "02" || $hour == "03" || $hour == "04") && $review['owner_email'] == $email){
								 array_push($days_already_signed, $date_exploded[0]);
								 $this->times++;
							 } 
						 }
					}
			 }
		}
	}
}