<?php

namespace App\Services\Analyzer\Gerrit;

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
		return json_decode(substr($result, 4));
	}

	public function analyze(Project $project, $from, $to)
	{
		$uri = '/a/changes/?q=project:'.$project->getAttribute('name');
		$uri .= ' -is:draft ((status:merged)OR(status:open))';
		$uri .= ' after:'.$from.' before:'.$to;
		$uri .= '&o=ALL_REVISIONS&o=DETAILED_ACCOUNTS&o=LABELS';

		$result = $this->fetch($project, $uri);
		$results = [];

		foreach ($result as $commit) {
			foreach ($commit->revisions as $revision => $data) {
				$uri = '/a/changes/'.$commit->id.'/revisions/'.$revision.'/comments/';
				$commentList = $this->fetch($project, $uri);

				if (empty($commentList)) {
					$codeReview = $commit->labels->{'Code-Review'};
					foreach ($codeReview as $reviewer) {
						if ($reviewer instanceof \stdClass) {
							if (!isset($results[$reviewer->_account_id])) {
								$results[$reviewer->_account_id] = [
									'username' => $reviewer->username,
									'name' => $reviewer->name,
									'avatar' => current($reviewer->avatars),
									'commits' => [],
								];
							}

							$results[$reviewer->_account_id]['commits'][$commit->_number] = $commit->subject;
						}
					}
				}

				foreach ($commentList as $file => $comments) {
					foreach ($comments as $comment) {
						if (!isset($results[$comment->author->_account_id])) {
							$results[$comment->author->_account_id] = [
								'username' => $comment->author->username,
								'name' => $comment->author->name,
								'avatar' => current($comment->author->avatars),
								'commits' => [],
							];
						}

						$results[$comment->author->_account_id]['commits'][$commit->_number] = $commit->subject;
					}
				}
			}
		}

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
		return view('review.gerrit.changes._reviews_per_user', ['result' => $result, 'project' => $project]);
	}
}
