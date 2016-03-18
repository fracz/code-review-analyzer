<?php

namespace App\Services\Analyzer\Gerrit\Badges;

abstract class AbstractBadge
{
	public $icon;
	public $description;
	public $times;

	public function __construct($icon, $description)
	{
		$this->icon = $icon;
		$this->description = $description;
		$this->times = 0;
	}

	public abstract function checkBadge($data, $email);

}
