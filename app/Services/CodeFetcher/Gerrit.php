<?php

namespace App\Services\CodeFetcher;

use App\Project;
use App\Services\CodeFetcherInterface;

class Gerrit implements CodeFetcherInterface
{
	use \App\Services\DataFetching\GerritDataFetchingTrait;

	/**
	 * @param Project $project Project to get code from.
	 * @param string $change Change number
	 * @param string $revision Revision number
	 * @param string $filename Name of the file to fetch.
	 * @return string The code.
	 */
	public function getCode($project, $change, $revision, $filename)
	{
		$uri = '/a/changes/'.$change.'/revisions/'.$revision.'/files/'.urlencode($filename).'/diff?intraline';
		$result = $this->fetch($project, $uri);
		$type = isset($result->meta_b) ? $result->meta_b->content_type : $result->meta_a->content_type;

		$code = [
			'lines' => 0,
			'type' => $this->getCodeType($type),
			'parts' => [],
		];

		foreach ($result->content as $part) {
			if (isset($part->ab)) {
				$code['parts'][] = [
					'type' => 'identical',
					'code' => array_map('htmlspecialchars', $part->ab)
				];
				$code['lines'] += count($part->ab);
			} else if (isset($part->b)) {
				$code['parts'][] = [
					'type' => 'added',
					'code' => array_map('htmlspecialchars', $part->b)
				];
				$code['lines'] += count($part->b);
			} else if (isset($part->a)) {
				$code['parts'][] = [
					'type' => 'removed',
					'code' => array_map('htmlspecialchars', $part->a)
				];
			}
		}

		return $code;
	}

	/**
	 * @param string $data Data to decode.
	 * @return mixed Decoded results.
	 */
	protected function decode($data)
	{
		return json_decode(substr($data, 4));
	}

	/**
	 * @return string Type name of the fetcher.
	 */
	public function getType()
	{
		return 'gerrit';
	}

	private function getCodeType($contentType)
	{
		switch($contentType) {
			case 'text/css':
				$type = 'css';
				break;
			case 'text/x-coffeescript':
				$type = 'coffeescript';
				break;
			case 'text/x-java-source':
				$type = 'java';
				break;
			case 'text/x-javascript':
				$type = 'javascript';
				break;
			case 'text/x-less':
				$type = 'less';
				break;
			case 'text/x-php':
				$type = 'php';
				break;
			case 'text/x-python':
				$type = 'python';
				break;
			case 'text/x-scss':
				$type = 'scss';
				break;
			case 'text/x-sql':
				$type = 'sql';
				break;
			case 'text/yaml':
				$type = 'yaml';
				break;
			case 'text/html':
				$type = 'markup';
				break;
			default:
				$type = $contentType;
//				$type = 'none';
		}

		return $type;
	}
}
