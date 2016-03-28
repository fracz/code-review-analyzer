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
        parent::__construct("☂", "One of your changes required 3 fixes");
    }

    public function checkCommit($commit)
    {
        $noOfFixes = count($commit["revisions"]);
        return $noOfFixes >= 4;
    }
}