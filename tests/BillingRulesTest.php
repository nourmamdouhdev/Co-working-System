<?php

declare(strict_types=1);

function billableHours(int $durationMinutes): int
{
    return max(1, (int) ceil($durationMinutes / 60));
}

$cases = [
    [1, 1],
    [60, 1],
    [61, 2],
    [120, 2],
    [0, 1],
];

$failures = 0;

foreach ($cases as [$minutes, $expected]) {
    $actual = billableHours($minutes);
    if ($actual !== $expected) {
        $failures++;
        echo "Failed: {$minutes} -> {$actual} (expected {$expected})\n";
    }
}

if ($failures > 0) {
    echo "Billing rules test failed with {$failures} failures.\n";
    exit(1);
}

echo "Billing rules test passed.\n";
