<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Project;
use App\Person;
use App\Services\DataFetching\GerritDataFetchingTrait;
use Cache;

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
				Cache::put('emails-to-update', [], 120);
				Cache::put('emails-to-update-from-badges', [], 120);
				
				
                $projects = Project::all();

                foreach ($projects as $project){
                    $this->collectDataForReview($project, null, date('Y-m-d',strtotime(date("Y-m-d") . "+1 days")));
                }
				
				print_r("data stored...");
				echo "<br/><br/>Emails to update after collecting data: ";
				print_r(Cache::get('emails-to-update'));
				
				$emails = Cache::get('emails-to-update');
				foreach($emails as $email){
					echo "<br/><br/> getting data for " . $email . "<br/>";
					
					$ch = curl_init(); 
					curl_setopt($ch, CURLOPT_URL, "http://apps.iisg.agh.edu.pl:10005/review/api/badges/user/nocache/".$email);
					
					curl_exec($ch);
					curl_close($ch);  
				}
				
				
				echo "<br/><br/>Emails to update from badge changes: ";
				print_r(Cache::get('emails-to-update-from-badges'));
				
				$emails = Cache::get('emails-to-update-from-badges');
				foreach($emails as $email){
					echo "<br/><br/> getting data for " . $email . "<br/>";
					
					$ch = curl_init(); 
					curl_setopt($ch, CURLOPT_URL, "http://apps.iisg.agh.edu.pl:10005/review/api/badges/user/nocache/".$email);
					
					curl_exec($ch);
					curl_close($ch);  
				}

            })->everyFiveMinutes();
    }

    protected function decode($result) {
        return json_decode(substr($result, 4));
    }

}
