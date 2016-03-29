<?php

namespace App\Services\Analyzer\Gerrit\Badges;

abstract class AbstractBadge
{
	public $icon;
	public $awesomeFont;
	public $description;
	public $times;

	public function __construct($icon, $awesomeFont, $description)
	{
		$this->icon = $icon;
		$this->awesomeFont = $awesomeFont;
		$this->description = $description;
		$this->times = 0;

	}

	public abstract function checkBadge($data, $email);

}
