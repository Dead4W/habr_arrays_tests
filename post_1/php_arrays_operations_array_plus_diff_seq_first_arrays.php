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

// Первым - последовательный массив (array_plus: $arr1 + $arr2)
elapsedDebug(function () use ($arrList, $arrTable) {
    $arrList + $arrTable;
});
// PHP 5.6.40   | Elapsed: 48.188 ms   | Peak RAM: 368.5 MB
// PHP 7.2.34   | Elapsed: 23.103 ms   | Peak RAM: 140.01 MB
// PHP 8.1.34   | Elapsed: 23.309 ms   | Peak RAM: 148.01 MB
// PHP 8.2.32   | Elapsed: 17.018 ms   | Peak RAM: 116.01 MB
// PHP 8.3.30   | Elapsed: 16.914 ms   | Peak RAM: 116.01 MB
// PHP 8.4.20   | Elapsed: 16.535 ms   | Peak RAM: 116.01 MB
// PHP 8.5.8RC1 | Elapsed: 15.031 ms   | Peak RAM: 116.01 MB