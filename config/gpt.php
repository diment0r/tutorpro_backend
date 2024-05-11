<?php

return [
    'openai' => [
        'api_key' => env('OPEN_API_KEY'),
        'api_url' => env('OPEN_API_URL'),

        'model' => env('GPT_MODEL'),
        'temperature' => env('GPT_TEMPERATURE'),
        'max_tokens' => env('MAX_TOKENS'),
        'top_p' => env('GPT_TOP_P'),
        'frequency_penalty' => env('GPT_FREQUENCY_PENALTY'),
        'presence_penalty' => env('GPT_PRESENCE_PENALTY'),
        'stop' => env('GPT_STOP'),
    ],
    'subjects' => [
        'biology' => env('BIOLOGY_SUBJECT_NAME'),
        'geography' => env('GEOGRAPHY_SUBJECT_NAME'),
        'history' => env('HISTORY_SUBJECT_NAME'),
        'literature' => env('LITERATURE_SUBJECT_NAME'),
    ],
    'paraphrase_size' => [
        'short' => env('LITERATURE_PRAPHRASE_SHORT_SIZE_PROMPT'),
        'long' => env('LITERATURE_PRAPHRASE_LONG_SIZE_PROMPT'),
    ]
];
