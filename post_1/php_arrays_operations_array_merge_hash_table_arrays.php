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

// $arrTable1 и $arrTable2 - массивы хеш-таблицы (объекты)
$length = 1000000; // 1 000 000
$arrTable1 = ['a' => 1];
for ($i = 0; $i <= $length; $i++) {
    $arrTable1[] = $i;
}
$arrTable2 = ['b' => 2];
for ($i = 0; $i <= $length; $i++) {
    $arrTable2[] = $i;
}

// Теперь интереснее, две хеш-таблицы
elapsedDebug(function () use ($arrTable1, $arrTable2) {
    array_merge($arrTable1, $arrTable2);
});
// PHP 5.6.40   | Elapsed: 93.519 ms   | Peak RAM: 460.5 MB
// PHP 7.2.34   | Elapsed: 21.282 ms   | Peak RAM:   148 MB
// PHP 8.1.34   | Elapsed: 20.922 ms   | Peak RAM:   164 MB
// PHP 8.2.32   | Elapsed: 24.637 ms   | Peak RAM:   164 MB
// PHP 8.3.30   | Elapsed: 25.161 ms   | Peak RAM:   164 MB
// PHP 8.4.20   | Elapsed: 23.728 ms   | Peak RAM:   164 MB
// PHP 8.5.8RC1 | Elapsed: 22.809 ms   | Peak RAM:   164 MB
