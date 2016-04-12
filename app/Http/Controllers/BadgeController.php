<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 18.03.16
 * Time: 00:24
 */

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Project;
use App\Services\Analyzer;
use App\Services\Analyzer\Gerrit\Badges\AbstractBadge;
use Cache;

class BadgeController extends Controller
{
    public static function getAllBadges()
    {
        $badges = array(
            new \App\Services\Analyzer\Gerrit\Badges\BiggestProgessInRankingBadge(),
            new \App\Services\Analyzer\Gerrit\Badges\HardWorkingBadge(),
            new \App\Services\Analyzer\Gerrit\Badges\ManyChangesInOneDayBadge(),
            new \App\Services\Analyzer\Gerrit\Badges\ManyReviewsInOneDayBadge(),
            new \App\Services\Analyzer\Gerrit\Badges\MostChangesBadge(),
            new \App\Services\Analyzer\Gerrit\Badges\MostCommentsPerChangeBadge(),
            new \App\Services\Analyzer\Gerrit\Badges\MostReviewsBadge(),
            new \App\Services\Analyzer\Gerrit\Badges\PerfectQualityChangeBadge(),
            new \App\Services\Analyzer\Gerrit\Badges\PoorQualityChangeBadge()
            );

        return $badges;
    }

    public static function getBadges($projectName, $userEmail)
    {
        $from = date('Y-m-d', strtotime("-1 week"));;
        $to = date("Y-m-d", time() + 86400);
        return BadgeController::getBadgesForPeriod($projectName, $userEmail, $from, $to);
    }

    public static function getBadgesForPeriod($projectName, $userEmail, $from, $to)
    {
        if (Cache::has('cachedBadges-'.$projectName.'-'.$userEmail.'-'.$from.'-'.$to)) {

            return Cache::get('cachedBadges-'.$projectName.'-'.$userEmail.'-'.$from.'-'.$to);

        } else {

            $dataFromLastWeek = BadgeController::generateApi($projectName, $from, $to);

            $badges = BadgeController::getAllBadges();

            $rewardedBadges = [];

            foreach ($badges as $index => $badge) {
                /** @var AbstractBadge $badge */
                $badge->checkBadge($dataFromLastWeek, $userEmail);

                if ($badge->times > 0) {
                    $rewardedBadges[] = $badge;
                }
            }

            //usort($rewardedBadges, array($this, "compareBadges"));
            $rankingScreen = new \App\Services\Analyzer\Gerrit\Badges\RankingBadge();

            $api = [
                "ranking" => $rankingScreen->getRank($dataFromLastWeek, $userEmail),
                "badges" => $rewardedBadges
            ];

            Cache::put('cachedBadges-'.$projectName.'-'.$userEmail.'-'.$from.'-'.$to, $api, 10);

            return $api;
        }
    }

    public static function compareBadges($badgeA, $badgeB)
    {
        return $badgeA->times < $badgeB->times;
    }

    public static function generateApi($name, $from, $to)
    {
        //proper format 2015-01-16
        //echo str_replace('&2F;', '/', $name);exit;
        $project = Project::where('name', str_replace('&2F;', '/', $name))->firstOrFail();

        $analyzer = new Analyzer();

        $analyzer->reBuildAnalyzerForApi();
        $results = $analyzer->analyze($project, $from, $to);

        return $results;
    }

}
