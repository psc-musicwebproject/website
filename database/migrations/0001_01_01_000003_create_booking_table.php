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
        Schema::create('booking', function (Blueprint $table) {
            $table->id();
            $table->uuid('booking_id')->unique();
            $table->string('booking_name');
            $table->uuid(('room_id'));
            $table->timestamp('booking_time');
            $table->string('user_id');
            $table->dateTime('booked_from')->nullable();
            $table->dateTime('booked_to')->nullable();
            $table->json('attendees');
            $table->string('status')->default('waiting');
            $table->string('approval_person_id')->nullable();
            $table->dateTime('approval_time')->nullable();
            $table->string('approval_comment')->nullable();
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->uuid('room_id')->unique();
            $table->string('room_name');
            $table->string('room_status')->default('available');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking');
        Schema::dropIfExists('rooms');
    }
};
