<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Sakura',
            'student_id' => '032997',
            'surname' => 'Chiyono O',
            'username' => 'u6832997',
            'type' => 'student',
            'class' => 'ปวส.1/5',
            'password' => '8888',
        ]);

        User::factory()->create([
            'name' => 'Belno',
            'student_id' => '032483',
            'surname' => 'Light',
            'username' => 'u6832483',
            'type' => 'student',
            'class' => 'ปวส.1/7 (ม.)',
            'password' => '7151',
        ]);

        User::factory()->create([
            'name' => 'Agnes',
            'student_id' => '032408',
            'surname' => 'Tachyon',
            'username' => 'u6832408',
            'type' => 'student',
            'class' => 'ปวส.1/6',
            'password' => '8153',
        ]);

        User::factory()->create([
            'name' => 'Tazuna',
            'student_id' => '992789',
            'surname' => 'Hayakawa',
            'username' => '992789',
            'class' => null,
            'type' => 'teacher',
            'password' => '6789',
        ]);

        User::factory()->create([
            'name' => 'Yayoi',
            'student_id' => '992999',
            'surname' => 'Akikawa',
            'username' => '992999',
            'class' => null,
            'type' => 'admin',
            'password' => '7777',
        ]);

        User::factory()->create([
            'name' => 'ประณต',
            'student_id' => '996209',
            'surname' => 'สว่างพิศาลกิจ',
            'username' => '996209',
            'class' => null,
            'type' => 'admin',
            'password' => 'Tong1234',
        ]);

    }
}
