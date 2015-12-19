<?php

namespace App\Services\DataFetching;

use App\Project;

trait GerritDataFetchingTrait
{
	private static $cache = [];

	protected function fetch(Project $project, $uri)
	{
		if (isset(self::$cache[$project->getAttribute('name')][$uri])) {
			return self::$cache[$project->getAttribute('name')][$uri];
		}
		
		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => str_replace(' ', '%20', $project->getAttribute('url').$uri),
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

		if ($result === null) {
			return [];
		}

		self::$cache[$project->getAttribute('name')][$uri] = $result;

		return $result;
	}

	/**
	 * @param string $data Data to decode.
	 * @return mixed Decoded results.
	 */
	protected abstract function decode($data);
}
