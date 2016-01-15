<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCodeReviewTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('codereviews', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('commit_id')->unsigned()->default(0);
                        $table->foreign('commit_id')->references('id')->on('commits')->onDelete('cascade');
                        
			$table->integer('reviewer_id')->unsigned()->default(0);
                        $table->foreign('reviewer_id')->references('id')->on('persons')->onDelete('cascade');

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
		Schema::drop('codereviews');
	}

}
