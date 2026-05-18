<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('web_name');
            $table->string('web_header');
            $table->string('notice')->nullable();
        });

        DB::table('app_settings')->insert([
            ['web_name' => 'PSC-MusicWeb', 'web_header' => 'PSC Music', 'notice' => null]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
