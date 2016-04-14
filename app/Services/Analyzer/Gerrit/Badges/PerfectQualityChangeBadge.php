<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 18:00
 */

namespace App\Services\Analyzer\Gerrit\Badges;


class PerfectQualityChangeBadge extends QualityBadge
{
    public function __construct()
    {
        parent::__construct(
            "Flawless change",
            "Make a change that did not require any fixes",
            "❤", "<i class=\"fa fa-check-circle\" style=\"color:forestgreen\"></i>", "FlawlessChange"
        );
    }

    public function checkCommit($commit, $email)
    {
		//if there was self-review + 1 then we don't get this badge
		foreach($commit['code_reviews'] as $rev){

			 if($rev['owner_email'] == $email && $commit['owner_email'] == $email && $rev['value'] == 1){
				return false;
			 }
		 }
				 
        return $commit["passed_without_corrections"] == "true" && ($commit["status"] == "MERGED" || $commit["status"] == "SUBMITTED"); 
    }
}
