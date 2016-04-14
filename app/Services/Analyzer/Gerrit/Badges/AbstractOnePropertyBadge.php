<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.03.16
 * Time: 19:39
 */

namespace App\Services\Analyzer\Gerrit\Badges;

use Cache;

abstract class AbstractOnePropertyBadge extends AbstractBadge
{
    private $category;
    private $property;

    public function __construct($name, $description, $icon, $awesomeFont, $id, $category, $property)
    {
        parent::__construct($name, $description, $icon, $awesomeFont, $id);
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

        if(count($winners) > 0)
		{
			foreach($winners as $winner){
				if($winner["email"] === $email && $maxRanking > 0)
				{
					$this->times = 1;
				}
			}
		}
		
		if (Cache::has('badge-value-' .$this->id)){
			$prevMax = Cache::get('badge-value-' .$this->id);
			
			if($maxRanking > $prevMax){
				//echo "new ranking for badge: ".$this->id;
				if(!Cache::has('emails-to-update-from-badges')){
					Cache::put('emails-to-update-from-badges', [], 8);
				}
				
				$array = [];
				foreach(Cache::get('emails-to-update-from-badges') as $em){
					array_push($array, $em);
				}
				foreach(Cache::get('badge-' .$this->id) as $user){
					
					$inArray = false;
					foreach($array as $item){
						if($item == $user["email"]){
							$inArray = true;
						}	
					}
					
					if(!$inArray)
						array_push($array, $user["email"]);
				}
				
				Cache::put('emails-to-update-from-badges', $array, 8);
			}
		}
		
		Cache::put('badge-' .$this->id, $winners, 8);
		Cache::put('badge-value-' .$this->id, $maxRanking, 8);
    }
}