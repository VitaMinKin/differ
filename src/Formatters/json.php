<?php

namespace Differ\Formatters\json;

function convertToJson(array $diff)
{
    $converter = function ($diff) use (&$converter) {
        $result = array_reduce($diff, function ($output, $element) use (&$converter) {
            ['name' => $elementName, 'diff' => $elementDiff, 'children' => $elementChildren] = $element;

            if (!empty($elementDiff)) {
                if ($elementDiff['itemState'] === 'changed') {
                    $oldValue = $elementDiff['oldValue'];
                    $newValue = $elementDiff['newValue'];
                    $output[$elementName] = [['oldValue' => $oldValue, 'newValue' => $newValue], 'changed'];
                } elseif ($elementDiff['itemState'] === 'added') {
                    $value = $elementDiff['value'];
                    $output[$elementName] = [$value, 'added'];
                } elseif ($elementDiff['itemState'] === 'deleted') {
                    $value = $elementDiff['value'];
                    $output[$elementName] = [$value, 'removed'];
                } else {
                    $value = $elementDiff['value'];
                    $output[$element['name']] = $value;
                }
            }

            if (!empty($elementChildren)) {
                $output[$element['name']] = $converter($elementChildren);
            }

            return $output;
        }, []);
        return $result;
    };
    return json_encode($converter($diff), JSON_PRETTY_PRINT);
}
