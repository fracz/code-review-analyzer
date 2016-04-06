<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 17:59
 */

namespace App\Services\Analyzer\Gerrit\Badges;


class MostReviewsBadge extends AbstractOnePropertyBadge
{
    public function __construct()
    {
        parent::__construct(
            "The best reviewer",
            "Make most of the reviews",
            "☻", "<i class=\"fa fa-ambulance\" style=\"color:darkorange\"></i>",
            "reviews_per_user", "count"
        );
    }

}