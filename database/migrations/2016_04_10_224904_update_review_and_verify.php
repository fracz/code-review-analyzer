<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateReviewAndVerify extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('codereviews', function(Blueprint $table)
			{
				$table->string('_revision_number');
			});
			
		Schema::table('verified', function(Blueprint $table)
			{
				$table->string('_revision_number');
			});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('codereviews', function(Blueprint $table)
            {
                $table->dropColumn(['_revision_number']);
            });
			
		Schema::table('verified', function(Blueprint $table)
            {
                $table->dropColumn(['_revision_number']);
            });
	}

}
