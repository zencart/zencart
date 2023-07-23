<?php

require __DIR__.'/../vendor/autoload.php';

$sanitizer = \HtmlSanitizer\Sanitizer::create(['extensions' => ['basic', 'code', 'image', 'list', 'table', 'extra']]);

$input = file_get_contents(__DIR__.'/fixture.html');
$times = 100;
$time = microtime(true);

echo "Running...\n";

for ($i = 0; $i < $times; ++$i) {
    $output = $sanitizer->sanitize($input);
}

$total = (microtime(true) - $time) * 1000;

echo 'Total for '.$times.' loops: '.round($total, 2)."ms\n";
echo 'Time per loop: '.round($total / $times, 2)."ms\n";
echo "\n";
