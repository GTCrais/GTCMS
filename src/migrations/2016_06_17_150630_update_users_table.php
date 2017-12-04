<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function ($table) {
			$table->string('email')->nullable()->change();
			$table->string('password')->nullable()->change();
			$table->string('name')->nullable()->change();
			$table->string('role')->nullable()->after('password');
			$table->boolean('is_superadmin')->nullable()->default('0')->after('name');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function ($table) {
			$table->dropColumn(['role', 'is_superadmin']);
		});
	}
}
