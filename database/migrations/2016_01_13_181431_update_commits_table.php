<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCommitsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('commits', function(Blueprint $table)
            {
                $table->dropColumn(['created']);
                $table->dropColumn(['updated']);
            });
            
            Schema::table('comments', function(Blueprint $table)
            {
                $table->dropColumn(['updated']);
            });
            
            Schema::table('commits', function(Blueprint $table)
            {
                $table->dateTime('created');
                $table->dateTime('updated');
            });
            
            Schema::table('comments', function(Blueprint $table)
            {
                $table->dateTime('updated');
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            Schema::table('commits', function(Blueprint $table)
            {
                $table->dropColumn(['created']);
                $table->dropColumn(['updated']);
            });
            
            Schema::table('comments', function(Blueprint $table)
            {
                $table->dropColumn(['updated']);
            });
            
            Schema::table('commits', function(Blueprint $table)
            {
                $table->date('created');
                $table->date('updated');
            });
            
            Schema::table('comments', function(Blueprint $table)
            {
                $table->date('updated');
            });
	}

}
