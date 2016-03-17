<?php

namespace App\Services\Analyzer\Gerrit\Badges;

class BiggestProgessInRankingBadge extends AbstractBadge
{
    public function __construct()
    {
        parent::__construct("✪", "You've made biggest progress in ranking");
    }

    public function getBadge($data, $email)
    {
        $commitsPerUser = $data["ranking_overall"];

        $winner = null;
        $maxRanking = 0.0;

        foreach ($commitsPerUser as $key => $commit) {
            $ranking = $commit["value"];

            if ($ranking > $maxRanking) {
                $winner = $commit;
                $maxRanking = $ranking;
            }
        }

        if (is_null($winner) == false)
            return $winner["email"] === $email;
        else
            return false;
    }
}

