<?php

namespace App\Services\Analyzer\Stash;

use App\Project;
use App\Services\Analyzer\AnalyzerInterface;
use App\Services\DataFetching\StashDataFetchingTrait;

abstract class AbstractAnalyzer implements AnalyzerInterface
{
	use StashDataFetchingTrait;

	public abstract function getLabel($results);

	public abstract function analyze(Project $project, $from, $to);

	public abstract function getContent($results, Project $project);

	public abstract function getResults($result, Project $project);
}
