<?php

namespace Differ\Formatters\pretty;

use const Differ\builder\DIFF_ELEMENT_ADDED;
use const Differ\builder\DIFF_ELEMENT_CHANGED;
use const Differ\builder\DIFF_ELEMENT_NESTED;
use const Differ\builder\DIFF_ELEMENT_UNCHANGED;
use const Differ\builder\DIFF_ELEMENT_REMOVED;

function getFormattedString($item, $depth)
{
    if (is_bool($item)) {
        return boolval($item) ? 'true' : 'false';
    }

    if (is_object($item)) {
        $preResult = json_encode($item, JSON_PRETTY_PRINT);

        $listOfStrings = explode("\n", $preResult);
        $addPadding = array_map(function ($elem) use ($depth) {
            return $depth . str_replace('"', "", $elem);
        }, $listOfStrings);
        $addPadding[0] = '{';

        $result = implode("\n", $addPadding);
        return $result;
    }

    return $item;
}

function convertToText(array $diff)
{
    $converter = function ($diff, $depth = "") use (&$converter) {
        $prefix = [DIFF_ELEMENT_UNCHANGED => '    ', DIFF_ELEMENT_ADDED => '  + ', DIFF_ELEMENT_REMOVED => '  - '];

        $depthPrefix = array_map(fn($item) => $depth . $item, $prefix);

        $result = array_reduce($diff, function ($acc, $element) use (&$converter, $depthPrefix) {

            ['name' => $elementName] = $element;
            [
                DIFF_ELEMENT_UNCHANGED => $depth,
                DIFF_ELEMENT_ADDED => $added,
                DIFF_ELEMENT_REMOVED => $deleted
            ] = $depthPrefix;

            switch ($element['type']) {
                case DIFF_ELEMENT_ADDED:
                case DIFF_ELEMENT_REMOVED:
                case DIFF_ELEMENT_UNCHANGED:
                    $value = getFormattedString($element['value'], $depth);
                    $acc[] = "{$depthPrefix[$element['type']]}{$elementName}: $value";
                    break;
                case DIFF_ELEMENT_CHANGED:
                    $oldValue = getFormattedString($element['oldValue'], $depth);
                    $newValue = getFormattedString($element['newValue'], $depth);
                    $acc[] = "{$added}{$elementName}: $newValue";
                    $acc[] = "{$deleted}{$elementName}: $oldValue";
                    break;
                case DIFF_ELEMENT_NESTED:
                    $acc[] = "{$depth}{$elementName}: {";
                    $acc[] = $converter($element['children'], $depth);
                    $acc[] = "$depth}";
                    break;
            }

            return $acc;
        }, []);

        return implode("\n", $result);
    };

    $output = $converter($diff);
    return "{\n$output\n}";
}
