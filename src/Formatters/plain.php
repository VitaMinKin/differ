<?php

namespace Differ\Formatters\plain;

function isComplexValue($item)
{
    if (is_array($item)) {
        return true;
    } else {
        return false;
    }
}

function convertToPlain(array $diff)
{
    $converter = function ($diff, $parentName = '') use (&$converter) {
        $result = array_reduce($diff, function ($output, $element) use (&$converter, $parentName) {
            $сompoundParameterName = "$parentName{$element['name']}";

            $condition = $element['diff'];

            if (!empty($condition)) {
                if (isset($condition['value'])) {
                    $value = (isComplexValue($condition['value'])) ? 'complex value' : $condition['value'];
                } else {
                    $oldValue = (isComplexValue($condition['oldValue'])) ? 'complex value' : $condition['oldValue'];
                    $newValue = (isComplexValue($condition['newValue'])) ? 'complex value' : $condition['newValue'];
                }

                if ($condition['itemState'] === 'changed') {
                    $output .= "Property '$сompoundParameterName' was changed. From '$oldValue' to '$newValue'" . "\n";
                } elseif ($condition['itemState'] === 'added') {
                    $output .= "Property '$сompoundParameterName' was added with value: '$value'" . "\n";
                } elseif ($condition['itemState'] === 'deleted') {
                    $output .= "Property '$сompoundParameterName' was removed" . "\n";
                }
            }

            if (!empty($element['children'])) {
                $output .= $converter($element['children'], "{$сompoundParameterName}.");
            }

            return $output;
        }, '');
        return $result;
    };
    return $converter($diff);
}
