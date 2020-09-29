<?php

namespace Differ\Formatters\pretty;

use const Differ\builder\DIFF_ELEMENT_ADDED;
use const Differ\builder\DIFF_ELEMENT_CHANGED;
use const Differ\builder\DIFF_ELEMENT_NESTED;
use const Differ\builder\DIFF_ELEMENT_UNCHANGED;
use const Differ\builder\DIFF_ELEMENT_REMOVED;

function getFormattedString($item, $indents)
{
    if (is_bool($item)) {
        return boolval($item) ? 'true' : 'false';
    }

    if (!is_object($item)) {
        return $item;
    }

    $preResult = json_encode($item, JSON_PRETTY_PRINT);

    $listOfStrings = explode("\n", $preResult);
    $addPadding = array_map(fn ($elem) => $indents . str_replace('"', "", $elem), $listOfStrings);
    $addPadding[0] = '{';

    $result = implode("\n", $addPadding);
    return $result;
}

function createString($prefix, $key, $value)
{
    return $prefix . $key . ': ' . $value;
}

function convertToText(array $diff)
{
    $converter = function ($diff, $indents = "") use (&$converter) {
        $prefix = [DIFF_ELEMENT_UNCHANGED => '    ', DIFF_ELEMENT_ADDED => '  + ', DIFF_ELEMENT_REMOVED => '  - '];

        $depthPrefix = array_map(fn($item) => $indents . $item, $prefix);

        $result = array_reduce($diff, function ($acc, $element) use (&$converter, $depthPrefix) {

            $elementName = $element['name'];
            [
                DIFF_ELEMENT_UNCHANGED => $depthIndent,
                DIFF_ELEMENT_ADDED => $plusSign,
                DIFF_ELEMENT_REMOVED => $minusSign
            ] = $depthPrefix;

            switch ($element['type']) {
                case DIFF_ELEMENT_ADDED:
                case DIFF_ELEMENT_REMOVED:
                case DIFF_ELEMENT_UNCHANGED:
                    $value = getFormattedString($element['value'], $depthIndent);
                    $acc[] = createString($depthPrefix[$element['type']], $elementName, $value);
                    break;
                case DIFF_ELEMENT_CHANGED:
                    $oldValue = getFormattedString($element['oldValue'], $depthIndent);
                    $newValue = getFormattedString($element['newValue'], $depthIndent);
                    $acc[] = createString($plusSign, $elementName, $newValue);
                    $acc[] = createString($minusSign, $elementName, $oldValue);
                    break;
                case DIFF_ELEMENT_NESTED:
                    $acc[] = createString($depthIndent, $elementName, '{');
                    $acc[] = $converter($element['children'], $depthIndent);
                    $acc[] = $depthIndent . '}';
                    break;
                default:
                    throw new \Exception("Unknown type {$element['type']} in diff!");
            }

            return $acc;
        }, []);

        return implode("\n", $result);
    };

    $output = $converter($diff);
    return "{\n$output\n}";
}
