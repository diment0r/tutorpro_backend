<?php

namespace Database\Seeders;

use App\Models\Paraphrase;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParaphraseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::inRandomOrder()->get();

        $subjects = [
            config('gpt.subjects.literature'),
            config('gpt.subjects.biology'),
            config('gpt.subjects.geography'),
            config('gpt.subjects.history'),
        ];

        for ($i = 0; $i < 10; $i++) {
            Paraphrase::factory()->create([
                'subject' => $subjects[rand(0, 3)],
            ]);
        }

        // foreach ($users as $user) {
        //     $paraphraseCount = rand(1, 3);

        //     Paraphrase::factory()->count($paraphraseCount)->create([
        //         'subject' => $subjects[rand(0, 3)],
        //         'user_id' => $user->id,
        //     ]);
        // }
    }
}
