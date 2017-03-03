<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		\Illuminate\Support\Facades\DB::table('users')->insert([
			'name' => 'Aleksander Wszystkomogący',
			'email' => 'admin@review-analyzer.local',
			'password' => password_hash('admin123', PASSWORD_DEFAULT),
		]);
	}
}
