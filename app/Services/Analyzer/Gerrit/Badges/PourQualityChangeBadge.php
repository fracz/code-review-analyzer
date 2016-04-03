<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 18:01
 */

namespace App\Services\Analyzer\Gerrit\Badges;


class PourQualityChangeBadge extends QualityBadge
{
    public function __construct()
    {
        parent::__construct("☂", "<i class=\"fa fa-recycle\" style=\"color:red\"></i>", "One of commits is poor quality (many fixes and disapproves)");
    }

    public function checkCommit($commit)
    {
        $noOfFixes = count($commit["revisions"]);
        return $noOfFixes >= 4 and $commit["bad_reviews_count"] > 0;
    }
}