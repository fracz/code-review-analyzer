<?php

namespace App\Services\Analyzer\Gerrit;

use App\Project;
use App\Services\Analyzer\StringTitle;

class NoTaskChanges extends AbstractAnalyzer
{
	use StringTitle;

	public function __toString()
	{
		return 'Liczba zmian NT';
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
					'changes' => [],
				];
			}

			if (strpos($commit->subject, '[NT]') !== false) {
				$results[$commit->owner->_account_id]['changes'][$commit->_number] = $commit->subject;
			}
		}

		$results = array_filter($results, function($item){
			return count($item['changes']) > 0;
		});

		foreach ($results as &$result) {
			$result['count'] = count($result['changes']);
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
		return view('review.gerrit.changes._nt_changes', ['result' => $result, 'project' => $project]);
	}
}
