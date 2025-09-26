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
        Schema::create('spotify_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unique(); 
            $table->string('gender');
            $table->integer('age');
            $table->string('country');
            $table->string('subscription_type');
            $table->integer('listening_time');
            $table->integer('songs_played_per_day');
            $table->float('skip_rate'); 
            $table->string('device_type');
            $table->integer('ads_listened_per_week');
            $table->boolean('offline_listening');
            $table->boolean('is_churned');
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spotify_users');
    }
};