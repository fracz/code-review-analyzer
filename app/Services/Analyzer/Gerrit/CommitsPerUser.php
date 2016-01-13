<?php

namespace App\Services\Analyzer\Gerrit;

use App\Project;
use App\Services\Analyzer\CommitCountTitle;

class CommitsPerUser extends AbstractAnalyzer
{
	public function getLabel($results)
	{
		return sprintf('%s (%d)', (string)$this, array_sum(array_map(function($item){
			return count($item['commits']);
		}, $results)));
	}

	public function __toString()
	{
		return 'Liczba zmian';
	}

	protected function decode($result)
	{
		return json_decode(substr($result, 4));
	}

	public function analyze(Project $project, $from, $to)
	{
            $this->collectDataForReview($project, $from, $to);
            
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
					'commits' => [],
				];
			}

			$results[$commit->owner->_account_id]['commits'][$commit->_number] = $commit->subject;
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
//print_r($results);exit;
		return $results;
	}

	public function getResults($results, Project $project)
	{
		return view('review._list', ['results' => $results, 'analyzer' => $this, 'project' => $project]);
	}

	public function getContent($result, Project $project)
	{
		return view('review.gerrit.changes._commits_per_user', ['result' => $result, 'project' => $project]);
	}
}
