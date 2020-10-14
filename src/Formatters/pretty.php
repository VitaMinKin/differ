<?php

namespace Differ\Formatters\pretty;

use const Differ\builder\DIFF_ELEMENT_ADDED;
use const Differ\builder\DIFF_ELEMENT_CHANGED;
use const Differ\builder\DIFF_ELEMENT_NESTED;
use const Differ\builder\DIFF_ELEMENT_UNCHANGED;
use const Differ\builder\DIFF_ELEMENT_REMOVED;

function removeQuotes($string)
{
    return str_replace('"', "", $string);
}

function createString($prefix, $property, $value)
{
    return $prefix . $property . ': ' . $value;
}

function calculateIndents(int $depth, $indent = null)
{
    if ($depth > 0) {
        $newIndent = "    " . $indent;
        return calculateIndents($depth - 1, $newIndent);
    }

    return $indent;
}

function getPrefix($type, int $depth)
{
    $prefixes = [
        DIFF_ELEMENT_UNCHANGED => '    ',
        DIFF_ELEMENT_ADDED => '  + ',
        DIFF_ELEMENT_REMOVED => '  - '
    ];

    $indent = calculateIndents($depth);
    $depthPrefixes = array_map(fn($prefix) => $indent . $prefix, $prefixes);

    return $depthPrefixes[$type];
}

function convertToPrettyString($value, int $depth)
{
    if (is_bool($value)) {
        return boolval($value) ? 'true' : 'false';
    }

    if (!is_object($value)) {
        return $value;
    }

    $indent = calculateIndents($depth + 1);
    $json = json_encode($value, JSON_PRETTY_PRINT);
    $strings = explode("\n", $json);
    $formattedStrings = array_map(fn ($string) => $indent . removeQuotes($string), $strings);
    $formattedStrings[0] = '{';

    return implode("\n", $formattedStrings);
}

function makeString(int $depth, $nodeType, $nodeName, $nodeValue)
{
    $stringValue = convertToPrettyString($nodeValue, $depth);
    $prefix = getPrefix($nodeType, $depth);
    return createString($prefix, $nodeName, $stringValue);
}

function convertToPretty(array $diff)
{
    $converter = function ($diff, $depth = 0) use (&$converter) {

        $prettyStrings = array_map(function ($property) use (&$converter, $depth) {
            [
                'name' => $nodeName,
                'type' => $nodeType,
                'children' => $children,
                'valueBefore' => $valueBefore,
                'valueAfter' => $valueAfter
            ] = $property;

            switch ($nodeType) {
                case DIFF_ELEMENT_ADDED:
                    return makeString($depth, $nodeType, $nodeName, $valueAfter);
                case DIFF_ELEMENT_REMOVED:
                case DIFF_ELEMENT_UNCHANGED:
                    return makeString($depth, $nodeType, $nodeName, $valueBefore);
                case DIFF_ELEMENT_CHANGED:
                    $strings[] = makeString($depth, DIFF_ELEMENT_ADDED, $nodeName, $valueAfter);
                    $strings[] = makeString($depth, DIFF_ELEMENT_REMOVED, $nodeName, $valueBefore);
                    return implode("\n", $strings);
                case DIFF_ELEMENT_NESTED:
                    $strings[] = makeString($depth, DIFF_ELEMENT_UNCHANGED, $nodeName, '{');
                    $strings[] = $converter($children, $depth + 1);
                    $strings[] = calculateIndents($depth + 1) . '}';
                    return implode("\n", $strings);
                default:
                    throw new \Exception("Unknown type {$nodeType} in diff!");
            }
        }, $diff);

        return implode("\n", $prettyStrings);
    };

    return "{\n" . $converter($diff) . "\n}";
}
