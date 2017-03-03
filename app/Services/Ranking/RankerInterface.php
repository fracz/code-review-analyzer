<?php

namespace App\Services\Ranking;

use App\Project;
use Illuminate\View\View;

interface RankerInterface
{
	/**
	 * @return string Ranker label.
	 */
	public function getLabel();

	/**
	 * Generates view to display results of the ranker.
	 *
	 * @param array $results List of results to use.
	 * @param Project $project The project.
	 * @return View
	 */
	public function getResults(array $results, Project $project);

	/**
	 * Creates ranking for all generated results.
	 *
	 * @param Project $project Project which needs ranking.
	 * @param array $results List of results to process.
	 * @return array
	 */
	public function createRanking(Project $project, array $results);
}
