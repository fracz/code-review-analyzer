<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 20:09
 */

namespace App\Services\Analyzer\Gerrit\Badges;


abstract class QualityBadge extends AbstractBadge
{
    abstract public function checkCommit($noOfFixes);

    public function checkBadge($data, $email)
    {
        $commitsPerUser = $data["all_commits_per_user"];
		//print_r($commitsPerUser);exit;
        foreach ($commitsPerUser as $key => $user) {
            $commits = $user["commits"];

            if ($user["email"] === $email) {
                foreach ($commits as $index => $value) {
                    $reviewsPerCommit = $data["reviews_per_commit"];
                    $commit = $reviewsPerCommit[$index];

					//print_r($commit);echo "<br/><br/>";
                    if($this->checkCommit($commit)){
						//print_r($commit);echo "<br/><br/>";
                        $this->times += 1;
                    }
                }

                return;
            }

        }
    }
}