<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 18.03.16
 * Time: 00:24
 */

namespace App\Http\Controllers;

use App\Person;
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
            new \App\Services\Analyzer\Gerrit\Badges\PoorQualityChangeBadge(),
            new \App\Services\Analyzer\Gerrit\Badges\ThrowawayCodeBadge(),
            new \App\Services\Analyzer\Gerrit\Badges\CircumspectBadge(),
            new \App\Services\Analyzer\Gerrit\Badges\SculptorBadge(),
            new \App\Services\Analyzer\Gerrit\Badges\NightOwlBadge(),
            new \App\Services\Analyzer\Gerrit\Badges\DonorBadge(),
            new \App\Services\Analyzer\Gerrit\Badges\SelfReviewerBadge()
        );

        return $badges;
    }

    public function getBadges($projectName, $userEmail)
    {
        $project = Project::where('name', str_replace('&2F;', '/', $projectName))->first();

        if (!$project)
            return null;

        $from = date('Y-m-d', strtotime("-" . $project->badges_period . " day"));;
        $to = date("Y-m-d", time() + 86400);
        return $this->getBadgesForPeriod($projectName, $userEmail, $from, $to, true);
    }

    public function getBadgesWithoutCache($projectName, $userEmail)
    {
        $project = Project::where('name', str_replace('&2F;', '/', $projectName))->first();

        if (!$project)
            return null;

        $from = date('Y-m-d', strtotime("-" . $project->badges_period . " day"));;
        $to = date("Y-m-d", time() + 86400);
        return $this->getBadgesForPeriod($projectName, $userEmail, $from, $to, false);
    }

    public function getUserBadgesWithoutCache($userEmail)
    {
        return $this->getUserBadgesCheck($userEmail, false);
    }

    public function getAllUserBadges()
    {
        $persons = Person::all();
        $result = [];
        foreach ($persons as $person) {
            $result[] = array_merge($this->getUserBadges($person->email), [
                'email' => $person->email,
                'name' => $person->name,
                'username' => $person->username,
            ]);
        }
        return $result;
    }

    public function getUserBadges($userEmail)
    {
        return $this->getUserBadgesCheck($userEmail, true);
    }

    public function getUserBadgesCheck($userEmail, $cache)
    {
        session_write_close();

        $projects = Project::all();
        $allUserBadges = [];
        $sumAllBadges = [];
        $sumAllBadges['ranking'] = 0;
        $sumAllBadges['ranking_projects'] = [];
        $sumAllBadges['achievements'] = [];
        $sumAllBadges['badges'] = [];

        foreach ($projects as $project) {
            if ($cache) {
                $allUserBadges[$project->getAttribute('name')] = $this->getBadges($project->getAttribute('name'), $userEmail);
            } else {
                $allUserBadges[$project->getAttribute('name')] = $this->getBadgesWithoutCache($project->getAttribute('name'), $userEmail);
            }

        }

        foreach ($allUserBadges as $projName => $projectBadges) {
            $sumAllBadges['ranking'] += $projectBadges['ranking'];
            $sumAllBadges['ranking_projects'][$projName] = $projectBadges['ranking'];

            //echo $projName. " " . count($projectBadges['achievements']). "<br/>";
            //unset($projectBadges['achievements']['patchset']);


            if ($projectBadges['achievements'] != "") {
                foreach ($projectBadges['achievements'] as $name => $achivData) {

                    //bo $name moze byc w liczbie pojedynczej lub mnogiej
                    if (substr($name, -1) != "s")
                        $name = $name . "s";

                    if (!isset($sumAllBadges['achievements'][$name]))
                        $sumAllBadges['achievements'][$name] = [];

                    $sumAllBadges['achievements'][$name]['weight'] = $achivData['weight'];


                    if (!isset($sumAllBadges['achievements'][$name]['times'])) {
                        $sumAllBadges['achievements'][$name]['times'] = $achivData['times'];
                    } else {
                        $sumAllBadges['achievements'][$name]['times'] += $achivData['times'];
                    }

                    if (!isset($sumAllBadges['achievements'][$name]['projects']))
                        $sumAllBadges['achievements'][$name]['projects'] = [];

                    $sumAllBadges['achievements'][$name]['projects'][$projName] = $achivData['times'];
                }
            }

            if (count($projectBadges['badges']) > 0) {
                foreach ($projectBadges['badges'] as $badgeData) {
                    //print_r($badgeData);exit;
                    if (!isset($sumAllBadges['badges'][$badgeData->id])) {
                        $sumAllBadges['badges'][$badgeData->id] = [];
                        $sumAllBadges['badges'][$badgeData->id]['awesomeFont'] = $badgeData->awesomeFont;
                        $sumAllBadges['badges'][$badgeData->id]['name'] = $badgeData->name;
                        $sumAllBadges['badges'][$badgeData->id]['description'] = $badgeData->description;
                        $sumAllBadges['badges'][$badgeData->id]['times'] = $badgeData->times;
                        $sumAllBadges['badges'][$badgeData->id]['projects'] = [];
                        $sumAllBadges['badges'][$badgeData->id]['projects'][$projName] = $badgeData->times;

                    } else {
                        $sumAllBadges['badges'][$badgeData->id]['times'] += $badgeData->times;
                        $sumAllBadges['badges'][$badgeData->id]['projects'][$projName] = $badgeData->times;
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

    public function getDaysForProject($projectName)
    {
        $project = Project::where('name', str_replace('&2F;', '/', $projectName))->first();
        if (!$project)
            return null;

        return $project->badges_period;
    }

    public function getBadgesForPeriod($projectName, $userEmail, $from, $to, $useCache)
    {
        session_write_close();

        if (Cache::has('cachedBadges-' . $projectName . '-' . $userEmail . '-' . $from . '-' . $to) && $useCache != false) {

            return Cache::get('cachedBadges-' . $projectName . '-' . $userEmail . '-' . $from . '-' . $to);

        } else {
            $dataFromLastWeek = $this->generateApi($projectName, $from, $to, false);

            if ($dataFromLastWeek == null) {
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
                "achievements" => $rankingScreen->getAchievements($dataFromLastWeek, $userEmail),
                "badges" => $rewardedBadges
            ];

            Cache::forever('cachedBadges-' . $projectName . '-' . $userEmail . '-' . $from . '-' . $to, $api);

            return $api;
        }
    }

    public function getProjectBadgesForLastPeriod($name)
    {
        session_write_close();
        $project = Project::where('name', str_replace('&2F;', '/', $name))->firstOrFail();
        if (!$project)
            return null;

        $to = date('Y-m-d', strtotime(' +1 day'));
        $noOfDays = $project->badges_period;
        $from = date('Y-m-d', strtotime(' -' . $noOfDays . ' day'));
        return $this->getProjectBadges($name, $from, $to);
    }

    public function getProjectBadges($projectName, $from, $to)
    {
        $dataFromLastWeek = $this->generateApi($projectName, $from, $to, true);

        if ($dataFromLastWeek == null) {
            header("HTTP/1.0 404 Not Found");
            echo "<h1>Error 404 Not Found</h1>";
            echo "The project that you have requested for could not be found.";
        }

        $rankingOverall = $dataFromLastWeek["ranking_overall"];
        $results = [];

        foreach ($rankingOverall as $index => $data) {
            $results[] = [
                'name' => $data["name"],
                'email' => $data["email"],
                'avatar' => $data["avatar"],
                'achievements' => $this->getBadges($projectName, $data["email"])
            ];

        }
        return $results;
    }

    public function compareBadges($badgeA, $badgeB)
    {
        return $badgeA->times < $badgeB->times;
    }

    public function generateApi($name, $from, $to, $useCache)
    {
        //proper format 2015-01-16
        //echo str_replace('&2F;', '/', $name);exit;
        $project = Project::where('name', str_replace('&2F;', '/', $name))->first();

        if (!$project)
            return null;

        $results = $this->analyzerService->analyze($project, $from, $to, $useCache);

        return $results;
    }

}
