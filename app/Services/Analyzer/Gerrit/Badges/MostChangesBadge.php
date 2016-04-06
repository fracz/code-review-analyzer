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
        parent::__construct(
            "The best contributor",
            "Make most of the changes",
            "☆", "<i class=\"fa fa-code\" style=\"color:teal\"></i>",
            "commits_per_user", "count"
        );
    }

}