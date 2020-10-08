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

function getPlainValue($value)
{
    return (isComplexValue($value)) ? 'complex value' : $value;
}

function makeString(array $parentNames, $nodeType, $valueBefore, $valueAfter)
{
    $сompoundParameterName = implode('.', $parentNames);

    switch ($nodeType) {
        case DIFF_ELEMENT_CHANGED:
            $oldValue = getPlainValue($valueBefore);
            $newValue = getPlainValue($valueAfter);
            return "Property '$сompoundParameterName' was changed. From '$oldValue' to '$newValue'";
        case DIFF_ELEMENT_ADDED:
            $value = getPlainValue($valueAfter);
            return "Property '$сompoundParameterName' was added with value: '$value'";
        case DIFF_ELEMENT_REMOVED:
            return "Property '$сompoundParameterName' was removed";
        case DIFF_ELEMENT_UNCHANGED:
            return "";
        default:
            throw new \Exception("Unknown type {$nodeType} in diff!");
    }
}

function convertToPlain(array $diff)
{
    $converter = function ($diff, array $parentNames = []) use (&$converter) {

        $plainStrings = array_reduce($diff, function ($strings, $property) use (&$converter, $parentNames) {
            [
                'name' => $nodeName,
                'type' => $nodeType,
                'children' => $children,
                'valueBefore' => $valueBefore,
                'valueAfter' => $valueAfter
            ] = $property;

            $parentNames[] = $nodeName;

            if ($nodeType == DIFF_ELEMENT_NESTED) {
                $strings[] = $converter($children, $parentNames);
                return $strings;
            }

            return [...$strings, makeString($parentNames, $nodeType, $valueBefore, $valueAfter)];
        }, []);

        $withoutEmptyStrings = array_filter($plainStrings, fn($string) => $string !== "");
        return implode("\n", $withoutEmptyStrings);
    };

    return $converter($diff);
}
