<?php

namespace App\Services\Analyzer\Gerrit\Badges;


class SculptorBadge extends AbstractBadge
{

    public function __construct()
    {
        parent::__construct(
            "Sculptor",
            "Change with more than 10 patchsets",
            "?", "<i class=\"fa fa-cubes\" style=\"color:gray\"></i>",
			"Sculptor"
        );
	}
		
	public function checkBadge($data, $email)
	{
		$commitsPerUser = $data["all_commits_per_user"];
		
		foreach ($commitsPerUser as $key => $commit) {
			 
			 foreach($commit['commits'] as $comKey => $com){
				 $detailedData = $data['reviews_per_commit'][$comKey];
				 
				 //print_r($detailedData);echo "<br/><br/>";
				 
				 if(count($detailedData['all_revisions']) > 10 && $commit['email'] == $email){
 
					 $anyFronCurrentPeriod = false;
					 foreach($detailedData['all_revisions'] as $rev){
						 if($rev['from_current_perion'] == true){
							 $anyFronCurrentPeriod = true;
							 break;
						 }
					 }
					 
					 if($anyFronCurrentPeriod)
						$this->times++;
				 }
			 }
		}
	}
}