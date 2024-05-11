<?php

namespace Database\Seeders;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = Question::inRandomOrder()->get();

        foreach ($questions as $question) {
            $optionsCount = 4;

            for ($i = 0; $i < $optionsCount; $i++) {
                Option::factory()->create([
                    'correct' => $i == 0,
                    'question_id' => $question->id,
                ]);
            }
        }
    }
}
