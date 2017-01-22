<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');

            // @TODO: "->collate('utf8_unicode_ci')" should fix the error:
            // [PDOException] SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was too long; max key length is 767 bytes
            // MySQL 5.6.26 (current production) have problems, but 5.7.17 (dev box) seems to be ok...
            $table->string('username')->collate('utf8_unicode_ci')->unique();
            $table->string('email')->collate('utf8_unicode_ci')->unique();

            $table->string('password');
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
        Schema::dropIfExists('users');
    }
}
