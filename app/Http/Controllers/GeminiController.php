<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class GeminiController extends Controller
{
    public function test()
    {
        $apiKey = config('services.gemini.key');
        $model = config('services.gemini.model');

        if (empty($apiKey)) {
            return response()->json([
                'error' => 'Gemini API key is not configured.',
            ], 500);
        }

        $response = Http::timeout(30)
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
            ])
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => 'Explain what an AI agent is in two simple sentences.',
                                ],
                            ],
                        ],
                    ],
                ]
            );

        if ($response->failed()) {
            return response()->json([
                'error' => 'Gemini API request failed.',
                'details' => $response->json('error.message')
                    ?? 'Unknown API error.',
            ], 502);
        }

        return response()->json([
            'success' => true,
            'answer' => $response->json(
                'candidates.0.content.parts.0.text'
            ),
        ]);
    }
}