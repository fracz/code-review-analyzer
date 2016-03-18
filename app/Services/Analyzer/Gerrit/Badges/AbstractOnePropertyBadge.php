<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 19:39
 */

namespace App\Services\Analyzer\Gerrit\Badges;


abstract class AbstractOnePropertyBadge extends AbstractBadge
{
    private $category;
    private $property;

    public function __construct($icon, $description, $category, $property)
    {
        parent::__construct($icon, $description);
        $this->category = $category;
        $this->property = $property;
    }

    public function checkBadge($data, $email)
    {
        $commitsPerUser = $data[$this->category];

        $winner = null;
        $maxRanking = 0.0;

        foreach ($commitsPerUser as $key => $commit) {
            $ranking = $commit[$this->property];

            if ($ranking > $maxRanking) {
                $winner = $commit;
                $maxRanking = $ranking;
            }

        }

        if(is_null($winner) === false and $winner["email"] === $email)
            $this->times = 1;

    }
}