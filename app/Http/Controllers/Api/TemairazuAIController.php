<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Throwable;

class TemairazuAIController extends Controller
{
    public function chat(Request $request)
    {
        // Validate the incoming message.
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $userMessage = trim($validated['message']);
        $normalizedMessage = strtolower($userMessage);

        /*
        |--------------------------------------------------------------------------
        | Deterministic answers for common booking questions
        |--------------------------------------------------------------------------
        | These answers avoid incomplete responses from the local language model
        | for common questions about the booking process.
        */

        $bookingKeywords = [
            'how do i book',
            'how to book',
            'explain how to book',
            'booking process',
            'book a hotel room',
            'how can i book',
        ];

        foreach ($bookingKeywords as $keyword) {
            if (str_contains($normalizedMessage, $keyword)) {
                return response()->json([
                    'reply' => "Here is how to book a hotel room:\n\n"
                        . "1. Choose a hotel and enter your check-in and check-out dates.\n"
                        . "2. Select a suitable room and enter your guest details.\n"
                        . "3. Review your information and follow the booking page instructions to complete your reservation.\n\n"
                        . "You can start your reservation using the Book a Room option on our website.",
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Connect to the local Ollama AI model
        |--------------------------------------------------------------------------
        */

        try {
            $response = Http::connectTimeout(10)
                ->timeout(180)
                ->post('http://127.0.0.1:11434/api/chat', [
                    'model' => 'qwen3:4b',
                    'stream' => false,
                    'think' => false,

                    'options' => [
                        'num_predict' => 512,
                        'temperature' => 0.2,
                    ],

                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => <<<'PROMPT'
/no_think

You are Temairazu AI, a helpful virtual assistant for a hotel booking website.

Your responsibilities:
- Help guests understand how to use the hotel booking website.
- Explain the general hotel reservation process.
- Help guests understand what information they may need when booking.
- Answer general questions about hotel reservations.
- Provide short, clear, friendly answers.

Important rules:
1. Give the final answer directly.
2. Never display internal reasoning, analysis, or thinking.
3. Do not write phrases such as "Let me think", "We need to", or "Let's analyze".
4. Do not repeat the user's question unnecessarily.
5. Use numbered steps when explaining a process.
6. Keep answers concise and easy to understand.
7. Do not invent hotel availability, prices, booking confirmations, or hotel policies.
8. If you do not know a specific hotel policy, explain that the guest should check the hotel's official information.
9. Do not claim that a reservation has been created or modified.
10. If a guest wants to make a reservation, direct them to the website's booking page.
11. Return only the answer intended for the guest.
PROMPT,
                        ],
                        [
                            'role' => 'user',
                            'content' => "/no_think\n" . $userMessage,
                        ],
                    ],
                ]);

            /*
            |--------------------------------------------------------------------------
            | Handle an unsuccessful Ollama response
            |--------------------------------------------------------------------------
            */

            if (!$response->successful()) {
                Log::error('Temairazu AI: Ollama returned an unsuccessful response.', [
                    'status' => $response->status(),
                    'body' => mb_substr($response->body(), 0, 1000),
                ]);

                return response()->json([
                    'reply' => 'The AI assistant is temporarily unavailable. Please try again shortly or use the booking page.',
                ], 502);
            }

            /*
            |--------------------------------------------------------------------------
            | Extract the AI response
            |--------------------------------------------------------------------------
            */

            $reply = trim((string) $response->json('message.content', ''));

            // Remove any exposed thinking or analysis tags.
            $reply = preg_replace(
                '/<think\b[^>]*>.*?<\/think>/is',
                '',
                $reply
            );

            $reply = preg_replace(
                '/<analysis\b[^>]*>.*?<\/analysis>/is',
                '',
                $reply
            );

            $reply = trim((string) $reply);

            /*
            |--------------------------------------------------------------------------
            | Detect incomplete or internal-reasoning responses
            |--------------------------------------------------------------------------
            */

            $internalReasoningPhrases = [
                'we need to',
                'let me think',
                'let\'s think',
                'we should answer',
                'we need answer',
                'the user asks',
                'the user wants',
                'we can say',
                'let\'s craft',
                'analysis:',
                'reasoning:',
            ];

            $containsInternalReasoning = false;

            foreach ($internalReasoningPhrases as $phrase) {
                if (str_contains(strtolower($reply), $phrase)) {
                    $containsInternalReasoning = true;
                    break;
                }
            }

            $done = (bool) $response->json('done', false);
            $doneReason = (string) $response->json('done_reason', '');

            if (
                $reply === ''
                || $containsInternalReasoning
                || !$done
                || $doneReason === 'length'
            ) {
                Log::warning('Temairazu AI: Empty, internal, or incomplete AI response.', [
                    'empty_reply' => $reply === '',
                    'internal_reasoning_detected' => $containsInternalReasoning,
                    'done' => $done,
                    'done_reason' => $doneReason,
                ]);

                return response()->json([
                    'reply' => 'I can help with general hotel booking questions. To make a reservation, open the Book a Room page, select your dates and room, enter your details, and follow the instructions to complete your booking.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Return the final response to the chat interface
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'reply' => $reply,
            ]);

        } catch (ConnectionException $exception) {
            Log::error('Temairazu AI: Could not connect to Ollama.', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'reply' => 'The AI assistant cannot connect to its AI service right now. Please try again later or use the booking page.',
            ], 503);

        } catch (Throwable $exception) {
            Log::error('Temairazu AI: Unexpected error.', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return response()->json([
                'reply' => 'Something went wrong while processing your message. Please try again.',
            ], 500);
        }
    }
}