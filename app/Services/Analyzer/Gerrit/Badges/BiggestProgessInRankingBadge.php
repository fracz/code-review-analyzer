<?php

namespace App\Services\Analyzer\Gerrit\Badges;

class BiggestProgessInRankingBadge extends AbstractBadge
{
    public function __construct()
    {
        parent::__construct("✪", "You've made biggest progress in ranking");
    }

    public function getBadge($results, $email) {
        print_r($results);

        // w $results masz analize danego projektu z ostatniego tygodnia
        return "AAA";
    }
}

