<?php

namespace App\Services\Analyzer\Gerrit\Badges;


class ThrowawayCodeBadge extends AbstractBadge
{

    public function __construct()
    {
        parent::__construct(
            "Throwaway Code",
            "Made a change that is abandoned",
            "?", "<i class=\"fa fa-code\" style=\"color:teal\"></i>",
			"ThrowawayCode"
        );
	}
		
	public function checkBadge($data, $email)
	{
		$commitsPerUser = $data["commits_per_user"];
		
		foreach ($commitsPerUser as $key => $commit) {
			 
			 if($commit['email'] == $email && $commit['abaddoned_count'] > 0){
				 $this->times = $commit['abaddoned_count'];
				 return;
			 }	
		}
	}
}