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
			$table->dropColumn(['name']);
		});

		Schema::table('users', function ($table) {
			$table->string('email')->nullable()->change();
			$table->string('password')->nullable()->change();
			$table->string('role')->nullable()->after('password');
			$table->string('first_name')->nullable()->after('role');
			$table->string('last_name')->nullable()->after('first_name');
			$table->boolean('is_superadmin')->nullable()->default('0')->after('last_name');
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
			$table->dropColumn(['role', 'first_name', 'last_name', 'is_superadmin']);
		});

		Schema::table('users', function ($table) {
			$table->string('name');
		});
	}
}
