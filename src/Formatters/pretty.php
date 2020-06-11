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

        $result = array_reduce($diff, function ($output, $element) use (&$converter, $prefix) {

            $diff = $element['diff'];
            $depth = $prefix['unchanged'];

            if (!empty($diff)) {
                if (isset($diff['value'])) {
                    $value = getFormattedStringValue($diff['value'], $depth);
                } else {
                    $oldValue = getFormattedStringValue($diff['oldValue'], $depth);
                    $newValue = getFormattedStringValue($diff['newValue'], $depth);
                }

                if ($diff['itemState'] === 'changed') {
                    $output .= "{$prefix['added']}{$element['name']}: $newValue\n";
                    $output .= "{$prefix['deleted']}{$element['name']}: $oldValue\n";
                } else {
                    $output .= "{$prefix[$diff['itemState']]}{$element['name']}: $value\n";
                }
            }

            if (!empty($element['children'])) {
                $output .= "{$depth}{$element['name']}: {\n";
                $output .= $converter($element['children'], $depth);
                $output .= "{$depth}}\n";
            }

            return $output;
        }, '');

        return $result;
    };

    $outputString = $converter($diff);
    return "{\n$outputString}\n";
}
