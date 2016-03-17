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
    abstract public function noOfFixesIsEnough($noOfFixes);

    public function getBadge($data, $email)
    {
        $commitsPerUser = $data["commits_per_user"];

        foreach ($commitsPerUser as $key => $user) {
            $commits = $user["commits"];

            if ($user["email"] === $email) {
                foreach ($commits as $index => $value) {
                    $reviewsPerCommit = $data["reviews_per_commit"];
                    $commit = $reviewsPerCommit[$index];

                    $noOfFixes = count($commit["revisions"]);
                    return $this->noOfFixesIsEnough($noOfFixes);
                }

                return false;
            }

        }
        return false;
    }
}