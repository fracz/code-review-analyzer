<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 18:01
 */

namespace App\Services\Analyzer\Gerrit\Badges;


class PoorQualityChangeBadge extends QualityBadge
{
    public function __construct()
    {
        parent::__construct(
            "Poor change",
            "Poor quality change that required many fixes and received many disapproves",
            "☂", "<i class=\"fa fa-exclamation-circle\" style=\"color:red\"></i>", "PoorChange"
        );
    }

    public function checkCommit($commit, $email)
    {
        //$noOfFixes = count($commit["revisions"]);
        //return $noOfFixes >= 4 and $commit["bad_reviews_count"] > 0;
		return $commit['bad_code_review_count'] > 2;
    }
}