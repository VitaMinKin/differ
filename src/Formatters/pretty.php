<?php

namespace Differ\Formatters\pretty;

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
        $prefix = ['unchanged' => '    ', 'added' => '  + ', 'deleted' => '  - '];

        $prefix = array_map(function ($item) use ($depth) {
            return $depth . $item;
        }, $prefix);

        $result = array_reduce($diff, function ($outputString, $element) use (&$converter, $prefix) {

            ['name' => $elementName, 'diff' => $elementDiff, 'children' => $elementChildren] = $element;
            ['unchanged' => $depth, 'added' => $added, 'deleted' => $deleted] = $prefix;

            if (!empty($elementDiff)) {
                if ($elementDiff['itemState'] === 'changed') {
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
    return "{\n$outputString}\n";
}
