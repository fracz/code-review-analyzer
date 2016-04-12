<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 19:39
 */

namespace App\Services\Analyzer\Gerrit\Badges;


abstract class AbstractOnePropertyBadge extends AbstractBadge
{
    private $category;
    private $property;

    public function __construct($name, $description, $icon, $awesomeFont, $category, $property)
    {
        parent::__construct($name, $description, $icon, $awesomeFont);
        $this->category = $category;
        $this->property = $property;
    }

    public function checkBadge($data, $email)
    {
        $commitsPerUser = $data[$this->category];
        
        //if($this->category == "changes_per_review"){
			//print_r($commitsPerUser);exit;}
        
        $winners = [];
        $maxRanking = 0.0;

        foreach ($commitsPerUser as $key => $commit) {
            $ranking = $commit[$this->property];
                
            if ($ranking > $maxRanking) {
				$winners = [];
				array_push($winners, $commit);
                $maxRanking = $ranking;
            } else if ($ranking == $maxRanking)
			{
				array_push($winners, $commit);
			}

        }

        if(count($winners) > 0) //and $winner["email"] === $email
		{
			foreach($winners as $winner){
				if($winner["email"] === $email && $maxRanking > 0)
					$this->times = 1;
			}
		}
    }
}