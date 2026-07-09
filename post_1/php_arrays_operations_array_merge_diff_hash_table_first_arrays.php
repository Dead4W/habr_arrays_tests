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

// $arrList - последовательный массив (список)
// $arrTable - ассоциативный массив (hash-table)
$length = 1000000; // 1 000 000
$arrList = [];
for ($i = 0; $i < $length; $i++) {
    $arrList[] = $i;
}
$arrTable = ['a' => 1];
for ($i = 0; $i < $length; $i++) {
    $arrTable[] = $i;
}

// Первым хеш-таблицу
elapsedDebug(function () use ($arrTable, $arrList) {
    array_merge($arrTable, $arrList);
});
// PHP 5.6.40   | Elapsed: 91.127 ms   | Peak RAM: 460.5 MB
// PHP 7.2.34   | Elapsed: 20.619 ms   | Peak RAM:   144 MB
// PHP 8.1.34   | Elapsed: 24.841 ms   | Peak RAM:   156 MB
// PHP 8.2.32   | Elapsed: 20.134 ms   | Peak RAM:   140 MB
// PHP 8.3.30   | Elapsed: 19.948 ms   | Peak RAM:   140 MB
// PHP 8.4.20   | Elapsed: 20.199 ms   | Peak RAM:   140 MB
// PHP 8.5.8RC1 | Elapsed: 19.010 ms   | Peak RAM:   140 MB