<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User ;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Candidate>
 */
class CandidateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id'=>User::factory()->create([
                'role'=>'candidate'
            ]),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'password' => fake()->password(),
            // 'state'=> ,
            // 'documentState'
        ];
    }
}
