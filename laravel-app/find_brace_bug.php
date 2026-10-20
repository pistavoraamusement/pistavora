<?php
// Usage: php find_brace_bug.php public/css/user-app-theme.css
$path = $argv[1] ?? 'public/css/user-app-theme.css';
$lines = file($path);
$depth = 0;
$lastZeroLine = 0;

foreach ($lines as $i => $line) {
    $lineNo = $i + 1;
    $opens = substr_count($line, '{');
    $closes = substr_count($line, '}');
    $depth += $opens - $closes;

    if ($depth === 0) {
        $lastZeroLine = $lineNo;
    }
    if ($depth < 0) {
        echo "Extra closing brace found at line $lineNo (depth went negative): " . trim($line) . "\n";
        exit;
    }
}

if ($depth !== 0) {
    echo "File ends with unbalanced braces. Final depth: $depth\n";
    echo "Last point where braces were balanced: line $lastZeroLine\n";
    echo "-- everything after line $lastZeroLine is inside an unclosed rule --\n";
    echo "Showing lines $lastZeroLine to " . min($lastZeroLine + 15, count($lines)) . ":\n\n";
    for ($j = $lastZeroLine - 1; $j < min($lastZeroLine + 15, count($lines)); $j++) {
        echo ($j + 1) . ": " . $lines[$j];
    }
} else {
    echo "Braces are balanced.\n";
}
