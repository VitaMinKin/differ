<?php

namespace Differ\Formatters\pretty;

function getStringValue($item)
{
    if (is_bool($item)) {
        return boolval($item) ? 'true' : 'false';
    }

    return (is_array($item)) ? json_encode($item, JSON_PRETTY_PRINT) : $item;
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

            if (!empty($diff)) {
                if (isset($diff['value'])) {
                    $value = getStringValue($diff['value']);

                    if (is_array($diff['value'])) {
                        $stringArray = explode("\n", $value);
                        $res = array_map(function($item) use ($prefix) {
                            return $prefix['unchanged'] . str_replace('"', "", $item);
                        }, $stringArray);
                        $res[0] = '{';
                        $value = implode("\n", $res);
                    }

                } else {
                    $oldValue = getStringValue($diff['oldValue']);
                    $newValue = getStringValue($diff['newValue']);
                }

                if ($diff['itemState'] === 'changed') {
                    $output .= "{$prefix['added']}{$element['name']}: $newValue\n";
                    $output .= "{$prefix['deleted']}{$element['name']}: $oldValue\n";
                } else {
                    $output .= "{$prefix[$diff['itemState']]}{$element['name']}: $value\n";
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
