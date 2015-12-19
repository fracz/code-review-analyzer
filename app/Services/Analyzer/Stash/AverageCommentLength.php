<?php

namespace App\Services\Analyzer\Stash;

use App\Project;
use App\Services\Analyzer\StringTitle;

class AverageCommentLength extends AbstractAnalyzer
{
	use StringTitle;

	public function __toString()
	{
		return 'Średnia długość komentarza';
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

					foreach ($comments->values as $commentSource) {
						$comment = $commentSource->comment;
						$commentUser = $comment->author;
						$commentUserName = isset($commentUser->displayName) ? $commentUser->displayName : $commentUser->name;

						if (!isset($results[$commentUser->name])) {
							$results[$commentUser->name] = [
								'username' => $commentUser->name,
								'name' => $commentUserName,
								'avatar' => preg_match('@^http(s)?://.*@', $commentUser->avatarUrl) != 1 ? $project->getAttribute('url').$commentUser->avatarUrl : $commentUser->avatarUrl,
								'average' => 0,
								'count' => 0,
								'value' => 0,
							];
						}

						$results[$commentUser->name]['count'] += 1;
						$results[$commentUser->name]['value'] += strlen($comment->text);
					}

					if ($isNotLastCommentsPage) {
						$commentsPageStart = $comments->nextPageStart;
					}
				} while ($isNotLastCommentsPage);
			}

			if ($isNotLastPage) {
				$pageStart = $result->nextPageStart;
			}
		} while ($isNotLastPage && isset($pullRequest) && $pullRequest->updatedDate >= $from);

		$results = array_filter($results, function($item){
			return $item['count'] > 0;
		});

		foreach ($results as &$result) {
			$result['average'] = $result['value']/$result['count'];
			unset($result['value']);
		}

		usort($results, function($a, $b){
			$isMore = $b['average'] > $a['average'];
			$isEqual = $b['average'] == $a['average'];
			return $isMore ? 1 : ($isEqual ? 0 : -1);
		});

		return $results;
	}

	public function getResults($results, Project $project)
	{
		return view('review._list', ['results' => $results, 'analyzer' => $this, 'project' => $project]);
	}

	public function getContent($result, Project $project)
	{
		return view('review.stash.statistics._average_comment_length', ['result' => $result]);
	}
}
