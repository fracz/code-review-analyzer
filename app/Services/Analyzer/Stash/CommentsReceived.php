<?php

namespace App\Services\Analyzer\Stash;

use App\Project;

class CommentsReceived extends AbstractAnalyzer
{
	public function getLabel($results)
	{
		return sprintf('%s (%d)', (string)$this, array_sum(array_map(function($item){
			return $item['count'];
		}, $results)));
	}

	public function __toString()
	{
		return 'Liczba otrzymanych komentarzy';
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
				$name = isset($user->displayName) ? $user->displayName : $user->name;

				if (!isset($results[$user->name])) {
					$results[$user->name] = [
						'username' => $user->name,
						'name' => $name,
						'avatar' => preg_match('@^http(s)?://.*@', $user->avatarUrl) != 1 ? $project->getAttribute('url').$user->avatarUrl : $user->avatarUrl,
						'count' => 0,
						'commits' => [],
					];
				}

				$results[$user->name]['commits'][$pullRequest->id] = [
					'subject' => $pullRequest->title,
					'comments' => [],
				];

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

						$results[$user->name]['count'] += 1;
						$results[$user->name]['commits'][$pullRequest->id]['comments'][$comment->id] = [
							'from' => [
								'name' => $commentUserName,
								'username' => $commentUser->name,
							],
							'date' => (new \DateTime())->setTimestamp($comment->updatedDate),
							'text' => $comment->text,
						];
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
		return view('review.stash.comments._received', ['result' => $result, 'project' => $project]);
	}
}
