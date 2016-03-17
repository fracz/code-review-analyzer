<?php

namespace App\Services\Analyzer\Gerrit\Badges;

abstract class AbstractBadge
{
	public $icon;
	public $description;

	public function __construct($icon, $description)
	{
		$this->icon = $icon;
		$this->description = $description;
	}

	public abstract function getBadge($results, $email);

}
