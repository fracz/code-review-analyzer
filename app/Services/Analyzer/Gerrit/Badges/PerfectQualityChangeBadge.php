<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 18:00
 */

namespace App\Services\Analyzer\Gerrit\Badges;


class PerfectQualityChangeBadge extends AbstractBadge
{
    public function __construct()
    {
        parent::__construct("❤", "You've made a change that didn't required any fixes");
    }

    public function getBadge($results, $email)
    {
        // TODO: Implement getBadge() method.
    }
}