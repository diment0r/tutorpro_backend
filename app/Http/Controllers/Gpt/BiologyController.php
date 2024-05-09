<?php

namespace App\Http\Controllers\Gpt;

use App\Http\Controllers\Controller;
use App\Http\Requests\Paraphrase\BiologyParaphraseRequest;
use App\Models\Paraphrase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BiologyController extends Controller
{
    public function topicParaphrase(BiologyParaphraseRequest $request)
    {
        $request->validated();
        $paraphrase = Paraphrase::with('user')->where('topic', $request->topic)->first();

        if ($paraphrase) {
            return response()->json([
                'success' => true,
                'data' => $paraphrase,
            ]);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('gpt.openai.api_key'),
            'Content-Type' => 'application/json',
        ])->post(config('gpt.openai.api_url'), [
            // 'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Розкажи основну теоретичну інформацію про "' . $request->topic . '" з предмету ' . config('gpt.subjects.biology'),
                ]
            ],

            'model' => config('gpt.openai.model'),
            'temperature' => (float)config('gpt.openai.temperature'),
            'max_tokens' => config('gpt.openai.max_tokens'),
            'top_p' => (float)config('gpt.openai.top_p'),
            'frequency_penalty' => (float)config('gpt.openai.frequency_penalty'),
            'presence_penalty' => (float)config('gpt.openai.presence_penalty'),
            'stop' => [config('gpt.openai.stop')],
        ])->json();

        if (!array_key_exists('choices', $response)) {
            return response()->json([
                'success' => false,
                'data' => [
                    'error' => $response['error'],
                ],
            ]);
        }

        $createdParaphraseId = Paraphrase::create([
            'subject' => config('gpt.subjects.biology'),
            'topic' => $request->topic,
            'paraphrase' => $response['choices'][0]['message']['content'],
            'user_id' => auth('sanctum')->user()->id,
        ])->id;

        $paraphrase = Paraphrase::with('user')->where('id', $createdParaphraseId)->first();

        return response()->json([
            'success' => true,
            'data' => $paraphrase,
        ]);
    }
}
