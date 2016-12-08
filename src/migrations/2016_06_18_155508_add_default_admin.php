<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultAdmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('users')->insert(array(
			'email' => 'admin@site.com',
			'password' => \Hash::make('admin'),
			'first_name' => 'Admin',
			'last_name' => 'Admin',
			'is_superadmin' => 1,
			'role' => 'admin',
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s')
		));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		DB::table('users')->where('email', 'admin@site.com')->delete();
    }
}
