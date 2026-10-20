<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI Idea Generator (Gemini, libre ang API key sa https://aistudio.google.com/apikey).
 * Kinukuha ang key at model sa config/services.php -> 'gemini' (galing sa .env).
 *
 * Tumatanggap ng context.selection = { attraction, age_group, location, notes, kind, count }
 *   kind  : mix | promo | newride | marketing
 *   count : 1-10 (ilang ideya ang gusto)
 */
class PromoAiController extends Controller
{
    private const KINDS = [
        'mix'       => 'A mix of promos, new ride or upgrade ideas, and marketing ideas.',
        'promo'     => 'Only promos: discounts, bundles, group deals and limited-time offers that make more people book.',
        'newride'   => 'Only product ideas: new rides, new attractions, upgrades or seasonal features the park could build or add.',
        'marketing' => 'Only marketing ideas: events, social media campaigns, partnerships and loyalty perks.',
    ];

    public function suggest(Request $request)
    {
        // Pwedeng umabot ng ilang segundo ang AI call; huwag hayaang pumatay ng request ang 30s na limit ng PHP.
        @set_time_limit(120);

        $data = $request->validate([
            'question' => 'required|string|max:600',
            'context'  => 'nullable|array',
        ]);

        $key = config('services.gemini.key');
        if (! $key) {
            return response()->json(['message' => 'AI is not configured. Set GEMINI_API_KEY in .env.'], 500);
        }

        $context = $data['context'] ?? [];
        $sel     = is_array($context['selection'] ?? null) ? $context['selection'] : [];
        $count   = max(1, min(10, (int) ($sel['count'] ?? 5)));
        $kind    = array_key_exists($sel['kind'] ?? '', self::KINDS) ? $sel['kind'] : 'mix';

        $system = strtr(<<<'TXT'
You are a business advisor for a theme park (Lipa Branch) with three attractions:
Dino Lakbay, Skataya and Piyesta Rides.
You get the park's real online booking data as JSON (bookings per attraction, share of all
bookings, top age groups, top packages, where visitors come from) and the manager's choices
(attraction, audience, location, kind of ideas, how many).

Your job: give exactly {COUNT} ideas for the chosen attraction.
Kind of ideas wanted: {KIND}

How to think:
- Compare the chosen attraction with the others using the data (bookings and share).
- If it has few bookings compared to the others, focus on getting more people to book it:
  attractive promos (discounts, bundles with a stronger attraction, group or barkada deals,
  off-peak offers) and, if the kind allows, ideas to improve or add to the attraction itself
  (a new ride, an upgrade, a seasonal feature) that fits the chosen audience and location.
- If it is the best seller, give ideas to keep it growing and to upsell.
- Fit the ideas to the chosen age group and visitor location.

Format rules (important):
- Start with ONE short sentence about what the data shows. Do not let thin data take over the answer.
- Then exactly {COUNT} bullets. Each bullet is ONE line:
  - **Idea name** (Promo / New ride / Marketing): what it is, and the offer in pesos if it makes sense. Why: one short reason that points to the data.
- Finish every bullet. Never stop in the middle of a sentence.
- Use simple words. Use "- " bullets and **bold** only. No tables, no headings.
- Prices you suggest are only suggestions. Do not invent booking numbers that are not in the data.
TXT, [
            '{COUNT}' => (string) $count,
            '{KIND}'  => self::KINDS[$kind],
        ]);

        $payload = [
            'question'     => $data['question'],
            'booking_data' => $context,
        ];

        $models = array_values(array_unique(array_filter([
            config('services.gemini.model'),
            config('services.gemini.fallback_model'),
        ])));

        if (! $models) {
            return response()->json(['message' => 'AI is not configured. Set GEMINI_MODEL in .env.'], 500);
        }

        $lastStatus = null;
        $lastMessage = null;

        foreach ($models as $model) {
            try {
                $res = $this->callGemini($key, $model, $system, $payload);
            } catch (\Throwable $e) {
                Log::error('Promo AI exception (' . $model . '): ' . $e->getMessage());
                $lastStatus = 0;
                $lastMessage = $e->getMessage();
                continue; // subukan ang fallback model
            }

            if ($res->successful()) {
                $answer = $this->extractText($res);

                if ($answer !== '') {
                    return response()->json(['answer' => $answer]);
                }

                // Walang text (hal. na-block ng safety filter) -> subukan ang susunod na model
                $lastStatus = 200;
                $lastMessage = $res->json('promptFeedback.blockReason')
                    ?: ($res->json('candidates.0.finishReason') ?: 'empty answer');
                Log::warning('Promo AI empty answer (' . $model . ')', ['body' => $res->body()]);
                continue;
            }

            $lastStatus = $res->status();
            $lastMessage = $res->json('error.message') ?: $res->body();
            Log::warning('Promo AI request failed (' . $model . ')', ['status' => $lastStatus, 'body' => $res->body()]);

            // Ang 400/401/403 (maling key, bawal ang request) ay hindi maaayos ng ibang model
            if (in_array($lastStatus, [400, 401, 403], true)) {
                break;
            }
            // 404 (walang ganyang model), 429 (naubos ang free quota), 5xx -> subukan ang fallback
        }

        return response()->json([
            'message' => 'The AI service returned an error'
                . ($lastStatus ? ' (' . $lastStatus . ')' : '')
                . ($lastMessage ? ': ' . mb_substr((string) $lastMessage, 0, 300) : '.'),
        ], 502);
    }

    private function callGemini(string $key, string $model, string $system, array $payload): Response
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent';

        return Http::withHeaders([
            'x-goog-api-key' => $key,
            'Content-Type'   => 'application/json',
        ])
            ->connectTimeout(10)
            ->timeout(60) // mas mababa kaysa set_time_limit para laging matapos muna ang HTTP call
            ->post($url, [
                'systemInstruction' => [
                    'parts' => [['text' => $system]],
                ],
                'contents' => [
                    [
                        'role'  => 'user',
                        'parts' => [['text' => json_encode($payload, JSON_UNESCAPED_UNICODE)]],
                    ],
                ],
                'generationConfig' => [
                    // Mataas ito dahil ang "thinking" ng Gemini ay kasama sa bilang ng output tokens.
                    // Noong 1200 lang, naputol ang sagot sa gitna ("Offer: Group").
                    'maxOutputTokens' => 8192,
                    'temperature'     => 0.8,
                ],
            ]);
    }

    private function extractText(Response $res): string
    {
        $parts = $res->json('candidates.0.content.parts', []);

        return trim(collect($parts)
            ->pluck('text')
            ->filter()
            ->implode("\n"));
    }
}