<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_type_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('db_type')->unique();
            $table->string('named_type');
        });

        // Insert default mappings
        DB::table('user_type_mappings')->insert([
            ['db_type' => 'user', 'named_type' => 'Student'],
            ['db_type' => 'admin', 'named_type' => 'Admin'],
            ['db_type' => 'teacher', 'named_type' => 'Teacher'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_type_mappings');
    }
};
