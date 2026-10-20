<?php
/**
 * apply_chatbot_thinking_fix.php
 * Run from your laravel-app root: php apply_chatbot_thinking_fix.php
 *
 * gemini-3.8-flash "thinks" before answering by default, and that thinking
 * eats into maxOutputTokens. For a short "say hi" it used ~140 tokens just
 * on thinking; for a full catalog answer (every package + price) it can eat
 * the WHOLE budget, so Gemini returns finishReason MAX_TOKENS with an empty
 * "text" — the bot then shows a blank bubble instead of an answer.
 *
 * Fix:
 *   1. Turn thinking off (thinkingBudget: 0) — this chatbot doesn't need
 *      chain-of-thought, and it frees the entire token budget for the
 *      actual answer.
 *   2. Treat an empty/missing text the same way (fallback message), so a
 *      blank bubble can never reach the guest even if Gemini returns one.
 */

$path = __DIR__ . '/app/Http/Controllers/ChatController.php';

if (!file_exists($path)) {
    echo "[SKIP] ChatController.php not found at $path\n";
    exit(1);
}

$content = file_get_contents($path);
$failed = false;

function patchIn(&$content, $old, $new, $label, &$failed) {
    if (strpos($content, $new) !== false) {
        echo "[OK]   $label — na-patch na dati, walang ginalaw.\n";
        return;
    }
    if (strpos($content, $old) === false) {
        echo "[WARN] $label — hindi na-match ang expected old content. I-check manually.\n";
        $failed = true;
        return;
    }
    $content = str_replace($old, $new, $content);
    echo "[DONE] $label — na-patch.\n";
}

// 1. turn off thinking so the whole token budget goes to the actual answer
patchIn(
    $content,
    "                        'generationConfig' => [\n"
    . "                            'temperature' => 0.4,\n"
    . "                            'maxOutputTokens' => \$maxTokens,\n"
    . "                        ],\n",
    "                        'generationConfig' => [\n"
    . "                            'temperature' => 0.4,\n"
    . "                            'maxOutputTokens' => \$maxTokens,\n"
    . "                            // This is a short customer-support chatbot — it doesn't need\n"
    . "                            // chain-of-thought, and thinking tokens were eating the whole\n"
    . "                            // output budget and leaving the answer blank. Turn it off so\n"
    . "                            // every token goes to the actual reply.\n"
    . "                            'thinkingConfig' => ['thinkingBudget' => 0],\n"
    . "                        ],\n",
    'ChatController — disable Gemini thinking tokens',
    $failed
);

// 2. never let an empty/missing text through as a blank reply
patchIn(
    $content,
    "            \$reply = \$response->json('candidates.0.content.parts.0.text', \"Sorry, I don't have an answer right now. Please try again?\");\n",
    "            \$replyText = \$response->json('candidates.0.content.parts.0.text');\n"
    . "            \$reply = (is_string(\$replyText) && trim(\$replyText) !== '')\n"
    . "                ? \$replyText\n"
    . "                : \"Sorry, I don't have an answer right now. Please try again?\";\n",
    'ChatController — never send a blank reply to the guest',
    $failed
);

file_put_contents($path, $content);

echo $failed
    ? "\nMay [WARN] sa itaas — i-paste mo dito ang output para maayos natin.\n"
    : "\nTapos na. Lahat ng patch ay [DONE] o [OK].\n";
