<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trusted_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->text('reason')->nullable();
            $table->timestamp('added_at');
            $table->foreignId('added_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trusted_ips');
    }
};
