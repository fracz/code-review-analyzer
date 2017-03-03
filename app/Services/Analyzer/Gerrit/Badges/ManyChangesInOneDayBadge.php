<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 17:55
 */

namespace App\Services\Analyzer\Gerrit\Badges;


class ManyChangesInOneDayBadge extends AbstractBadge
{

    public function __construct()
    {
        parent::__construct(
            "Fast worker",
            "At least 3 changes in one day",
            "⊰", "<i class=\"fa fa-rocket\" style=\"color:green\"></i>", "FastWorker"
        );
    }

    public function checkBadge($data, $email)
    {
        $changesPerReview = $data["commits_per_user"];
        $map = [];

        foreach ($changesPerReview as $key => $user) {

            if ($user["email"] === $email) {
                $commits = $user["commits"];

                foreach ($commits as $index => $value) {
                    $reviewsPerCommit = $data["reviews_per_commit"];
                    $commit = $reviewsPerCommit[$index];
                    $date = $commit["create_date"];

                    if (strlen($date) >= 10)
                        $date = substr($date, 0, 10);

                    if (array_key_exists($date, $map)) {
                        $map[$date] = $map[$date] + 1;
                        if ($map[$date] == 3){
                            //$map[$date] -= 3;
                            $this->times += 1;
                        }
                    } else
                        $map[$date] = 1;
                }


                return;
            }

        }

    }
}
