<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 18:59
 */

namespace App\Services\Analyzer\Gerrit\Badges;


class RankingScreen
{

    public function getRank($data, $email)
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
            return $maxRanking;
        else
            return 0.0;

    }
}