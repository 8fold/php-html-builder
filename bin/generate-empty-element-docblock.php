<?php

declare(strict_types=1);

/**
 * Generates the @method static docblock for EmptyElement from the WHATWG HTML
 * living standard one-page spec: an element is void when its definition's
 * "Tag omission in text/html" entry reads "No end tag." Full pipeline in one
 * script (derive + emit); derive-void-elements.php remains as the standalone
 * list verifier.
 *
 * Authored by GitHub Copilot (session 122325ca-bce6-4353-83ce-83f97b778344), 2026-11-10.
 *
 * Usage: php generate-empty-element-docblock.php [output.php] [spec.html]
 *   output.php  Where to write the docblock (default: stdout)
 *   spec.html   Path or URL to the one-page spec
 *               (default: https://html.spec.whatwg.org/)
 */

$source = $argv[2] ?? 'https://html.spec.whatwg.org/';

$html = file_get_contents($source);
if ($html === false) {
    fwrite(STDERR, "Could not read: {$source}\n");
    exit(1);
}

// Spec markup: <dd>No <a href="#syntax-end-tag"
// id="the-img-element:syntax-end-tag">end tag</a>.</dd>
$pattern = '/>No <a href="#syntax-end-tag" id="the-([a-z0-9]+)-element:/';
if (preg_match_all($pattern, $html, $matches) === false || $matches[1] === []) {
    fwrite(STDERR, "No void elements found; the spec markup may have changed.\n");
    exit(1);
}

$names = array_values(array_unique($matches[1]));
sort($names);

$lines = [];
$lines[] = '/**';
$lines[] = ' * Magic static void-element factories. Generated from the WHATWG HTML';
$lines[] = sprintf(
    ' * living standard (https://html.spec.whatwg.org/, retrieved %s):',
    date('Y-m-d'),
);
$lines[] = sprintf(
    ' * %d void elements, derived from each element\'s "Tag omission in',
    count($names),
);
$lines[] = ' * text/html: No end tag." entry. Alphabetical.';
$lines[] = ' *';
foreach ($names as $name) {
    $lines[] = sprintf(' * @method static static %s(string ...$properties)', $name);
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
