<?php

namespace App\Services\Analyzer\Stash;

use App\Project;
use App\Services\Analyzer\CommitCountTitle;

class ReviewsPerUser extends AbstractAnalyzer
{
	public function getLabel($results)
	{
		return sprintf('%s (%d)', (string)$this, array_sum(array_map(function($item){
			return count($item['commits']);
		}, $results)));
	}

	public function __toString()
	{
		return 'Liczba sprawdzonych zmian';
	}

	protected function decode($result)
	{
		return json_decode($result);
	}

	public function analyze(Project $project, $from, $to)
	{
		$pageStart = 0;
		$uri = '/pull-requests?state=all&order=newest&avatarSize=30&limit=500';
		$from = strtotime($from) * 1000;
		$to = (strtotime($to) + 24*3600) * 1000;

		$results = [];

		do {
			$result = $this->fetch($project, $uri.'&start='.$pageStart);

			if (!isset($result->values)) {
				return $results;
			}

			$isNotLastPage = !(isset($result->isLastPage) && $result->isLastPage);

			// Filter start time
			$pullRequests = array_filter($result->values, function ($item) use ($to) {
				return $item->updatedDate <= $to;
			});

			foreach ($pullRequests as $pullRequest) {
				if (!($pullRequest->updatedDate <= $to && $pullRequest->updatedDate >= $from)) {
					continue;
				}

				foreach ($pullRequest->reviewers as $reviewer) {
					$user = $reviewer->user;

					if (!isset($results[$user->name])) {
						$name = isset($user->displayName) ? $user->displayName : $user->name;
						$results[$user->name] = [
							'username' => $user->name,
							'name' => $name,
							'avatar' => preg_match('@^http(s)?://.*@', $user->avatarUrl) != 1 ? $project->getAttribute('url').$user->avatarUrl : $user->avatarUrl,
							'commits' => [],
						];
					}

					$results[$user->name]['commits'][$pullRequest->id] = $pullRequest->title;
				}
			}

			if ($isNotLastPage) {
				$pageStart = $result->nextPageStart;
			}
		} while ($isNotLastPage && isset($pullRequest) && $pullRequest->updatedDate >= $from);

		$results = array_filter($results, function($item){
			return count($item['commits']) > 0;
		});

		foreach ($results as &$result) {
			$result['count'] = count($result['commits']);
		}

		usort($results, function($a, $b){
			return $b['count'] - $a['count'];
		});

		return $results;
	}

	public function getResults($results, Project $project)
	{
		return view('review._list', ['results' => $results, 'analyzer' => $this, 'project' => $project]);
	}

	public function getContent($result, Project $project)
	{
		return view('review.stash.changes._reviews_per_user', ['result' => $result, 'project' => $project]);
	}
}
