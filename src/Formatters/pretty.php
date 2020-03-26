<?php

namespace Differ\Formatters\pretty;

function getStringValue($item)
{
    return (is_array($item)) ? json_encode($item) : $item;
}

function convertToText(array $diff)
{
    $converter = function ($diff, $depth = "") use (&$converter) {
        $prefix = ['unchanged' => '   ', 'added' => ' + ', 'deleted' => ' - '];

        $prefix = array_map(function ($item) use ($depth) {
            return $depth . $item;
        }, $prefix);

        $result = array_reduce($diff, function ($output, $element) use (&$converter, $prefix) {
            $state = $element['diff'];
            if (!empty($state)) {
                if (isset($state['value'])) {
                    $value = getStringValue($state['value']);
                } else {
                    $oldValue = getStringValue($state['oldValue']);
                    $newValue = getStringValue($state['newValue']);
                }

                if ($state['itemState'] === 'changed') {
                    $output .= "{$prefix['deleted']}{$element['name']}: $oldValue\n";
                    $output .= "{$prefix['added']}{$element['name']}: $newValue\n";
                } else {
                    $output .= "{$prefix[$state['itemState']]}{$element['name']}: $value\n";
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
