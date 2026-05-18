<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'user_id' => $this->faker->unique()->numerify('######'),
            'username' => $this->faker->unique(),
            'type' => $this->faker->randomElement(['student', 'teacher', 'admin']),
            'class' => $this->faker->randomElement(['ปวส.1/5','ปวส.1/6','ปวส.1/7 (ม.)']),
            'password' => static::$password ??= 'password', // Remove Hash::make()
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
