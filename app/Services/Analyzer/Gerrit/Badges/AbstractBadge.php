<?php

namespace App\Services\Analyzer\Gerrit\Badges;

abstract class AbstractBadge
{
	protected $writtenIcon;
	protected $description;

	public function __construct($writtenIcon, $description)
	{
		$this->writtenIcon = $writtenIcon;
		$this->description = $description;
	}

	public abstract function getBadge($results, $email);
}
