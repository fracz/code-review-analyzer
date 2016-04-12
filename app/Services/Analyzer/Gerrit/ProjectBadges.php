<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 12.04.16
 * Time: 13:57
 */

namespace App\Services\Analyzer\Gerrit;

use App\Http\Controllers\BadgeController;
use App\Project;
use App\Services\Analyzer\StringTitle;

class ProjectBadges extends AbstractAnalyzer
{
    use StringTitle;

    public function __toString()
    {
        return 'Odznaki w projekcie';
    }

    protected function decode($result)
    {
        return json_decode(substr($result, 4));
    }

    public function analyze(Project $project, $from, $to)
    {
        //echo "echo from ReviewPairs";exit;
        //$this->collectDataForReview($project, $from, $to);

        $result = \App\Commit::where('project', $project->getAttribute('name'))
            ->where('updated', '>=', $from)
            ->where('updated', '<=', $to)->get();

        $results = [];
        $commits = [];

        foreach ($result as $commit) {

            if (in_array($commit->owner->_account_id, $commits) == false) {
                $projectName = str_replace('/', '&2F;', $project->getAttribute('name'));
                $badges = '';//BadgeController::getBadges($projectName, $commit->owner->email);

                $results[] = [
                    'username' => $commit->owner->username,
                    'name' => $commit->owner->name,
                    'email' => $commit->owner->email,
                    'avatar' => (object)['url' => $commit->owner->avatars->first()->url,
                        'height' => $commit->owner->avatars->first()->height],
                    'achievements' => $badges,
                ];
                $commits[] = $commit->owner->_account_id;
            }

        }

        return $results;
    }

    public function getResults($results, Project $project)
    {
        return view('review._list', ['results' => $results, 'analyzer' => $this, 'project' => $project]);
    }

    public function getContent($result, Project $project)
    {
        return view('review.gerrit.review_pairs._results', ['result' => $result, 'project' => $project]);
    }
}
