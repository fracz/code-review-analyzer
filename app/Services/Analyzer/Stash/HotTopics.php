<?php

namespace App\Services\Analyzer\Stash;

use App\Project;
use App\Services\Analyzer\StringTitle;

class HotTopics extends AbstractAnalyzer
{
	use StringTitle;

	public function __toString()
	{
		return 'Hot topics';
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

				if (!isset($results[$pullRequest->id])) {
					$name = isset($user->displayName) ? $user->displayName : $user->name;
					$results[$pullRequest->id] = [
						'id' => $pullRequest->id,
						'subject' => $pullRequest->title,
						'username' => $user->name,
						'name' => $name,
						'avatar' => preg_match('@^http(s)?://.*@', $user->avatarUrl) != 1 ? $project->getAttribute('url').$user->avatarUrl : $user->avatarUrl,
						'messages' => [],
						'count' => 0,
					];
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
						$results[$pullRequest->id]['messages'][$comment->id] = $this->addComment($comment);
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

		foreach ($results as &$result) {
			$result['count'] = count($result['messages']);
		}

		$results = array_filter($results, function($item){
			return $item['count'] > 0;
		});

		if (empty($results)) {
			return [];
		}

		$average = array_sum(array_map(function($item){ return $item['count']; }, $results))/count($results);

		$results = array_filter($results, function($item) use ($average) {
			return $item['count'] > $average;
		});

		usort($results, function($a, $b){
			return $b['count'] - $a['count'];
		});

		return $results;
	}

	private function addComment($comment)
	{
		$user = $comment->author;
		$userName = isset($user->displayName) ? $user->displayName : $user->name;

		$result = [
			'from' => [
				'name' => $userName,
				'username' => $user->name,
			],
			'date' => (new \DateTime())->setTimestamp($comment->updatedDate),
			'text' => $comment->text,
			'replies' => [],
		];

		foreach ($comment->comments as $reply) {
			$result['replies'][$reply->id] = $this->addComment($reply);
		}

		return $result;
	}

	public function getResults($results, Project $project)
	{
		return view('review._list', ['results' => $results, 'analyzer' => $this, 'project' => $project]);
	}

	public function getContent($result, Project $project)
	{
		return view('review.stash.hot_topics._results', ['result' => $result, 'project' => $project]);
	}
}
