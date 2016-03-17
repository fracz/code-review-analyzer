<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 17:56
 */

namespace App\Services\Analyzer\Gerrit\Badges;


class ManyReviewsInOneDayBadge extends AbstractBadge
{

    public function __construct()
    {
        parent::__construct("⊱", "You've made 3 reviews in one day");
    }

    public function getBadge($results, $email)
    {
        // TODO: Implement getBadge() method.
    }
}