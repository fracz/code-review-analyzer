<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 17:54
 */

namespace App\Services\Analyzer\Gerrit\Badges;


class HardWorkingBadge extends AbstractBadge
{
    public function __construct()
    {
        parent::__construct("◈", "You've been working every day on the project");
    }

    public function getBadge($results, $email)
    {
        // TODO: Implement getBadge() method.
    }
}