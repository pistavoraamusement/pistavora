<?php
/**
 * apply_google_stateless.php
 * Run from your laravel-app root: php apply_google_stateless.php
 *
 * GoogleController used stateful Socialite (Socialite::driver('google')->user()),
 * which requires the 'state' token from the redirect step to still match the
 * one stored in session when the callback comes back. Any session/cookie hiccup
 * (Chrome prefetch, a stale tab, session driver timing, etc.) breaks that match
 * and throws Laravel\Socialite\Two\InvalidStateException.
 *
 * FacebookController already avoids this entirely by using ->stateless(), and
 * that login has been working reliably. This patch makes Google use the exact
 * same, already-proven pattern — nothing else in the app is touched.
 */

$path = __DIR__ . '/app/Http/Controllers/GoogleController.php';

if (!file_exists($path)) {
    echo "[SKIP] GoogleController.php not found at $path\n";
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

patchIn(
    $content,
    "        return Socialite::driver('google')->redirect();",
    "        return Socialite::driver('google')->stateless()->redirect();",
    'GoogleController::redirect() — stateless (same as Facebook)',
    $failed
);

patchIn(
    $content,
    "        \$googleUser = Socialite::driver('google')->user();",
    "        \$googleUser = Socialite::driver('google')->stateless()->user();",
    'GoogleController::callback() — stateless (same as Facebook)',
    $failed
);

file_put_contents($path, $content);

echo $failed
    ? "\nMay [WARN] sa itaas — i-paste mo dito ang output para maayos natin.\n"
    : "\nTapos na. Lahat ng patch ay [DONE] o [OK].\n";
