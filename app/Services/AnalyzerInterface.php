<?php
namespace App\Services;

interface AnalyzerInterface
{
	public function getList();

	public function getRankers();

	public function analyze($project, $from, $to);
}
