<?php

namespace Differ\Formatters\plain;

use const Differ\DIFF_ELEMENT_ADDED;
use const Differ\DIFF_ELEMENT_CHANGED;
use const Differ\DIFF_ELEMENT_UNCHANGED;
use const Differ\DIFF_ELEMENT_REMOVED;

function isComplexValue($item)
{
    return is_array($item);
}

function convertToPlain(array $diff)
{
    $converter = function ($diff, $parentName = '') use (&$converter) {

        $result = array_reduce($diff, function ($outputString, $element) use (&$converter, $parentName) {

            ['name' => $elementName, 'diff' => $elementDiff, 'children' => $elementChildren] = $element;
            $сompoundParameterName = ($parentName == '') ? $elementName : "$parentName.$elementName";

            if (!empty($elementDiff)) {
                switch ($elementDiff['itemState']) {
                    case DIFF_ELEMENT_CHANGED:
                        $before = $elementDiff['oldValue'];
                        $after = $elementDiff['newValue'];
                        $oldValue = (isComplexValue($before)) ? 'complex value' : $before;
                        $newValue = (isComplexValue($after)) ? 'complex value' : $after;
                        $outputString .= "Property '$сompoundParameterName' was changed. ";
                        $outputString .= "From '$oldValue' to '$newValue'" . "\n";
                        break;
                    case DIFF_ELEMENT_ADDED:
                        $value = (isComplexValue($elementDiff['value'])) ? 'complex value' : $elementDiff['value'];
                        $outputString .= "Property '$сompoundParameterName' was added with value: '$value'" . "\n";
                        break;
                    case DIFF_ELEMENT_REMOVED:
                        $outputString .= "Property '$сompoundParameterName' was removed" . "\n";
                }
            }

            if (!empty($elementChildren)) {
                $outputString .= $converter($elementChildren, $сompoundParameterName);
            }

            return $outputString;
        }, '');
        return $result;
    };
    return trim($converter($diff));
}
