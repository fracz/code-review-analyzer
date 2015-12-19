<?php

namespace App\Services\Analyzer\Stash;

use App\Project;
use App\Services\Analyzer\StringTitle;

class ReviewPairs extends AbstractAnalyzer
{
	use StringTitle;

	public function __toString()
	{
		return 'Pary sprawdzające';
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

				$user = $pullRequest->author->user;

				if (!isset($results[$user->name])) {
					$name = isset($user->displayName) ? $user->displayName : $user->name;
					$results[$user->name] = [
						'username' => $user->name,
						'name' => $name,
						'avatar' => preg_match('@^http(s)?://.*@', $user->avatarUrl) != 1 ? $project->getAttribute('url').$user->avatarUrl : $user->avatarUrl,
						'count' => 0,
						'commits' => 0,
						'pairs' => [],
					];
				}

				foreach ($pullRequest->reviewers as $reviewer) {
					$reviewUser = $reviewer->user;

					if (!isset($results[$user->name]['pairs'][$reviewUser->name])) {
						$name = isset($reviewUser->displayName) ? $reviewUser->displayName : $reviewUser->name;
						$results[$user->name]['pairs'][$reviewUser->name] = [
							'count' => 0,
							'username' => $reviewUser->name,
							'name' => $name,
							'commits' => [],
						];
					}

					$results[$user->name]['pairs'][$reviewUser->name]['count'] += 1;
					$results[$user->name]['pairs'][$reviewUser->name]['commits'][$pullRequest->id] = $pullRequest->title;
				}
			}

			if ($isNotLastPage) {
				$pageStart = $result->nextPageStart;
			}
		} while ($isNotLastPage && isset($pullRequest) && $pullRequest->updatedDate >= $from);


		foreach ($results as &$result) {
			$result['pairs'] = array_filter($result['pairs'], function($item){
				return $item['count'] > 0;
			});

			$result['count'] = count($result['pairs']);

			usort($result['pairs'], function ($a, $b){
				return $b['count'] - $a['count'];
			});

			foreach ($result['pairs'] as &$pair) {
				$result['commits'] += count($pair['commits']);
			}
		}

		$results = array_filter($results, function($item){
			return $item['count'] > 0;
		});

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
		return view('review.stash.review_pairs._results', ['result' => $result, 'project' => $project]);
	}
}
