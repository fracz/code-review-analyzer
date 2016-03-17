<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 17:59
 */

namespace App\Services\Analyzer\Gerrit\Badges;


class MostReviewsBadge extends AbstractBadge
{
    public function __construct()
    {
        parent::__construct("☻", "You've made most of the reviews in project this week");
    }

    public function getBadge($data, $email)
    {
        // TODO: Implement getBadge() method.
    }
}