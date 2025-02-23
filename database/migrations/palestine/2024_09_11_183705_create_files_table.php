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
      Schema::create('files', function (Blueprint $table) {
        $table->id();
        $table->foreignId('article_id')->constrained('articles')->onDelete('cascade');
        $table->string('file_name');
        $table->string('file_path');
        $table->bigInteger('file_size');
        $table->string('file_type');
        $table->string('mime_type');
        $table->string('file_category');
        $table->unsignedInteger('download_count')->default(0);
        $table->unsignedInteger('views_count')->default(0);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
