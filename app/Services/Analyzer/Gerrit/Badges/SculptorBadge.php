<?php

namespace App\Services\Analyzer\Gerrit\Badges;


class SculptorBadge extends AbstractBadge
{

    public function __construct()
    {
        parent::__construct(
            "Sculptor",
            "Made a change with more than 5 patchsets",
            "?", "<i class=\"fa fa-code\" style=\"color:teal\"></i>",
			"Sculptor"
        );
	}
		
	public function checkBadge($data, $email)
	{
		$commitsPerUser = $data["all_commits_per_user"];
		
		foreach ($commitsPerUser as $key => $commit) {
			 
			 foreach($commit['commits'] as $comKey => $com){
				 $detailedData = $data['reviews_per_commit'][$comKey];
				 if(count($detailedData['all_revisions']) > 5 && $commit['email'] == $email){
					 $this->times++;
				 }
			 }
		}
	}
}