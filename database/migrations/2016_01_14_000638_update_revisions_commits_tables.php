<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRevisionsCommitsTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('comments', function(Blueprint $table)
            {
                $table->integer('revision_id')->unsigned()->default(0);
                $table->foreign('revision_id')->references('id')->on('revisions')->onDelete('cascade');
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            Schema::table('comments', function(Blueprint $table)
            {
                $table->dropForeign('comments_revision_id_foreign');
                $table->dropColumn(['revision_id']);
            });
        }

}