<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAvatarsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('avatars', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('url');
			$table->string('height');
                        $table->integer('person_id')->unsigned()->default(0);
                        $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');
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
		Schema::drop('avatars');
	}

}
