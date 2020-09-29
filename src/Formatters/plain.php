<?php

namespace Differ\Formatters\plain;

use const Differ\builder\DIFF_ELEMENT_ADDED;
use const Differ\builder\DIFF_ELEMENT_CHANGED;
use const Differ\builder\DIFF_ELEMENT_NESTED;
use const Differ\builder\DIFF_ELEMENT_REMOVED;
use const Differ\builder\DIFF_ELEMENT_UNCHANGED;

function isComplexValue($item)
{
    return is_object($item);
}

function getValue($value)
{
    return (isComplexValue($value)) ? 'complex value' : $value;
}

function convertToPlain(array $diff)
{
    $converter = function ($diff, $parentName = '') use (&$converter) {

        $outputStrings = array_reduce($diff, function ($stringAcc, $element) use (&$converter, $parentName) {
            ['name' => $elementName] = $element;
            $сompoundParameterName = ($parentName == '') ? $elementName : "$parentName.$elementName";

            switch ($element['type']) {
                case DIFF_ELEMENT_CHANGED:
                    $before = $element['oldValue'];
                    $after = $element['newValue'];
                    $oldValue = getValue($before);
                    $newValue = getValue($after);
                    $stringAcc[] = "Property '$сompoundParameterName' was changed. From '$oldValue' to '$newValue'";
                    break;
                case DIFF_ELEMENT_ADDED:
                    $value = getValue($element['value']);
                    $stringAcc[] = "Property '$сompoundParameterName' was added with value: '$value'";
                    break;
                case DIFF_ELEMENT_REMOVED:
                    $stringAcc[] = "Property '$сompoundParameterName' was removed";
                    break;
                case DIFF_ELEMENT_NESTED:
                    $stringAcc[] = $converter($element['children'], $сompoundParameterName);
                    break;
                case DIFF_ELEMENT_UNCHANGED:
                    break;
                default:
                    throw new \Exception("Unknown type {$element['type']} in diff!");
            }

            return $stringAcc;
        }, []);

        return implode("\n", $outputStrings);
    };

    return trim($converter($diff));
}
