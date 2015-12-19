<?php

namespace App\Services\DataFetching;

use App\Project;

trait StashDataFetchingTrait
{
	private static $cache = [];

	protected function fetch(Project $project, $uri)
	{
		if (isset(self::$cache[$project->getAttribute('name')][$uri])) {
			return self::$cache[$project->getAttribute('name')][$uri];
		}

		$url = $project->getAttribute('url').'/rest/api/1.0/projects/'.$project->getAttribute('name').
			'/repos/'.$project->getAttribute('repository');

		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => $url.$uri,
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERPWD => $project->getAttribute('username').':'.$project->getAttribute('password'),
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => true,
		]);
		$result = curl_exec($ch);

		if ($result === false) {
			return [];
		}

		$result = $this->decode($result);
		self::$cache[$project->getAttribute('name')][$uri] = $result;

		return $result;
	}

	/**
	 * @param string $data Data to decode.
	 * @return mixed Decoded results.
	 */
	protected abstract function decode($data);
}
