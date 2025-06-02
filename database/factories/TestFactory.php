<?php

namespace Database\Factories;

use App\Models\Qcm;
use App\Models\Step;
use App\Models\Skill;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'objective' => $this->faker->sentence(),
            'prerequisites' => $this->faker->paragraph(),
            'tools_Required' => $this->faker->words(3, true),
            'before_answer' => $this->faker->sentence(),
            'qcm_id' => Qcm::factory(),    // Automatically create a QCM
            'company_id' => Company::factory(), // Automatically create a Company
            'skill_id' => Skill::inRandomOrder()->first()?->id
        ];
    }
}
