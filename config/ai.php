<?php

return [
    'ml' => [
        'python_bin' => env('ML_PYTHON_BIN', 'python'),
        'enabled' => (bool) env('ML_ENABLED', true),
        'timeout' => 10,
    ],
    'llm' => [
        'enabled' => (bool) env('LLM_ENABLED', true),
        'provider' => env('LLM_PROVIDER', 'gemini'),
        'api_key' => env('LLM_API_KEY'),
        'model' => env('LLM_MODEL', 'gemini-2.0-flash'),
        'timeout' => (int) env('LLM_TIMEOUT', 15),
    ],
];
