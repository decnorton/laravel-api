<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Creates the users table
		Schema::create('users', function ($table) {
		    $table->increments('id')->unsigned();

		    $table->string('username')->unique();
		    $table->string('email')->unique();
		    $table->string('name')->nullable();
		    $table->string('password');

		    $table->timestamps();
		    $table->softDeletes();
		});

		Schema::create('password_reminders', function(Blueprint $table)
		{
			$table->string('email')->index();
			$table->string('token')->index();
			$table->timestamps();
		});

		Schema::create('roles', function ($table) {
		    $table->increments('id')->unsigned();
		    $table->string('name')->unique();
		    $table->string('display_name')->unique();
		    $table->string('description')->nullable();

		    $table->timestamps();
		    $table->softDeletes();
		});

		// Creates the permissions table
		Schema::create('permissions', function ($table) {
		    $table->increments('id')->unsigned();

		    $table->string('name')->unique();
		    $table->string('display_name')->unique();
		    $table->string('description')->nullable();

		    $table->timestamps();
		});

		// Creates the user_roles (Many-to-Many relation) table
		Schema::create('user_roles', function ($table) {
		    $table->increments('id')->unsigned();
		    $table->integer('user_id')->unsigned();
		    $table->integer('role_id')->unsigned();

		    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		    $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

		    $table->unique(['user_id', 'role_id']);
		});

		// Creates the role_permissions (Many-to-Many relation) table
		Schema::create('role_permissions', function ($table) {
		    $table->increments('id')->unsigned();

		    $table->integer('role_id')->unsigned();
		    $table->integer('permission_id')->unsigned();

		    $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
		    $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

		    $table->unique(['role_id', 'permission_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Drop foreign keys
		Schema::table('user_roles', function(Blueprint $table) {
		    $table->dropForeign('user_roles_user_id_foreign');
		    $table->dropForeign('user_roles_role_id_foreign');
		});

		Schema::table('role_permissions', function(Blueprint $table) {
		    $table->dropForeign('role_permissions_permission_id_foreign');
		    $table->dropForeign('role_permissions_role_id_foreign');
		});

		// Drop tables
		Schema::drop('users');
		Schema::drop('password_reminders');
		Schema::drop('user_roles');
		Schema::drop('role_permissions');
		Schema::drop('roles');
		Schema::drop('permissions');
	}

}
