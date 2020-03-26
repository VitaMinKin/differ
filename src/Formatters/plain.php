<?php

namespace Differ\Formatters\plain;

function isComplexValue($item) //true or false? Неверное название функции!
{
    if (is_array($item)) {
        return 'complex value';
    } else {
        return $item;
    }
}

function convertToPlain(array $diff)
{
    $converter = function ($diff, $parentName = '') use (&$converter) {
        $result = array_reduce($diff, function ($output, $element) use (&$converter, $parentName) {
            $propertyName = "$parentName{$element['name']}";

            $state = $element['diff'];

            if (!empty($state)) {
                if (isset($state['value'])) {
                    $value = isComplexValue($state['value']);
                } else {
                    $oldValue = isComplexValue($state['oldValue']);
                    $newValue = isComplexValue($state['newValue']);
                }

                if ($state['itemState'] === 'changed') {
                    $output .= "Property '$propertyName' was changed. From '$oldValue' to '$newValue'" . "\n";
                } elseif ($state['itemState'] === 'added') {
                    $output .= "Property '$propertyName' was added with value: '$value'" . "\n";
                } elseif ($state['itemState'] === 'deleted') {
                    $output .= "Property '$propertyName' was removed" . "\n";
                }
            }

            if (!empty($element['children'])) {
                $output .= $converter($element['children'], "{$propertyName}.");
            }

            return $output;
        }, '');
        return $result;
    };
    return $converter($diff);
}
