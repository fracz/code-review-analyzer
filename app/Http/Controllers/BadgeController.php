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
use App\Services\Analyzer\Gerrit\Badges\AbstractBadge;
use App\Services\AnalyzerInterface;

class BadgeController extends Controller
{
    private $analyzerService;

    function __construct(AnalyzerInterface $analyzerService)
    {
        $this->analyzerService = $analyzerService;
    }

    public function getAllBadges()
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
            new \App\Services\Analyzer\Gerrit\Badges\PourQualityChangeBadge()
        );

        return $badges;
    }

    public function getBadges($projectName, $userEmail)
    {
        $from = date('Y-m-d', strtotime("-1 week"));;
        $to = date("Y-m-d", time() + 86400);
        return $this->getBadgesForPeriod($projectName, $userEmail, $from, $to);
    }

    public function getBadgesForPeriodForAllUsers($projectName, $from, $to)
    {
        $data = $this->generateApi($projectName, $from, $to);
        $rankingOverall = $data["ranking_overall"];
        $result = [];

        foreach ($rankingOverall as $key => $user) {
            $email = $user["email"];
            $badges = $this->getBadgesForPeriod($projectName, $email, $from, $to);
            $result[$email] = $badges;
        }

        return $result;
    }

    public function getBadgesForPeriod($projectName, $userEmail, $from, $to)
    {
        $dataFromLastWeek = $this->generateApi($projectName, $from, $to);

        $badges = $this->getAllBadges();

        $rewardedBadges = [];

        foreach ($badges as $index => $badge) {
            /** @var AbstractBadge $badge */
            if ($badge->getBadge($dataFromLastWeek, $userEmail)) {
                $rewardedBadges[] = $badge;
            }
        }

        $rankingScreen = new \App\Services\Analyzer\Gerrit\Badges\RankingScreen();

        $api = [
            "ranking" => $rankingScreen->getRank($dataFromLastWeek, $userEmail),
            "badges" => $rewardedBadges
        ];

        return $api;
    }

    public function generateApi($name, $from, $to)
    {
        //proper format 2015-01-16
        //echo str_replace('&2F;', '/', $name);exit;
        $project = Project::where('name', str_replace('&2F;', '/', $name))->firstOrFail();

        $this->analyzerService->reBuildAnalyzerForApi();
        $results = $this->analyzerService->analyze($project, $from, $to);

        return $results;
    }

}