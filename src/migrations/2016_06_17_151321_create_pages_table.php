<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pages', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('page_id')->unsigned()->nullable();
			$table->foreign('page_id')->references('id')->on('pages')->onUpdate('cascade')->onDelete('cascade');
			$table->integer('depth')->index()->nullable();
			$table->integer('position')->index()->nullable();
			$table->string('name')->index()->nullable();
			$table->string('slug')->index()->nullable();
			$table->string('title')->index()->nullable();
			$table->text('lead')->nullable();
			$table->text('content')->nullable();
			$table->text('meta_keywords')->nullable();
			$table->text('meta_description')->nullable();
			$table->string('model_key')->index()->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('pages');
	}
}
