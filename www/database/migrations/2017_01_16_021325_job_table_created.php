<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class JobTableCreated extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('file_datetime')->nullable()->default(null);
            $table->dateTime('datetime_updated')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('datetime_imported')->nullable();
            $table->string('file_browser_id', 1000)->nullable();
            $table->string('file_project', 100)->nullable();
            $table->string('file_url', 2100)->nullable();
            $table->string('file_id', 10000)->nullable();
            $table->mediumText('file_content_static')->nullable();
            $table->text('file_content_dynamic')->nullable();
            $table->mediumText('content_static_without_tags')->nullable();
            $table->text('content_dynamic_without_tags')->nullable();
            $table->tinyInteger('is_published')->default(0);

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
        Schema::dropIfExists('job');
    }
}
