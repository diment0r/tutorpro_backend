<?php

namespace App\Http\Controllers\Gpt;

use App\Http\Controllers\Controller;
use App\Http\Requests\Paraphrase\LiteratureParaphraseRequest;
use App\Models\Paraphrase;
use Illuminate\Http\Request;

class LiteratureController extends Controller
{
    public function topicParaphrase(LiteratureParaphraseRequest $request)
    {
        $request->validated();
        $response = Paraphrase::with('user', 'questions.options')->where('topic', $request->topic)->first();

        if ($response) {
            return response()->json([
                'success' => true,
                'data' => $response,
            ]);
        }
    }
}
