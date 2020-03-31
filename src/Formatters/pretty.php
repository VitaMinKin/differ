<?php

namespace Differ\Formatters\pretty;

function getStringValue($item)
{
    //return (is_array($item)) ? json_encode($item, JSON_PRETTY_PRINT) : $item;
    if (is_array($item)) {
        return implode("\n", $item);
    } else {
        return $item;
    }
}

function convertToText(array $diff)
{
    $converter = function ($diff, $depth = "") use (&$converter) {
        $prefix = ['unchanged' => '   ', 'added' => ' + ', 'deleted' => ' - '];

        $prefix = array_map(function ($item) use ($depth) {
            return $depth . $item;
        }, $prefix);

        $result = array_reduce($diff, function ($output, $element) use (&$converter, $prefix) {
            $currentState = $element['diff'];
            if (!empty($currentState)) {
                if (isset($currentState['value'])) {
                    $value = getStringValue($currentState['value']);
                } else {
                    $oldValue = getStringValue($currentState['oldValue']);
                    $newValue = getStringValue($currentState['newValue']);
                }

                if ($currentState['itemState'] === 'changed') {
                    $output .= "{$prefix['deleted']}{$element['name']}: $oldValue\n";
                    $output .= "{$prefix['added']}{$element['name']}: $newValue\n";
                } else {
                    $output .= "{$prefix[$currentState['itemState']]}{$element['name']}: $value\n";
                }
            }

            if (!empty($element['children'])) {
                $output .= "{$prefix['unchanged']}{$element['name']}: {\n";
                $output .= $converter($element['children'], $prefix['unchanged']);
                $output .= "{$prefix['unchanged']}}\n";
            }

            return $output;
        }, '');

        return $result;
    };

    $outputString = $converter($diff);
    return "{\n$outputString}\n";
}
