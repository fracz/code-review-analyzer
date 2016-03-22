<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVerifiedTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('verified', function(Blueprint $table)
		{
			$table->increments('id');
                        $table->integer('verified_value');
                        $table->string('verified_date');
			$table->integer('commit_id')->unsigned()->default(0);
                        $table->foreign('commit_id')->references('id')->on('commits')->onDelete('cascade');
                        
			$table->integer('verifier_id')->unsigned()->default(0);
                        $table->foreign('verifier_id')->references('id')->on('persons')->onDelete('cascade');

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('verified');
	}

}
