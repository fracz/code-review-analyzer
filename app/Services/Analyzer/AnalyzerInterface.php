<?php
namespace App\Services\Analyzer;

use App\Project;

interface AnalyzerInterface
{
	public function getLabel($results);

	public function analyze(Project $project, $from, $to);

	public function getContent($results, Project $project);

	public function getResults($result, Project $project);
}
