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
            "Make a poor quality change (many fixes and disapproves)",
            "☂", "<i class=\"fa fa-exclamation-circle\" style=\"color:red\"></i>"
        );
    }

    public function checkCommit($commit)
    {
        $noOfFixes = count($commit["revisions"]);
        return $noOfFixes >= 4 and $commit["bad_reviews_count"] > 0;
    }
}