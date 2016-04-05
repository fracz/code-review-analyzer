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
        parent::__construct("☆", "<i class=\"fa fa-random\" style=\"color:teal\"></i>",
            "Made most of the changes in project recently",
            "commits_per_user", "count");
    }

}