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
        Schema::table('club_members', function (Blueprint $table) {
            $table->longText('contact_info')->nullable();
            $table->longText('instrument')->nullable();
            $table->longText('experience')->nullable();
            $table->string('wanted_duty')->nullable();
            $table->longText('image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('club_members', function (Blueprint $table) {
            $table->dropColumn('contact_info');
            $table->dropColumn('instrument');
            $table->dropColumn('experience');
            $table->dropColumn('wanted_duty');
            $table->dropColumn('image');
        });
    }
};
