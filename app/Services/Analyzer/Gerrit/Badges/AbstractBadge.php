<?php

namespace App\Services\Analyzer\Gerrit\Badges;

abstract class AbstractBadge
{
	public $icon;
	public $awesomeFont;
	public $name;
	public $description;
	public $times;

	public function __construct($name, $description, $icon, $awesomeFont)
	{
		$this->name = $name;
		$this->description = $description;
		$this->icon = $icon;
		$this->awesomeFont = $awesomeFont;
		$this->times = 0;

	}

	public abstract function checkBadge($data, $email);

}
