<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatingExpressionHitsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expression_hits', function (Blueprint $table) {
            $table->increments('id');
            $table->string('expression')->index()->unique();
            $table->integer('hits')->default(0);
            $table->dateTime('last_hit')->nullable();
            $table->timestamps();
        });

        Schema::create('expression_hits_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable()->index();
            $table->string('expression')->index();
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
        Schema::dropIfExists('expression_hits');
        Schema::dropIfExists('expression_hits_history');
    }
}
