<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\PremiumPurchaseRequest;
use App\Models\Paraphrase;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUserByToken()
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'data' => [
                    'error' => 'User with same token does not exists',
                ],
            ]);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    public function premiumPurchase(PremiumPurchaseRequest $request)
    {
        $request->validated();
        $userId = auth('sanctum')->user()->id;
        $user = User::where('id', $userId)->first();

        if ($user->premium) {
            return response()->json([
                'success' => false,
                'data' => [
                    'error' => 'This user already has premium account',
                ],
            ]);
        }

        $user->premium = true;
        $user->save();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    public function getUserHistoryParaphraseById($paraphraseId)
    {
        $paraphrase = Paraphrase::where('id', $paraphraseId)->with('user', 'questions.options')->first();
        if (!$paraphrase) {
            return response()->json([
                'success' => false,
                'data' => [
                    'error' => 'No paraphrase with id ' . $paraphraseId,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'paraphrase' => $paraphrase,
            ],
        ]);
    }
}
