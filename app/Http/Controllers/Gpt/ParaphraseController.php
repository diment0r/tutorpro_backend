<?php

namespace App\Http\Controllers\Gpt;

use App\Http\Controllers\Controller;
use App\Http\Requests\Paraphrase\DefaultParaphraseRequest;
use App\Http\Requests\Paraphrase\LiteratureParaphraseRequest;
use App\Models\Option;
use App\Models\Paraphrase;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ParaphraseController extends Controller
{
    private function gptRequest($isJsonResponseFormat, $requestContent)
    {
        $messages = [
            ['role' => 'system', 'content' => $isJsonResponseFormat ? 'Provide a valid JSON output' : ''],
            ['role' => 'user', 'content' => $requestContent],
        ];
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . config('gpt.openai.api_key'),
            'Content-Type' => 'application/json',
        ])->post(config('gpt.openai.api_url'), [
            'response_format' => $isJsonResponseFormat ? ['type' => 'json_object'] : null,
            'messages' => $messages,

            'model' => config('gpt.openai.model'),
            'temperature' => (float)config('gpt.openai.temperature'),
            'max_tokens' => config('gpt.openai.max_tokens'),
            'top_p' => (float)config('gpt.openai.top_p'),
            'frequency_penalty' => (float)config('gpt.openai.frequency_penalty'),
            'presence_penalty' => (float)config('gpt.openai.presence_penalty'),
            'stop' => [config('gpt.openai.stop')],
        ])->json();
    }

    private function storeParaphrase($subject, $topic, $paraphrase)
    {
        $createdParaphraseId = Paraphrase::create([
            'subject' => $subject,
            'topic' => $topic,
            'paraphrase' => $paraphrase,
            'user_id' => auth('sanctum')->user()->id,
        ])->id;
        return Paraphrase::with('user')->where('id', $createdParaphraseId)->first();
    }

    private function storeParaphraseTest($test, $paraphraseId)
    {
        foreach ($test->questions as $question) {
            $questionId = Question::create([
                'question' => $question->question,
                'paraphrase_id' => $paraphraseId,
            ])->id;

            for ($i = 0; $i < 4; $i++) {
                Option::create([
                    'option' => $question->options[$i],
                    'correct' => $i == $question->correct,
                    'question_id' => $questionId,
                ]);
            }
        }

        return Paraphrase::where('id', $paraphraseId)->with('questions.options')->first()->questions;
    }

    public function defaultTopicParaphrase(DefaultParaphraseRequest $request)
    {
        $request->validated();
        $subject = strtolower($request->subject);
        if (!($subject == config('gpt.subjects.biology') || $subject == config('gpt.subjects.geography') || $subject == config('gpt.subjects.history'))) {
            return response()->json([
                'success' => false,
                'data' => [
                    'error' => 'Subject name is invalid. Must be one of this [біологія, географія, історія]',
                ],
            ]);
        }

        $paraphrase = Paraphrase::with('user')->where('topic', $request->topic)->first();
        if ($paraphrase) {
            return response()->json([
                'success' => true,
                'data' => $paraphrase,
            ]);
        }

        $propmt = 'Розкажи основну теоретичну інформацію про "' . $request->topic . '" з предмету ' . $request->subject;
        $response = $this->gptRequest(false, $propmt);
        if (!array_key_exists('choices', $response)) {
            return response()->json([
                'success' => false,
                'data' => [
                    'error' => $response['error'],
                ],
            ]);
        }

        $paraphrase = $this->storeParaphrase($request->subject, $request->topic, $response['choices'][0]['message']['content']);
        return response()->json([
            'success' => true,
            'data' => $paraphrase,
        ]);
    }

    public function literatureTopicParaphrase(LiteratureParaphraseRequest $request)
    {
        $request->validated();
        if (!(strtolower($request->size) == 'short' || strtolower($request->size) == 'long')) {
            return response()->json([
                'success' => false,
                'data' => [
                    'error' => 'Size is invalid. Must be one of this [short, long]',
                ],
            ]);
        }

        $paraphrase = Paraphrase::with('user')->where('topic', $request->topic . ' [' . $request->size . ']')->first();
        if ($paraphrase) {
            return response()->json([
                'success' => true,
                'data' => $paraphrase,
            ]);
        }

        $paraphraseSizePrompt = strtolower($request->size) == 'short' ? config('gpt.paraphrase_size.short') : config('gpt.paraphrase_size.long');
        $propmt = 'Розкажи послідовно основні події літературного твору "' . $request->topic . '", дійові особи, тема, думка твору. ' . $paraphraseSizePrompt;
        $response = $this->gptRequest(false, $propmt);
        if (!array_key_exists('choices', $response)) {
            return response()->json([
                'success' => false,
                'data' => [
                    'error' => $response['error'],
                ],
            ]);
        }

        $paraphrase = $this->storeParaphrase(config('gpt.subjects.literature'), $request->topic . ' [' . $request->size . ']', $response['choices'][0]['message']['content']);
        return response()->json([
            'success' => true,
            'data' => $paraphrase,
        ]);
    }

    public function paraphraseTest($paraphraseId)
    {
        $paraphrase = Paraphrase::where('id', $paraphraseId)->with('questions.options')->first();
        if (!$paraphrase) {
            return response()->json([
                'success' => false,
                'data' => [
                    'error' => 'No paraphrase with id ' . $paraphraseId,
                ],
            ]);
        }

        if (count($paraphrase->questions) != 0) {
            return response()->json([
                'success' => true,
                'data' => $paraphrase->questions,
            ]);
        }

        $propmt = 'Сфорулюй 5 тестових запитань до цього тексту ' . $paraphrase->paraphrase . ' , який є стислим переказом теми ' . $paraphrase->topic . ' з предмету ' . $paraphrase->subject . '. На кожне запитання 4 варіанти відповіді, де тільки одна правильна. Запитання мають бути виключно українською мовою. Всі запитання надай у полі "questions" як масив запитань. В об\'єкті питання має бути поле "question" значенням якого є текст запитання. Масив "options" з переліком варіантів відповіді. Та "correct" значення якого індекс правильного варіанту відповіді з масиву "options"';
        $response = $this->gptRequest(true, $propmt);
        if (!array_key_exists('choices', $response)) {
            return response()->json([
                'success' => false,
                'data' => [
                    'error' => $response['error'],
                ],
            ]);
        }

        $questions = $this->storeParaphraseTest(json_decode($response['choices'][0]['message']['content']), $paraphraseId);
        return response()->json([
            'success' => true,
            'data' => $questions,
        ]);
    }
}


