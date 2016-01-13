<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('comments', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('comment_id')->unique();
			$table->integer('line');
                        $table->integer('start_line');
                        $table->integer('start_character');
                        $table->integer('end_line');
                        $table->integer('end_character');
                        $table->date('updated');
                        $table->string('message');
                        
                        $table->integer('commit_id')->unsigned()->default(0);
                        $table->foreign('commit_id')->references('id')->on('commits')->onDelete('cascade');
                        $table->integer('author_id')->unsigned()->default(0);
                        $table->foreign('author_id')->references('id')->on('persons')->onDelete('cascade');
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
		Schema::drop('comments');
	}

}
