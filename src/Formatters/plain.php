<?php

namespace Differ\Formatters\plain;

use const Differ\Differ\DIFF_ELEMENT_ADDED;
use const Differ\Differ\DIFF_ELEMENT_CHANGED;
use const Differ\Differ\DIFF_ELEMENT_REMOVED;

function isComplexValue($item)
{
    return is_array($item);
}

function convertToPlain(array $diff)
{
    $converter = function ($diff, $parentName = '') use (&$converter) {

        $outputStrings = array_reduce($diff, function ($stringAcc, $element) use (&$converter, $parentName) {
            ['name' => $elementName, 'children' => $elementChildren] = $element;
            $сompoundParameterName = ($parentName == '') ? $elementName : "$parentName.$elementName";

            switch ($element['type']) {
                case DIFF_ELEMENT_CHANGED:
                    $before = $element['oldValue'];
                    $after = $element['newValue'];
                    $oldValue = (isComplexValue($before)) ? 'complex value' : $before;
                    $newValue = (isComplexValue($after)) ? 'complex value' : $after;
                    $stringAcc[] = "Property '$сompoundParameterName' was changed. From '$oldValue' to '$newValue'";
                    break;
                case DIFF_ELEMENT_ADDED:
                    $value = (isComplexValue($element['value'])) ? 'complex value' : $element['value'];
                    $stringAcc[] = "Property '$сompoundParameterName' was added with value: '$value'";
                    break;
                case DIFF_ELEMENT_REMOVED:
                    $stringAcc[] = "Property '$сompoundParameterName' was removed";
            }

            if (!empty($elementChildren)) {
                $stringAcc[] = $converter($elementChildren, $сompoundParameterName);
            }

            return $stringAcc;
        }, []);

        return implode(PHP_EOL, $outputStrings);
    };

    return trim($converter($diff));
}
