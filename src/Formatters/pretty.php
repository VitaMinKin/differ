<?php

namespace Differ\Formatters\pretty;

use const Differ\DIFF_ELEMENT_ADDED;
use const Differ\DIFF_ELEMENT_CHANGED;
use const Differ\DIFF_ELEMENT_UNCHANGED;
use const Differ\DIFF_ELEMENT_REMOVED;

function getFormattedStringValue($item, $depth)
{
    if (is_bool($item)) {
        return boolval($item) ? 'true' : 'false';
    }

    if (is_array($item)) {
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

        $prefix = array_map(function ($item) use ($depth) {
            return $depth . $item;
        }, $prefix);

        $result = array_reduce($diff, function ($outputString, $element) use (&$converter, $prefix) {

            ['name' => $elementName, 'diff' => $elementDiff, 'children' => $elementChildren] = $element;
            [
                DIFF_ELEMENT_UNCHANGED => $depth,
                DIFF_ELEMENT_ADDED => $added,
                DIFF_ELEMENT_REMOVED => $deleted
            ] = $prefix;

            if (!empty($elementDiff)) {
                if ($elementDiff['itemState'] === DIFF_ELEMENT_CHANGED) {
                    $oldValue = getFormattedStringValue($elementDiff['oldValue'], $depth);
                    $newValue = getFormattedStringValue($elementDiff['newValue'], $depth);
                    $outputString .= "{$added}{$elementName}: $newValue\n";
                    $outputString .= "{$deleted}{$elementName}: $oldValue\n";
                } else {
                    $value = getFormattedStringValue($elementDiff['value'], $depth);
                    $outputString .= "{$prefix[$elementDiff['itemState']]}{$elementName}: $value\n";
                }
            }

            if (!empty($elementChildren)) {
                $outputString .= "{$depth}{$elementName}: {\n";
                $outputString .= $converter($elementChildren, $depth);
                $outputString .= "$depth}\n";
            }

            return $outputString;
        }, '');

        return $result;
    };

    $outputString = $converter($diff);
    return "{\n$outputString}";
}
