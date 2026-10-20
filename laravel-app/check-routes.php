<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$missing = [];
$dirs = [__DIR__.'/resources/views', __DIR__.'/app'];

foreach ($dirs as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (!preg_match('/\.php$/', $file->getFilename())) continue;
        $code = file_get_contents($file->getPathname());
        if (preg_match_all('/route\(\s*[\'"]([a-zA-Z0-9_.\-]+)[\'"]/', $code, $m)) {
            foreach (array_unique($m[1]) as $name) {
                if (!Illuminate\Support\Facades\Route::has($name)) {
                    $missing[$name][] = str_replace(__DIR__.DIRECTORY_SEPARATOR, '', $file->getPathname());
                }
            }
        }
    }
}

ksort($missing);
if (!$missing) { echo "OK - walang nawawalang route names\n"; exit; }
foreach ($missing as $name => $files) {
    echo $name . '  <-  ' . implode(', ', array_unique($files)) . "\n";
}
