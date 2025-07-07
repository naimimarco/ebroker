<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Services\HelperService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $profileAvailableNames = ['profile1.jpg', 'profile2.jpg', 'profile3.jpg', 'profile4.jpg', 'profile5.jpg'];
        $counter = rand(0, count($profileAvailableNames) - 1);
        $name = $this->faker->name;
        return [
            'name'              => $name,
            'email'             => $this->faker->email,
            'password'          => "demo@test",
            'auth_id'           => Str::uuid(),
            'mobile'            => $this->faker->phoneNumber,
            'profile'           => $profileAvailableNames[$counter],
            'logintype'         => '3',
            'is_email_verified' => '1',
            'isActive'          => '1',
            'slug_id'           => generateUniqueSlug($name, 5),
            'notification'      => '1',
            'about_me'          => $this->faker->text,
            'facebook_id'       => $this->faker->uuid,
            'twiiter_id'        => $this->faker->uuid,
            'instagram_id'      => $this->faker->uuid,
            'youtube_id'        => $this->faker->uuid,
            'latitude'          => $this->faker->latitude,
            'longitude'         => $this->faker->longitude,
            'city'              => $this->faker->city,
            'state'             => $this->faker->state,
            'country'           => $this->faker->country,
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
    }
}
