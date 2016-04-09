<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 18:59
 */

namespace App\Services\Analyzer\Gerrit\Badges;


class RankingBadge
{

    public function getRank($data, $email)
    {
        $commitsPerUser = $data["ranking_overall"];

        foreach ($commitsPerUser as $key => $commit) {
            $ranking = $commit["value"];

            if($commit["email"] === $email){
                return $ranking;
            }

        }

        return 0;
    }
	
	public function getFormula($data, $email)
    {
        $commitsPerUser = $data["ranking_overall"];

        foreach ($commitsPerUser as $key => $commit) {
            $formula = $commit["formula"];

            if($commit["email"] === $email){
                return $formula;
            }

        }

        return "";
    }
}