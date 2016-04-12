<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCodeReview extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('codereviews', function(Blueprint $table)
			{
				$table->string('review_date');
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
                $table->dropColumn(['review_date']);
            });
	}

}
