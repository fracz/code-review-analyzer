<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 17:55
 */

namespace App\Services\Analyzer\Gerrit\Badges;


class ManyChangesInOneDayBadge extends AbstractBadge
{

    public function __construct()
    {
        parent::__construct("⊰", "You've made 3 changes in one day");
    }

    public function getBadge($results, $email)
    {
        // TODO: Implement getBadge() method.
    }
}