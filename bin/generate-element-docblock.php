<?php

declare(strict_types=1);

/**
 * Generates the @method static docblock for Element from the W3C
 * elements-of-html element reference.
 *
 * Authored by GitHub Copilot (session 122325ca-bce6-4353-83ce-83f97b778344), 2026-11-10.
 *
 * Refactored by Josh Bruce, 2026-11-10.
 *
 * Usage: php generate-element-docblock.php [output.php] [elements.json]
 *   output.php     Where to write the docblock (default: stdout)
 *
 *   elements.json  Path or URL to the elements reference
 *    (default: https://raw.githubusercontent.com/w3c/elements-of-html/master/elements.json)
 */

require_once __DIR__ . 'functions.php';

$source = $argv[2] ??
    'https://raw.githubusercontent.com/w3c/elements-of-html/master/elements.json';

$entries = getElementEntries($source);

$specsByElement = getSpecsByElement($entries);

$variantCount = count($entries) - count($specsByElement);

// Group by era: current (in the newest spec) first, then deprecated groups
// ordered by how recently they were dropped, with an unspecified tail group
// for entries the source gives no spec data.
$eraOrder = ['2.0', '3.2', '4.01', 'X1.0', 'X1.1', '5', '5.1', '5.2'];
$current = end($eraOrder);

$groups = []; // key: last-appearance index in $eraOrder, or -1 for unspecified
foreach ($specsByElement as $element => $specs) {
    if ($specs === []) {
        $groups[-1][] = $element;
        continue;
    }
    $last = -1;
    foreach ($specs as $spec) {
        $index = array_search($spec, $eraOrder, true);
        if ($index !== false && $index > $last) {
            $last = $index;
        }
    }
    $groups[$last][] = $element;
}

krsort($groups); // newest last-appearance first; -1 (unspecified) lands last

$lines = [];
$lines[] = '/**';
$lines[] = ' * Magic static element factories. Generated from w3c/elements-of-html';
$lines[] = sprintf(
    ' * elements.json (retrieved %s): %d entries, %d unique elements after',
    date('Y-m-d'),
    count($entries),
    count($specsByElement),
);
$lines[] = sprintf(
    ' * collapsing the %d input[type=...] variants into input. Sectioned by',
    $variantCount,
);
$lines[] = ' * era: current elements first, then deprecated groups by when they were';
$lines[] = ' * dropped, each group alphabetical.';
$lines[] = ' *';
$lines[] = ' * Element semantics per the WHATWG HTML living standard:';
$lines[] = ' * https://html.spec.whatwg.org';
$lines[] = ' *';

$firstRun = true;
foreach ($groups as $lastIndex => $elements) {
    sort($elements);
    if ($firstRun === false) {
        $lines[] = ' *';

    }
    $firstRun = false;

    if ($lastIndex === -1) {
        $lines[] = ' * No spec data in source (verify before relying on these):';

    } elseif ($eraOrder[$lastIndex] === $current) {
        $lines[] = sprintf(' * Available in %s (current):', $current);

    } else {
        // The XHTML 1.x line runs parallel to HTML 4.01; an element dropped
        // after X1.0/X1.1 was typically still available in 4.01, so list both.
        $lastAvailable = [$eraOrder[$lastIndex]];
        if (str_starts_with($eraOrder[$lastIndex], 'X1')) {
            array_unshift($lastAvailable, '4.01');
        }

        $lastAvailableIndex = $lastIndex + 1;
        if (isset($eraOrder[$lastAvailableIndex]) === false) {
            fwrite(STDERR, "Could not get last spec available for last index: {$lastIndex}\n");
            exit(1);
        }

        $lines[] = sprintf(
            ' * Deprecated in %s; last available in %s:',
            $eraOrder[$lastAvailableIndex],
            implode(', ', $lastAvailable),
        );
    }
    foreach ($elements as $element) {
        $lines[] = sprintf(
            ' * @method static static %s(string|\Stringable ...$content)',
            $element
        );

    }
}
$lines[] = ' */';

$docblock = implode("\n", $lines) . "\n";

$target = $argv[1] ?? null;
if ($target === null) {
    echo $docblock;
    exit(0);
}

if (file_put_contents(__DIR__ . '/../docblocks/' . $target, $docblock) === false) {
    fwrite(STDERR, "Could not write: {$target}\n");
    exit(1);
}

fwrite(STDOUT, sprintf("Wrote %d lines to %s\n", count($lines), $target));
