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

function calculateIndents(int $depth, $indent = "")
{
    if ($depth > 0) {
        $newIndent = "    " . $indent;
        return calculateIndents($depth - 1, $newIndent);
    }

    return $indent;
}

function getPrefix($type, int $depth)
{
    $prefix = [
        DIFF_ELEMENT_UNCHANGED => '    ',
        DIFF_ELEMENT_ADDED => '  + ',
        DIFF_ELEMENT_REMOVED => '  - '
    ];

    $indent = calculateIndents($depth);
    $depthPrefix = array_map(fn($item) => $indent . $item, $prefix);

    return $depthPrefix[$type];
}

function convertToPrettyString($item, int $depth)
{
    if (is_bool($item)) {
        return boolval($item) ? 'true' : 'false';
    }

    if (!is_object($item)) {
        return $item;
    }

    $indent = calculateIndents($depth + 1);
    $json = json_encode($item, JSON_PRETTY_PRINT);
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

        $prettyStrings = array_reduce($diff, function ($strings, $property) use (&$converter, $depth) {
            [
                'name' => $nodeName,
                'type' => $nodeType,
                'children' => $children,
                'valueBefore' => $valueBefore,
                'valueAfter' => $valueAfter
            ] = $property;

            switch ($nodeType) {
                case DIFF_ELEMENT_ADDED:
                    $strings[] = makeString($depth, $nodeType, $nodeName, $valueAfter);
                    break;
                case DIFF_ELEMENT_REMOVED:
                case DIFF_ELEMENT_UNCHANGED:
                    $strings[] = makeString($depth, $nodeType, $nodeName, $valueBefore);
                    break;
                case DIFF_ELEMENT_CHANGED:
                    $strings[] = makeString($depth, DIFF_ELEMENT_ADDED, $nodeName, $valueAfter);
                    $strings[] = makeString($depth, DIFF_ELEMENT_REMOVED, $nodeName, $valueBefore);
                    break;
                case DIFF_ELEMENT_NESTED:
                    $strings[] = makeString($depth, DIFF_ELEMENT_UNCHANGED, $nodeName, '{');
                    $strings[] = $converter($children, $depth + 1);
                    $strings[] = calculateIndents($depth + 1) . '}';
                    break;
                default:
                    throw new \Exception("Unknown type {$nodeType} in diff!");
            }

            return $strings;
        }, []);

        return implode("\n", $prettyStrings);
    };

    return "{\n" . $converter($diff) . "\n}";
}
