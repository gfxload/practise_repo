<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogTable extends Migration
{
    public function up()
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject');
            $table->nullableMorphs('causer');
            $table->uuid('batch_uuid')->nullable();
            $table->string('event')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index('log_name');
            $table->index('batch_uuid');
            $table->index('event');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_log');
    }
}
