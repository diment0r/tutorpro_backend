<?php

namespace Database\Seeders;

use App\Models\Paraphrase;
use App\Models\Question;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paraphrases = Paraphrase::inRandomOrder()->get();

        foreach ($paraphrases as $paraphrase) {
            $questionCount = 3;

            Question::factory()->count($questionCount)->create([
                'paraphrase_id' => $paraphrase->id,
            ]);
        }
    }
}