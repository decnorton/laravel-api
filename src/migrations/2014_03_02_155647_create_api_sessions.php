<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiSessions extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('api_clients', function ($table)
		{
			$table->increments('id');
			$table->string('name');
			$table->timestamps();
		});

		Schema::create('api_sessions', function ($table)
		{
			$table->increments('id');

			$table->integer('user_id')->unsigned();
			$table->integer('client_id')->unsigned();
			$table->string('public_key', 96);
			$table->string('private_key', 96);

			$table->timestamp('expires')->nullable();
			$table->timestamp('last_used')->nullable();

			$table->timestamps();

			$table->foreign('user_id')->references('id')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('api_sessions', function ($table)
		{
			$table->dropForeign('api_sessions_user_id_foreign');
		});

		Schema::drop('api_sessions');
	}

}
