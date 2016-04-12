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
		$project = Project::where('name', str_replace('&2F;', '/', $projectName))->first();

		if(!$project)
			return null;
		
        $from = date('Y-m-d', strtotime("-" . $project->badges_period . " day"));;
        $to = date("Y-m-d", time() + 86400);
        return BadgeController::getBadgesForPeriod($projectName, $userEmail, $from, $to);
    }
	
	public function getUserBadges($userEmail)
	{
		session_write_close();
		
		$projects = Project::all();
		$allUserBadges = [];
		$sumAllBadges = [];
		$sumAllBadges['ranking'] = 0;
		$sumAllBadges['achievements'] = [];
		$sumAllBadges['badges'] = [];

		foreach ($projects as $project){
			
			$allUserBadges[$project->getAttribute('name')] = $this->getBadges($project->getAttribute('name'), $userEmail);
		}
		
		foreach($allUserBadges as $projName => $projectBadges){
			$sumAllBadges['ranking'] += $projectBadges['ranking'];
			
			//echo $projName. " " . count($projectBadges['badges']). "<br/>";
			
			if(count($projectBadges['achievements']) > 1)
			{
				foreach($projectBadges['achievements'] as $name => $achivData){
				
					//bo $name moze byc w liczbie pojedynczej lub mnogiej
					if(substr($name, -1) != "s")
						$name = $name."s";
					
					if(!isset($sumAllBadges['achievements'][$name]))
						$sumAllBadges['achievements'][$name] = [];
					
					$sumAllBadges['achievements'][$name]['weight'] = $achivData['weight'];
					
					
					if(!isset($sumAllBadges['achievements'][$name]['times'])) {
						$sumAllBadges['achievements'][$name]['times'] = $achivData['times'];
					}
					else {
						$sumAllBadges['achievements'][$name]['times'] += $achivData['times'];
					}
						
					if(!isset($sumAllBadges['achievements'][$name]['projects']))
						$sumAllBadges['achievements'][$name]['projects'] = [];
					
					array_push($sumAllBadges['achievements'][$name]['projects'], $projName);
				}
			}
			
			if(count($projectBadges['badges']) > 0)
			{
				foreach($projectBadges['badges'] as $badgeData){
					//print_r($badgeData);exit;
					if(!isset($sumAllBadges['badges'][$badgeData->name])){
						$sumAllBadges['badges'][$badgeData->name] = [];
						$sumAllBadges['badges'][$badgeData->name]['awesomeFont'] = $badgeData->awesomeFont;
						$sumAllBadges['badges'][$badgeData->name]['name'] = $badgeData->name;
						$sumAllBadges['badges'][$badgeData->name]['description'] = $badgeData->description;
						$sumAllBadges['badges'][$badgeData->name]['times'] = $badgeData->times;
						$sumAllBadges['badges'][$badgeData->name]['projects'] = [];
						array_push($sumAllBadges['badges'][$badgeData->name]['projects'], $projName);
						
					} else {
						$sumAllBadges['badges'][$badgeData->name]['times'] += $badgeData->times;
						array_push($sumAllBadges['badges'][$badgeData->name]['projects'], $projName);
					}
						
					
					
					
				}
			}
			
			//print_r($sumAllBadges);echo "<br/><Br/><Br/>";
		}
		
		//powrot do liczbyp pojedynczej zmian. Uwaga -> zmienia kolejnosc w tablicy wiec 
		//poki to nie jest wymaganiem =zakomentowane 
		
		/*foreach($sumAllBadges['achievements'] as $name => $val){
			if($val['times'] <= 1){
				$sumAllBadges['achievements'][substr($name, 0, -1)] = $sumAllBadges['achievements'][$name];
				unset($sumAllBadges['achievements'][$name]);
			}
		}*/
		
		return $sumAllBadges;
	}

    public static function getBadgesForPeriod($projectName, $userEmail, $from, $to)
    {
		session_write_close();
		
        if (Cache::has('cachedBadges-'.$projectName.'-'.$userEmail.'-'.$from.'-'.$to)) {
			
            return Cache::get('cachedBadges-'.$projectName.'-'.$userEmail.'-'.$from.'-'.$to);

        } else {


            $dataFromLastWeek = BadgeController::generateApi($projectName, $from, $to);

			if($dataFromLastWeek == null){
				header("HTTP/1.0 404 Not Found");
				echo "<h1>Error 404 Not Found</h1>";
				echo "The project that you have requested for could not be found.";
			}
			
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
				"achievements" => $rankingScreen->getAchievements($dataFromLastWeek, $userEmail),
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
        $project = Project::where('name', str_replace('&2F;', '/', $name))->first();

		if(!$project)
			return null;

        $analyzer = new Analyzer();

        $analyzer->reBuildAnalyzerForApi();
        $results = $analyzer->analyze($project, $from, $to);

        return $results;
    }

}
