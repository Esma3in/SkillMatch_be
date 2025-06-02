<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Candidate ;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile_candidate>
 */
class ProfileCandidateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'field' =>fake()->firstName(),
            'last_name' =>fake()->lastName(),
            'phoneNumber'=>fake()->phoneNumber(),
            'file'=>fake()->optional()->word()."pdf",
            'experience' => json_encode([
                [
                    'title' => fake()->jobTitle,
                    'company' => fake()->company,
                    'duration' => fake()->randomElement(['1 year', '2 years', '6 months']),
                    'description' => fake()->sentence,
                ],
                [
                    'title' => fake()->jobTitle,
                    'company' => fake()->company,
                    'duration' => fake()->randomElement(['1 year', '3 years', '9 months']),
                    'description' => fake()->sentence,
                ],
            ]),
            'formation' => json_encode([
                [
                    'degree' => fake()->randomElement(['Bachelor', 'Master', 'PhD']),
                    'field' => fake()->randomElement(['Computer Science', 'Engineering', 'Business']),
                    'institution' => fake()->company,
                    'year' => fake()->year,
                ],
            ]),
            'photoProfil' => fake()->imageUrl(200, 200, 'people'),
            'localisation' => json_encode([
                'city' => fake()->city,
                'country' => fake()->country,
            ]),
            'competenceList' => json_encode(fake()->words(5)),
            'candidate_id' => Candidate::factory(),
        ];
    }
}
