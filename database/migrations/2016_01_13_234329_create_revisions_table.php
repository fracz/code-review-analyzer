<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRevisionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('revisions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('revision_id')->unique();
			$table->integer('_number');
                        $table->dateTime('created');
                        $table->string('ref');
                        
                        $table->integer('commit_id')->unsigned()->default(0);
                        $table->foreign('commit_id')->references('id')->on('commits')->onDelete('cascade');
                        $table->integer('uploader_id')->unsigned()->default(0);
                        $table->foreign('uploader_id')->references('id')->on('persons')->onDelete('cascade');
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
		Schema::drop('revisions');
	}

}
