<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 17:58
 */

namespace App\Services\Analyzer\Gerrit\Badges;

class BiggestProgessInRankingBadge extends AbstractOnePropertyBadge
{
    public function __construct()
    {
        parent::__construct(
            "Priceless developer",
            "The highest score in the project",
            "✪", "<i class=\"fa fa-diamond\" style=\"color:slategray\"></i>",
			"MostValuedDeveloper",
            "ranking_overall", "value"
        );
    }
}

