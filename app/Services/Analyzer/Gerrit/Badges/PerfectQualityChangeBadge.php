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
        parent::__construct("❤", "<i class=\"fa fa-cogs\" style=\"color:blueviolet\"></i>",
            "Made a change that didn't require any fixes");
    }

    public function checkCommit($commit)
    {
        return $commit["first_verification_passed"] && $commit["status"] == "MERGED";
    }
}
