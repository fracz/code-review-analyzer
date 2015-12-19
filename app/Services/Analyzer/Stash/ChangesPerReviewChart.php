<?php

namespace App\Services\Analyzer\Stash;

use App\Project;
use App\Services\Analyzer\StringTitle;

class ChangesPerReviewChart extends AbstractAnalyzer
{
	use StringTitle;

	public function __toString()
	{
		return 'Średnia liczba komentarzy na zmianę - wykres';
	}

	protected function decode($result)
	{
		return json_decode($result);
	}

	public function analyze(Project $project, $from, $to)
	{
		$results = [
			'x' => 'x',
			'columns' => [
				['x'],
				['user'],
				['average'],
			],
			'names' => [
				'user' => 'Użytkownik',
				'average' => 'Średnia',
			],
			'type' => 'bar',
			'types' => [
				'average' => 'line',
			],
		];

		$labels = [];
		$values = [];

		$pageStart = 0;
		$uri = '/pull-requests?state=all&order=newest&avatarSize=30&limit=500';
		$from = strtotime($from) * 1000;
		$to = (strtotime($to) + 24*3600) * 1000;

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
				$name = isset($user->displayName) ? $user->displayName : $user->name;

				if (!in_array($name, $labels)) {
					$labels[$user->name] = $name;
					$values[$user->name] = [
						'commits' => 0,
						'messages' => 0,
					];
				}

				$values[$user->name]['commits'] += 1;

				$commentsPageStart = 0;
				$commentsUri = '/pull-requests/'.$pullRequest->id.'/activities?fromType=comment&avatarSize=30&limit=500';

				do {
					$comments = $this->fetch($project, $commentsUri.'&start='.$commentsPageStart);

					if (!isset($comments->values)) {
						continue;
					}

					$isNotLastCommentsPage = !(isset($comments->isLastPage) && $comments->isLastPage);

					// Filter only comments from activity feed
					$comments->values = array_filter($comments->values, function($item){
						return $item->action === 'COMMENTED';
					});

					$values[$user->name]['messages'] += count($comments->values);

					if ($isNotLastCommentsPage) {
						$commentsPageStart = $comments->nextPageStart;
					}
				} while ($isNotLastCommentsPage);
			}

			if ($isNotLastPage) {
				$pageStart = $result->nextPageStart;
			}
		} while ($isNotLastPage && isset($pullRequest) && $pullRequest->updatedDate >= $from);

		if (count($values) > 0) {
			$average = round(array_sum(array_map(
					function ($value){
						return $value['messages'] / (float)$value['commits'];
					},
					$values
				)) / (float)count($values), 2);

			foreach ($values as $user => $value) {
				$results['columns'][0][] = $labels[$user];
				$results['columns'][1][] = round($value['messages'] / $value['commits'], 2);
				$results['columns'][2][] = $average;
			}
		}

		return $results;
	}

	public function getResults($results, Project $project)
	{
		return view('review.stash.statistics._changes_per_review_chart', ['results' => $results]);
	}

	public function getContent($result, Project $project)
	{
		return '';
	}
}
