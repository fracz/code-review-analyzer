<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 17:58
 */

namespace App\Services\Analyzer\Gerrit\Badges;


class MostCommentsPerChangeBadge extends AbstractBadge
{

    public function __construct()
    {
        parent::__construct("♬", "You're getting biggest number of comments per change in team");
    }

    public function getBadge($results, $email)
    {
        // TODO: Implement getBadge() method.
    }
}