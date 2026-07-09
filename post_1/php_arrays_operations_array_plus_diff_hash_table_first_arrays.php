<?php
ini_set('memory_limit', '2048M');

function elapsedDebug(\Closure $fn) {
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

// Первым хеш-таблицу (array_plus: $arr1 + $arr2)
elapsedDebug(function () use ($arrTable, $arrList) {
    $arrTable + $arrList;
});
// PHP 5.6.40   | Elapsed: 48.085 ms   | Peak RAM: 368.5 MB
// PHP 7.2.34   | Elapsed: 12.366 ms   | Peak RAM:   108 MB
// PHP 8.1.34   | Elapsed: 14.068 ms   | Peak RAM:   116 MB
// PHP 8.2.32   | Elapsed: 12.131 ms   | Peak RAM:   100 MB
// PHP 8.3.30   | Elapsed: 12.516 ms   | Peak RAM:   100 MB
// PHP 8.4.20   | Elapsed: 11.345 ms   | Peak RAM:   100 MB
// PHP 8.5.8RC1 | Elapsed: 12.440 ms   | Peak RAM:   100 MB