// "topic": "Компоненти цитоплазми еукаріотичної клітини",
// "subject": "біологія",

// "topic": "Світовий океан та його частини",
// "subject": "географія",

// "topic": "Русь-Україна: культура IX–XIV століть",
// "subject": "історія"


// ? DEFAULT PARAPHRASE
// $response = Http::withHeaders([
        //     'Authorization' => 'Bearer ' . config('gpt.openai.api_key'),
        //     'Content-Type' => 'application/json',
        // ])->post(config('gpt.openai.api_url'), [
        //     // 'response_format' => null,
        //     'messages' => [
        //         [
        //             'role' => 'user',
        //             'content' => 'Розкажи основну теоретичну інформацію про "' . $request->topic . '" з предмету ' . $request->subject,
        //         ]
        //     ],

        //     'model' => config('gpt.openai.model'),
        //     'temperature' => (float)config('gpt.openai.temperature'),
        //     'max_tokens' => config('gpt.openai.max_tokens'),
        //     'top_p' => (float)config('gpt.openai.top_p'),
        //     'frequency_penalty' => (float)config('gpt.openai.frequency_penalty'),
        //     'presence_penalty' => (float)config('gpt.openai.presence_penalty'),
        //     'stop' => [config('gpt.openai.stop')],
        // ])->json();

// * ...

        // $createdParaphraseId = Paraphrase::create([
        //     'subject' => $request->subject,
        //     'topic' => $request->topic,
        //     'paraphrase' => $response['choices'][0]['message']['content'],
        //     'user_id' => auth('sanctum')->user()->id,
        // ])->id;

        // $paraphrase = Paraphrase::with('user')->where('id', $createdParaphraseId)->first();

// ? LITERATURE PARAPHRASE
  // $response = Http::withHeaders([
        //     'Authorization' => 'Bearer ' . config('gpt.openai.api_key'),
        //     'Content-Type' => 'application/json',
        // ])->post(config('gpt.openai.api_url'), [
        //     'messages' => [
        //         [
        //             'role' => 'user',
        //             'content' => 'Розкажи послідовно основні події літературного твору "' . $request->topic . '", дійові особи, тема, думка твору. ' . $paraphraseSizePrompt,
        //         ]
        //     ],

        //     'model' => config('gpt.openai.model'),
        //     'temperature' => (float)config('gpt.openai.temperature'),
        //     'max_tokens' => config('gpt.openai.max_tokens'),
        //     'top_p' => (float)config('gpt.openai.top_p'),
        //     'frequency_penalty' => (float)config('gpt.openai.frequency_penalty'),
        //     'presence_penalty' => (float)config('gpt.openai.presence_penalty'),
        //     'stop' => [config('gpt.openai.stop')],
        // ])->json();

// ? TEST
// $response = Http::withHeaders([
        //     'Authorization' => 'Bearer ' . config('gpt.openai.api_key'),
        //     'Content-Type' => 'application/json',
        // ])->post(config('gpt.openai.api_url'), [
        //     'response_format' => ['type' => 'json_object'],
        //     'messages' => [
        //         [
        //             'role' => 'system',
        //             'content' => 'Provide a valid JSON output'
        //         ],
        //         [
        //             'role' => 'user',
        //             'content' => 'Сфорулюй 5 тестових запитань до цього тексту ' . $paraphrase->paraphrase . ' , який є стислим переказом теми ' . $paraphrase->topic . ' з предмету ' . $paraphrase->subject . '. На кожне запитання 4 варіанти відповіді, де тільки одна правильна. Запитання мають бути виключно українською мовою. Всі запитання надай у полі "questions" як масив запитань. В об\'єкті питання має бути поле "question" значенням якого є текст запитання. Масив "options" з переліком варіантів відповіді. Та "correct" значення якого індекс правильного варіанту відповіді з масиву "options"',
        //         ]
        //     ],

        //     'model' => config('gpt.openai.model'),
        //     'temperature' => (float)config('gpt.openai.temperature'),
        //     'max_tokens' => config('gpt.openai.max_tokens'),
        //     'top_p' => (float)config('gpt.openai.top_p'),
        //     'frequency_penalty' => (float)config('gpt.openai.frequency_penalty'),
        //     'presence_penalty' => (float)config('gpt.openai.presence_penalty'),
        //     'stop' => [config('gpt.openai.stop')],
        // ])->json();

        // $test = json_decode($response['choices'][0]['message']['content']);

           // foreach ($test->questions as $question) {
        //     $questionId = Question::create([
        //         'question' => $question->question,
        //         'paraphrase_id' => $paraphraseId,
        //     ])->id;

        //     for ($i = 0; $i < 4; $i++) {
        //         Option::create([
        //             'option' => $question->options[$i],
        //             'correct' => $i == $question->correct,
        //             'question_id' => $questionId,
        //         ]);
        //     }
        // }

        // $paraphrase = Paraphrase::where('id', $paraphraseId)->with('questions.options')->first();