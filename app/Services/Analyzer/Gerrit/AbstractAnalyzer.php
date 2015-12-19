<?php

namespace App\Services\Analyzer\Gerrit;

use App\Project;
use App\Services\Analyzer\AnalyzerInterface;
use App\Services\DataFetching\GerritDataFetchingTrait;

abstract class AbstractAnalyzer implements AnalyzerInterface
{
	use GerritDataFetchingTrait;

	public abstract function getLabel($results);

	public abstract function analyze(Project $project, $from, $to);

	public abstract function getContent($results, Project $project);

	public abstract function getResults($result, Project $project);
}
