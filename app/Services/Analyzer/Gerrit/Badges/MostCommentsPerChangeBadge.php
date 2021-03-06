<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 17:58
 */

namespace App\Services\Analyzer\Gerrit\Badges;

class MostCommentsPerChangeBadge extends AbstractOnePropertyBadge
{
    public function __construct()
    {
        parent::__construct(
            "Controversial",
            "Receive the biggest number of comments per change",
            "♬", "<i class=\"fa fa-book\" style=\"color:deeppink\"></i>",
			"Controversial",
            "comments_received", "most_comments_per_change"
        );
    }

}
