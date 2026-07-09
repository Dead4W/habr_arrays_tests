<?php
ini_set('memory_limit', '2048M');

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

elapsedDebug(function () use (&$arr, $length) {
    // Вставляем элемент, который порождает разрыв между ключами
    $arr[$length*10] = $length*10;
    // ИЛИ Вставляем элемент со строковым ключом
    $arr['test_string'] = $length*10;
});
// PHP 5.6.40   | Elapsed: 0.007 ms    | Peak RAM:  1426 MB
// PHP 7.2.34   | Elapsed: 103.956 ms  | Peak RAM:  1090 MB
// PHP 8.1.34   | Elapsed: 117.026 ms  | Peak RAM:  1154 MB
// PHP 8.2.32   | Elapsed: 99.345 ms   | Peak RAM:   898 MB
// PHP 8.3.30   | Elapsed: 102.610 ms  | Peak RAM:   898 MB
// PHP 8.4.20   | Elapsed: 99.213 ms   | Peak RAM:   898 MB
// PHP 8.5.8RC1 | Elapsed: 95.777 ms   | Peak RAM:   898 MB