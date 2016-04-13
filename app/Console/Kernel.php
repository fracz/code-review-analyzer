<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Project;
use App\Person;
use App\Services\DataFetching\GerritDataFetchingTrait;

class Kernel extends ConsoleKernel {

    use GerritDataFetchingTrait;

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
            'App\Console\Commands\Inspire',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
            $schedule->command('inspire')
                             ->hourly();

            $schedule->call(function () {
                $projects = Project::all();

                foreach ($projects as $project){
                    $this->collectDataForReview($project, null, date('Y-m-d',strtotime(date("Y-m-d") . "+1 days")));
                }
				
				print_r("data stored...");
				
				$persons = Person::all();
				foreach($persons as $person){
					echo "<br/><br/> getting data for " . $person->email;
					
					$ch = curl_init(); 
					curl_setopt($ch, CURLOPT_URL, "http://apps.iisg.agh.edu.pl:10005/review/api/badges/user/nocache/".$person->email);
					
					curl_exec($ch);
					curl_close($ch);  
				}

            })->everyFiveMinutes();
    }

    protected function decode($result) {
        return json_decode(substr($result, 4));
    }

}
