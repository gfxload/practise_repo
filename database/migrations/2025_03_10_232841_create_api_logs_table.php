<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method', 20)->nullable()->index(); // method1 or method2
            $table->string('api_type', 20)->nullable()->index(); // order or download
            $table->string('url')->nullable();
            $table->string('order_id')->nullable()->index();
            $table->string('status', 20)->nullable()->index(); // success or failed
            $table->integer('http_status')->nullable();
            $table->text('request_data')->nullable();
            $table->text('response_data')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('download_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('download_id')->references('id')->on('downloads')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};

