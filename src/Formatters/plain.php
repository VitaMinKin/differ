<?php

namespace Differ\Formatters\plain;

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
                if ($elementDiff['itemState'] === 'changed') {
                    $oldValue = (isComplexValue($elementDiff['oldValue'])) ? 'complex value' : $elementDiff['oldValue'];
                    $newValue = (isComplexValue($elementDiff['newValue'])) ? 'complex value' : $elementDiff['newValue'];
                    $outputString .= "Property '$сompoundParameterName' was changed. From '$oldValue' to '$newValue'" . "\n";
                } elseif ($elementDiff['itemState'] === 'added') {
                    $value = (isComplexValue($elementDiff['value'])) ? 'complex value' : $elementDiff['value'];
                    $outputString .= "Property '$сompoundParameterName' was added with value: '$value'" . "\n";
                } elseif ($elementDiff['itemState'] === 'deleted') {
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
    return $converter($diff);
}
