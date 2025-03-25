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
        Schema::table('services', function (Blueprint $table) {
            $table->string('url_pattern')->nullable()->after('image_path')->comment('Regex pattern to validate service URL');
            $table->string('file_id_pattern')->nullable()->after('url_pattern')->comment('Regex pattern to extract file ID from URL');
            $table->string('expected_url_format')->nullable()->after('file_id_pattern')->comment('Example of expected URL format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['url_pattern', 'file_id_pattern', 'expected_url_format']);
        });
    }
};
