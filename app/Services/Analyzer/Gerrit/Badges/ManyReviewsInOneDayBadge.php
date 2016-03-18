<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 17:56
 */

namespace App\Services\Analyzer\Gerrit\Badges;


class ManyReviewsInOneDayBadge extends AbstractBadge
{

    public function __construct()
    {
        parent::__construct("⊱", "You've made 3 reviews in one day");
    }

    public function checkBadge($data, $email)
    {
        $changesPerReview = $data["reviews_per_user"];
        $map = [];

        foreach ($changesPerReview as $key => $user) {

            if ($user["email"] === $email) {
                $commits = $user["commits"];

                foreach ($commits as $index => $value) {
                    $reviewsPerCommit = $data["reviews_per_commit"];
                    $commit = $reviewsPerCommit[$index];
                    $revisions = $commit["revisions"];

                    foreach($revisions as $key2 => $revision){
                        $reviewerEmail = $revision["owner_email"];
                        $date = $commit["create_date"];

                        if (strlen($date) >= 10)
                            $date = substr($date, 0, 10);

                        if($reviewerEmail === $email){
                            if (array_key_exists($date, $map)) {
                                $map[$date] = $map[$date] + 1;
                                if ($map[$date] >= 4){
                                    $map[$date] -= 4;
                                    $this->times += 1;
                                }

                            } else
                                $map[$date] = 1;

                        }

                    }

                }

                return;
            }

        }

    }
}
