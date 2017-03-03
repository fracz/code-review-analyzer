<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 17:54
 */

namespace App\Services\Analyzer\Gerrit\Badges;


use DateTime;

class HardWorkingBadge extends AbstractBadge
{
    public function __construct()
    {
        parent::__construct(
            "Regular worker",
            "At least one contribution everyday",
            "◈", "<i class=\"fa fa-bar-chart\" style=\"color:purple\"></i>", "RegularWorker"
        );
    }

    public function checkBadge($data, $email)
    {
        $changesPerReview = $data["all_commits_per_user"];
        $map = [];
        $changeMadeIndexDayAgo = [false, false, false, false, false, false, false];

        foreach ($changesPerReview as $key => $user) {

            if ($user["email"] === $email) {
                $commits = $user["commits"];

                foreach ($commits as $index => $value) {
                    $reviewsPerCommit = $data["reviews_per_commit"];
                    $commit = $reviewsPerCommit[$index];
					
					foreach($commit['revisions'] as $revision){
						$dateString = $revision["create_date"];
						
						 if (strlen($dateString) >= 10)
							$dateString = substr($dateString, 0, 10);

						if (array_key_exists($dateString, $map)) {
							$map[$dateString] = $map[$dateString] + 1;
						} else
							$map[$dateString] = 1;
						
					}
					
					foreach($commit['code_reviews'] as $review){
						$dateString = $review["review_date"];
						
						 if (strlen($dateString) >= 10)
							$dateString = substr($dateString, 0, 10);

						if (array_key_exists($dateString, $map)) {
							$map[$dateString] = $map[$dateString] + 1;
						} else
							$map[$dateString] = 1;
					}
                }
				
				
				foreach($map as $date => $times){
					$today = date("y-m-d");
					$dateDiff = date_diff(new DateTime($today), new DateTime($date));
					$daysAgo = intval($dateDiff->format('%d'));

					if ($daysAgo < 7)
						$changeMadeIndexDayAgo[$daysAgo] = true;
				}
				
				//skip saturdays & sundays
				$today = date("y-m-d");
				$dayofweek = date('N', strtotime($today));
				
				//sobota
				if($dayofweek == 6){
					$changeMadeIndexDayAgo[0] = 1;
				} else if ($dayofweek == 7) {  //niedziela
					$changeMadeIndexDayAgo[1] = 1;
				} else if ($dayofweek == 5) {
					$changeMadeIndexDayAgo[5] = 1; //5 dni wstecz byla niedziela wiec zazanaczam ja na = 1
					$changeMadeIndexDayAgo[6] = 1;
				} else if ($dayofweek == 4) {
					$changeMadeIndexDayAgo[4] = 1;
					$changeMadeIndexDayAgo[5] = 1;
				} else if ($dayofweek == 3) {
					$changeMadeIndexDayAgo[3] = 1;
					$changeMadeIndexDayAgo[4] = 1;
				} else if ($dayofweek == 2) {
					$changeMadeIndexDayAgo[2] = 1;
					$changeMadeIndexDayAgo[3] = 1;
				} else if ($dayofweek == 1) {
					$changeMadeIndexDayAgo[1] = 1;
					$changeMadeIndexDayAgo[2] = 1;
				}
				
				//always current day is skipped
				$changeMadeIndexDayAgo[0] = 1;
				
				//print_r($changeMadeIndexDayAgo);exit;
				
				
				if($this->checkArray($changeMadeIndexDayAgo))
					$this->times = 1;
            }

        }

        return false;
    }

    public function checkArray($array)
    {
        return $array[0] == true and $array[1] == true and $array[2] == true
        and $array[3] == true and $array[4] == true and $array[5] == true
        and $array[6] == true;
    }
}
