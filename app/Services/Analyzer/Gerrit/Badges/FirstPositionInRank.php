<?php

namespace App\Services\Analyzer\Gerrit\Badges;

class FirstPositionInRank extends AbstractBadge
{
    public function getBadge($results, $email) {
        print_r($results);

        // w $results masz analize danego projektu z ostatniego tygodnia
        return "AAA";
    }
}

