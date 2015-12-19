<?php

namespace App\Services;

use App\Project;

interface CodeFetcherInterface
{
	/**
	 * @return string Type name of the fetcher.
	 */
	public function getType();
	/**
	 * @param Project $project Project to get code from.
	 * @param string $change Change number
	 * @param string $revision Revision number
	 * @param string $filename Name of the file to fetch.
	 * @return string The code.
	 */
	public function getCode($project, $change, $revision, $filename);
}
