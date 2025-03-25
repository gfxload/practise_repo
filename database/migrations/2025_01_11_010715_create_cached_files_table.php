<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cached_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('file_id');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size');
            $table->string('path');
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();

            $table->unique(['service_id', 'file_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cached_files');
    }
};
