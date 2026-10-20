<?php
/**
 * apply_chatbot_quality.php
 * Run from your laravel-app root: php apply_chatbot_quality.php
 *
 * Fixes three things in the Wonder Park chatbot:
 *
 * 1. KULANG NA SAGOT — the prompt told the bot to keep every answer to 2-4
 *    sentences and to always say "for 1 pax", so "How much is Field of
 *    Rides?" only mentioned a few prices. Now, for price / inclusion
 *    questions, it must cover EVERY package of that experience, one short
 *    line each, and say "per guest" for per-head passes.
 *
 * 2. PAMINSAN-MINSANG GLITCH — a temporary Gemini server error (5xx) is now
 *    retried once, and the request timeout is longer for the newer model.
 *
 * 3. WIDGET (public/js/reks-assist.js):
 *    - one question at a time (clicking two quick chips fast made the
 *      answers arrive out of order, e.g. RollerFever answered with Field
 *      of Rides),
 *    - "glitch" replies are no longer saved into the chat history, so a
 *      failed answer can't confuse the next one,
 *    - line breaks in the bot's reply are now shown.
 */

$root = __DIR__;
$failed = false;

function patchFile($path, $old, $new, $label) {
    global $failed;
    if (!file_exists($path)) {
        echo "[SKIP] $label — file not found: $path\n";
        $failed = true;
        return;
    }
    $content = file_get_contents($path);
    if (strpos($content, $new) !== false) {
        echo "[OK]   $label — na-patch na dati, walang ginalaw.\n";
        return;
    }
    if (strpos($content, $old) === false) {
        echo "[WARN] $label — hindi na-match ang expected old content. I-check manually.\n";
        $failed = true;
        return;
    }
    file_put_contents($path, str_replace($old, $new, $content));
    echo "[DONE] $label — na-patch.\n";
}

$chat = $root . '/app/Http/Controllers/ChatController.php';
$js   = $root . '/public/js/reks-assist.js';

// ---------------------------------------------------------------- 1. prompt
patchFile(
    $chat,
    'Keep answers brief (2-4 sentences) and warm.',
    'Keep answers warm and to the point. For general questions use 2-4 sentences.'
    . "\nBut when a guest asks about the price, cost, packages or inclusions of an"
    . "\nexperience (Dino Adventure, RollerFever or Field of Rides), cover EVERY"
    . "\npackage listed for it below — walk-in passes, group bundles and party"
    . "\npackages alike — with its exact price. Write one short line per package"
    . "\n(package name, then price), then one closing sentence pointing them to"
    . "\nthe Booking page. Never leave a package out to keep it short.",
    'ChatController prompt — answer length rule'
);

patchFile(
    $chat,
    "- Whenever you mention a package, always include its starting price and\n"
    . "  the pax (guest count) it starts at, the same way you would for any\n"
    . "  other package — don't describe one package with numbers and another\n"
    . "  without. If you don't have an exact figure for something, say so rather\n"
    . "  than leaving it vague.",
    "- Whenever you mention a package, always include its exact price from the\n"
    . "  list below. For per-guest passes and per-ride tickets say \"per guest\"\n"
    . "  (or \"per head\") — do NOT say \"for 1 pax\". For party packages say the\n"
    . "  starting price and the pax it starts at, for example \"starts at 15,000\n"
    . "  PHP for 10 pax\". If you don't have an exact figure for something, say\n"
    . "  so rather than leaving it vague.\n"
    . "- Answer only the experience the guest asked about, using the latest\n"
    . "  question — never answer about a different experience.",
    'ChatController prompt — pax wording rule'
);

patchFile(
    $chat,
    'Keep answers short and conversational — this is a chat widget, not an essay.',
    'Keep answers friendly and conversational — this is a chat widget, not an essay.',
    'ChatController prompt — closing line'
);

// ------------------------------------------------- 2. timeout + one retry
patchFile(
    $chat,
    'return Http::timeout(20)',
    'return Http::timeout(45)',
    'ChatController — longer request timeout'
);

patchFile(
    $chat,
    "            \$response = \$geminiCall(1500);\n",
    "            \$response = \$geminiCall(1500);\n\n"
    . "            // A temporary Gemini server error (5xx, e.g. \"model overloaded\")\n"
    . "            // usually clears in a second — retry once before showing a glitch.\n"
    . "            if (\$response->serverError()) {\n"
    . "                usleep(800000);\n"
    . "                \$response = \$geminiCall(1500);\n"
    . "            }\n",
    'ChatController — retry once on 5xx'
);

// --------------------------------------------------------------- 3. widget
patchFile(
    $js,
    "  async function sendMessage(text) {\n    if (!text || !text.trim()) return;\n",
    "  let sending = false;\n\n  async function sendMessage(text) {\n    if (!text || !text.trim() || sending) return;\n    sending = true;\n",
    'reks-assist.js — one question at a time'
);

patchFile(
    $js,
    "      history.push({ role: 'user', content: text });\n"
    . "      history.push({ role: 'assistant', content: data.reply });\n"
    . "    } catch (err) {\n"
    . "      removeTyping();\n"
    . "      appendMessage(\"Naku, di ako makaconnect sa server ngayon. Subukan ulit mamaya.\", 'bot');\n"
    . "      console.error('Wonder Park chat error:', err);\n"
    . "    }\n"
    . "  }\n",
    "      // Don't save failed replies (glitch / busy messages) as if the bot\n"
    . "      // had said them — they'd confuse the next answer.\n"
    . "      if (!data.error) {\n"
    . "        history.push({ role: 'user', content: text });\n"
    . "        history.push({ role: 'assistant', content: data.reply });\n"
    . "      }\n"
    . "    } catch (err) {\n"
    . "      removeTyping();\n"
    . "      appendMessage(\"Naku, di ako makaconnect sa server ngayon. Subukan ulit mamaya.\", 'bot');\n"
    . "      console.error('Wonder Park chat error:', err);\n"
    . "    } finally {\n"
    . "      sending = false;\n"
    . "    }\n"
    . "  }\n",
    'reks-assist.js — keep error replies out of history + release the lock'
);

patchFile(
    $js,
    "    bubble.textContent = text;\n    row.appendChild(bubble);\n",
    "    bubble.textContent = text;\n    bubble.style.whiteSpace = 'pre-line';\n    row.appendChild(bubble);\n",
    'reks-assist.js — show line breaks in replies'
);

echo $failed
    ? "\nMay [WARN]/[SKIP] sa itaas — i-paste mo dito ang output para maayos natin.\n"
    : "\nTapos na. Lahat ng patch ay [DONE] o [OK].\n";
