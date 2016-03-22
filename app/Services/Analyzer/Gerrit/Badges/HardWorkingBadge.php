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
        parent::__construct("◈", "You've been working every day on the project");
    }

    public function checkBadge($data, $email)
    {
        $changesPerReview = $data["commits_per_user"];
        $map = [];
        $changeMadeIndexDayAgo = [false, false, false, false, false, false, false];

        foreach ($changesPerReview as $key => $user) {

            if ($user["email"] === $email) {
                $commits = $user["commits"];

                foreach ($commits as $index => $value) {
                    $reviewsPerCommit = $data["reviews_per_commit"];
                    $commit = $reviewsPerCommit[$index];
                    $dateString = $commit["create_date"];

                    if (strlen($dateString) >= 10)
                        $dateString = substr($dateString, 0, 10);

                    if (array_key_exists($dateString, $map)) {
                        $today = date("y-m-d");
                        $dateDiff = date_diff(new DateTime($today), new DateTime($dateString));
                        $daysAgo = intval($dateDiff->format('%d'));

                        if ($daysAgo < 7)
                            $changeMadeIndexDayAgo[$daysAgo] = true;

                        $map[$dateString] = $map[$dateString] + 1;
                    } else
                        $map[$dateString] = 1;
                }

                $this->times = $this->checkArray($changeMadeIndexDayAgo);
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
