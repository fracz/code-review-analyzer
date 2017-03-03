<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommitsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('commits', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('commit_id')->unique();
			$table->string('project');
                        $table->string('branch');
			$table->string('change_id');
			$table->string('subject');
			$table->string('status');
			$table->dateTime('created');
			$table->dateTime('updated');
			$table->string('submittable');
			$table->string('insertions');
			$table->string('deletions');
			$table->string('_number');
                        $table->integer('owner_id')->unsigned()->default(0);
                        $table->foreign('owner_id')->references('id')->on('persons')->onDelete('cascade');
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
		Schema::drop('commits');
	}

}
