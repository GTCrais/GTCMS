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
		DB::table('pages')->insert(array(
			'depth' => '0',
			'position' => '1',
			'name' => 'Homepage',
			'title' => 'Homepage',
			'model_key' => 'standard'
		));
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
