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
            $table->uuid('booking_uuid')->primary();
            $table->string('booking_name');
            $table->uuid('room_id');
            $table->timestamp('booking_time');
            $table->uuid('user_id');
            $table->dateTime('booked_from')->nullable();
            $table->dateTime('booked_to')->nullable();
            $table->json('attendees')->nullable();
            $table->uuid('approval_person_id')->nullable();
            $table->dateTime('approval_time')->nullable();
            $table->string('approval_comment')->nullable();
            $table->string(('checking_status'))->default('not_checked');
            $table->uuid('checking_person_id')->nullable();
            $table->dateTime('checking_time')->nullable();
            $table->dateTime('checkout_time')->nullable();
            $table->uuid('checkout_person_id')->nullable();
            $table->string('booking_status')->default('waiting_approval');
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->uuid('room_uuid')->primary();
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
