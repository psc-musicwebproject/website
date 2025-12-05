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
        Schema::create('club_members', function (Blueprint $table) {
            $table->id();
            $table->uuid('member_id')->unique();
            $table->string('user_id');
            $table->string('status')->default('waiting');
            $table->string('ability');
            $table->string('approval_person_id')->nullable();
            $table->dateTime('approval_time')->nullable();
            $table->string('approval_comment')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_members');
    }
};
