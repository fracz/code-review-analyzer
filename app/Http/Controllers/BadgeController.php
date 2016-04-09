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
use Cache;

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
            new \App\Services\Analyzer\Gerrit\Badges\PoorQualityChangeBadge()
            );

        return $badges;
    }

    public function getBadges($projectName, $userEmail)
    {
        $from = date('Y-m-d', strtotime("-1 week"));;
        $to = date("Y-m-d", time() + 86400);
        return $this->getBadgesForPeriod($projectName, $userEmail, $from, $to);
    }

    public function getBadgesForPeriod($projectName, $userEmail, $from, $to)
    {
		session_write_close();
		
        if (Cache::has('cachedBadges-'.$projectName.'-'.$userEmail.'-'.$from.'-'.$to)) {
			
            return Cache::get('cachedBadges-'.$projectName.'-'.$userEmail.'-'.$from.'-'.$to);

        } else {
            $dataFromLastWeek = $this->generateApi($projectName, $from, $to);

			if($dataFromLastWeek == null){
				header("HTTP/1.0 404 Not Found");
				echo "<h1>Error 404 Not Found</h1>";
				echo "The project that you have requested for could not be found.";
			}
			
            $badges = $this->getAllBadges();

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
				"formula" => $rankingScreen->getFormula($dataFromLastWeek, $userEmail),
                "badges" => $rewardedBadges
            ];

            Cache::put('cachedBadges-'.$projectName.'-'.$userEmail.'-'.$from.'-'.$to, $api, 10);

            return $api;
        }
    }

    public function compareBadges($badgeA, $badgeB)
    {
        return $badgeA->times < $badgeB->times;
    }

    public function generateApi($name, $from, $to)
    {
        //proper format 2015-01-16
        //echo str_replace('&2F;', '/', $name);exit;
        $project = Project::where('name', str_replace('&2F;', '/', $name))->first();

		if(!$project)
			return null;

        $this->analyzerService->reBuildAnalyzerForApi();
        $results = $this->analyzerService->analyze($project, $from, $to);

        return $results;
    }

}
