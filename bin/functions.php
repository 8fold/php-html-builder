<?php

declare(strict_types=1);

use stdClass;

/**
 * @return list<stdClass>
 */
function getElementEntries(string $source): array
{
    $json = file_get_contents($source);
    if ($json === false) {
        fwrite(STDERR, "Could not read: {$source}\n");
        exit(1);
    }

    if (json_validate($json) === false) {
        fwrite(STDERR, "Invalid JSON in: {$source}\n");
        exit(1);
    }

    $entries = json_decode($json, false);
    if (is_array($entries) === false) {
        fwrite(STDERR, "JSON not array of element objects: {$source}\n");
        exit(1);
    }

    $e = [];
    foreach ($entries as $entry) {
        if (
            is_object($entry) === false
            or is_a($entry, stdClass::class) === false
            or property_exists($entry, 'element') === false
            or is_string($entry->element) === false
            or property_exists($entry, 'specs') === false
        ) {
            fwrite(STDERR, "JSON not array of valid objects: {$source}\n");
            exit(1);
        }
        $e[] = $entry;
    }

    return $e;
}

/**
 * @param  list<stdClass>  $entries
 * @return array<string, list<string>>
 */
function getSpecsByElement(array $entries): array
{
    // The reference lists input once per type attribute (input[type="hidden"],
    // etc.); the builder dispatches on tag name only, so collapse to `input`.
    $specsByName = [];
    foreach ($entries as $entry) {
        $element = $entry->element;
        if (is_string($element) and str_contains($element, '[')) {
            // TODO: convert to pipe when PHP 8.5 required
            // $element = explode('[', $element) |> 'array_first';
            $element = explode('[', $element);
            $element = $element[0];
        }

        if (is_string($element) === false) {
            $json = json_encode($entry);
            fwrite(STDERR, "Element not a string: {$json}\n");
            exit(1);
        }

        if (array_key_exists($element, $specsByName) === false) {
            if (is_array($entry->specs) === false) {
                fwrite(STDERR, "'specs' not array: {$element}\n");
                exit(1);
            }
            // TODO: convert to pipe when PHP 8.5 required |> 'array_values';
            $specsByName[$element] = array_values(
                array_filter($entry->specs, 'is_string')
            );
        }
    }
    return $specsByName;
}
