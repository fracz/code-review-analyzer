<?php

namespace App\Services\Ranking\Gerrit;

use App\Project;
use App\Services\Ranking\RankerInterface;

class OverallRankingChart implements RankerInterface
{
	/**
	 * Creates ranking for all generated results.
	 *
	 * @param Project $project Project which needs ranking.
	 * @param array $results List of results to process.
	 * @return array
	 */
	public function createRanking(Project $project, array $results)
	{
		$result = [
			'x' => 'x',
			'columns' => [
				['x'],
				['user'],
			],
			'names' => [
				'user' => 'Użytkownik',
			],
			'type' => 'bar',
		];

		foreach ($results['ranking_overall'] as $username => $user) {
			$result['columns'][0][] = $user['name'];
			$result['columns'][1][] = $user['value'];
		}

		return $result;
	}

	public function getLabel()
	{
		return 'Ogólny - wykres';
	}

	public function getResults(array $results, Project $project)
	{
		return view('review.gerrit.ranking._overall_chart', ['results' => $results, 'project' => $project]);
	}
}
