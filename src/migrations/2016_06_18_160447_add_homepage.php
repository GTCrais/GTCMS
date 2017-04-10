<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHomepage extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('pages')->insert([
			'depth' => '0',
			'position' => '1',
			'name' => 'Homepage',
			'title' => 'Homepage',
			'model_key' => 'standard',
			'created_at' => \Carbon\Carbon::now(),
			'updated_at' => \Carbon\Carbon::now()
		]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('pages')->where('depth', '0')->where('position', '1')->delete();
	}
}
