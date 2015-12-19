<?php

namespace App\Services\Analyzer\Gerrit;

use App\Project;
use App\Services\Analyzer\StringTitle;

class ChangesPerReview extends AbstractAnalyzer
{
	use StringTitle;

	public function __toString()
	{
		return 'Średnia liczba komentarzy na zmianę';
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
			if (!isset($results[$commit->owner->_account_id])) {
				$results[$commit->owner->_account_id] = [
					'username' => $commit->owner->username,
					'name' => $commit->owner->name,
					'avatar' => current($commit->owner->avatars),
					'messages' => 0,
					'average' => 0,
					'commits' => 0,
				];
			}

			$results[$commit->owner->_account_id]['commits'] += 1;

			foreach ($commit->revisions as $revision => $data) {
				$uri = '/a/changes/'.$commit->id.'/revisions/'.$revision.'/comments/';
				$comments = (array)$this->fetch($project, $uri);

				foreach ($comments as $comment) {
//print_r($comments);exit;
					$results[$commit->owner->_account_id]['messages'] += count($comment);
				}
			}
		}

		$results = array_filter($results, function($item){
			return $item['messages'] > 0;
		});

		foreach ($results as &$result) {
			$result['average'] = $result['messages']/$result['commits'];
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
		return view('review.gerrit.statistics._changes_per_review', ['result' => $result]);
	}
}
