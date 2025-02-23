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
        Schema::table('users', function (Blueprint $table) {
            // Add missing columns that are in the fillable array
            $table->string('phone')->nullable();
            $table->string('job_title')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('country')->nullable();
            $table->text('bio')->nullable();
            $table->json('social_links')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->boolean('is_online')->default(false);
            $table->string('google_id')->nullable();
            $table->boolean('is_admin')->default(false);
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['online', 'offline', 'away'])->default('offline');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }
            $table->dropColumn([
                'phone',
                'job_title',
                'gender',
                'country',
                'bio',
                'social_links',
                'last_seen',
                'is_online',
                'google_id',
                'is_admin'
            ]);
        });
    }
};
