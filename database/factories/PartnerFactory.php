<?php

namespace Database\Factories;

use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerFactory extends Factory
{
    protected $model = Partner::class;

    public function definition(): array
    {
        $username = strtolower($this->faker->unique()->userName());
        $email = strtolower($this->faker->unique()->safeEmail());

        return [
            'name' => $this->faker->name(),
            'username' => $username,
            'normalized_username' => $username,
            'email' => $email,
            'normalized_email' => $email,
            'phone_country_code' => 'CI',
            'phone_e164' => '+225'.$this->faker->unique()->numerify('0#########'),
            'country_code' => 'CI',
            'password' => 'Password!123456',
            'status' => 'pending_email',
        ];
    }
}
