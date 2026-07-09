<?php
ini_set('memory_limit', '4096M');

function elapsedDebug(\Closure $fn) {
    if (function_exists('memory_reset_peak_usage')) {
        memory_reset_peak_usage();
    }

    $start = microtime(true);
    $fn();
    $durationMs = (microtime(true) - $start) * 1000;
    $durationMsFormatted = number_format($durationMs, 3, '.', '');

    $peakBytes = memory_get_peak_usage(true);
    $peakMb = $peakBytes / (1024 * 1024);
    $peakRam = rtrim(rtrim(number_format($peakMb, 2, '.', ''), '0'), '.');

    $sectionWidth = 20;
    $elapsedSection = sprintf("%s: %8s", 'Elapsed', "{$durationMsFormatted} ms");
    $peakRamSection = sprintf("%s: %8s", 'Peak RAM', "{$peakRam} MB");

    echo sprintf(
        " PHP %-8s | %-{$sectionWidth}s | %-{$sectionWidth}s\n",
        PHP_VERSION,
        $elapsedSection,
        $peakRamSection
    );
}

// Создаем последовательный массив без дырок
$length = 10000000; // 10 000 000
$arr = [];
for ($i = 0; $i <= $length; $i++) {
    $arr[] = $i;
}

// array size = 10 000 000
// Наша хорошая функция
function goodFunc($arr) {
    // Мы добавили костыль, конкретно в этом месте, мы добавляем какое-то число, но оно не должно попасть в вверх
    $someVirtualPayload = 123;
    $arr[] = $someVirtualPayload;
    // Дальше идет обработка массива
}

elapsedDebug(function () use ($arr) {
    goodFunc($arr);
});
// PHP 5.6.40   | Elapsed: 448.260 ms  | Peak RAM: 2393.75 MB
// PHP 7.2.34   | Elapsed: 74.961 ms   | Peak RAM: 1026.01 MB
// PHP 8.1.34   | Elapsed: 77.254 ms   | Peak RAM: 1026.01 MB
// PHP 8.2.32   | Elapsed: 40.413 ms   | Peak RAM: 514.01 MB
// PHP 8.3.30   | Elapsed: 39.689 ms   | Peak RAM: 514.01 MB
// PHP 8.4.20   | Elapsed: 40.029 ms   | Peak RAM: 514.01 MB
// PHP 8.5.8RC1 | Elapsed: 41.055 ms   | Peak RAM: 514.01 MB
