<?php

namespace App\Services\Analyzer\Stash;

use App\Project;
use App\Services\Analyzer\StringTitle;

class ReviewPairsGraph extends AbstractAnalyzer
{
	use StringTitle;

	public function __toString()
	{
		return 'Pary sprawdzające - graf';
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
		$nodes = [];
		$edges = [];

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
				if (!in_array($user->id, $nodes)) {
					$name = isset($user->displayName) ? $user->displayName : $user->name;
					$nodes[] = $user->id;
					$edges[$user->name] = [];
					$results[] = [
						'group' => 'nodes',
						'data' => [
							'id' => 'N'.$user->id,
							'label' => $name,
							'image' => preg_match('@^http(s)?://.*@', $user->avatarUrl) != 1 ? $project->getAttribute('url').$user->avatarUrl : $user->avatarUrl,
						],
					];
				}

				foreach ($pullRequest->reviewers as $reviewer) {
					$reviewUser = $reviewer->user;

					if (!isset($edges[$user->id][$reviewUser->id])) {
						$edges[$user->id][$reviewUser->id] = [];
					}

					$edges[$user->id][$reviewUser->id][] = $pullRequest->id;
				}
			}

			if ($isNotLastPage) {
				$pageStart = $result->nextPageStart;
			}
		} while ($isNotLastPage && isset($pullRequest) && $pullRequest->updatedDate >= $from);

		foreach ($edges as $source => $targets) {
			foreach ($targets as $target => $commits) {
				$results[] = [
					'group' => 'edges',
					'data' => [
						'id' => 'E'.$source.'_'.$target,
						'weight' => count(array_unique($commits)),
						'source' => 'N'.$source,
						'target' => 'N'.$target,
					],
				];
			}
		}

		return $results;
	}

	public function getResults($results, Project $project)
	{
		return view('review.stash.review_pairs._graph', ['results' => $results]);
	}

	public function getContent($result, Project $project)
	{
		return '';
	}
}
