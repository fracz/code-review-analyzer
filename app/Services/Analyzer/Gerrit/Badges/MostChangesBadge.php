<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 17:58
 */

namespace App\Services\Analyzer\Gerrit\Badges;


class MostChangesBadge extends AbstractOnePropertyBadge
{

    public function __construct()
    {
        parent::__construct("☆", "<i class=\"fa fa-random\" style=\"color:teal\"></i>", "You've made most of the commits in project this week",
            "commits_per_user", "count");
    }

}