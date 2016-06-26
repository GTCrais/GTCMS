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
			$table->dropColumn(['name', 'created_at', 'updated_at']);
		});

		Schema::table('users', function ($table) {
			$table->string('role')->after('password');
			$table->string('first_name')->nullable()->after('role');
			$table->string('last_name')->nullable()->after('first_name');
			$table->boolean('is_superadmin')->default('0')->after('last_name');
			$table->dateTime('created_at');
			$table->dateTime('updated_at');
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
			$table->dropColumn(['role', 'first_name', 'last_name', 'is_superadmin', 'created_at', 'updated_at']);
		});

		Schema::table('users', function ($table) {
			$table->string('name');
			$table->timestamps();
		});
    }
}
