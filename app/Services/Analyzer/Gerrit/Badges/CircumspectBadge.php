<?php

namespace App\Services\Analyzer\Gerrit\Badges;


class CircumspectBadge extends AbstractBadge
{

    public function __construct()
    {
        parent::__construct(
            "Circumspect",
            "Made a change without CI fails",
            "?", "<i class=\"fa fa-cogs\" style=\"color:black\"></i>",
			"Circumspect"
        );
	}
		
	public function checkBadge($data, $email)
	{
		$commitsPerUser = $data["commits_per_user"];
		
		foreach ($commitsPerUser as $key => $commit) {
			 
			 if($commit['email'] == $email && $commit['circumspect_count'] > 0){
				 $this->times = $commit['circumspect_count'];
				 return;
			 }	
		}
	}
}