<?php

namespace Differ\Formatters\json;

use function Differ\Formatters\pretty\getStringValue;

function convertToJson(array $diff)
{
    $converter = function ($diff) use (&$converter) {
        $result = array_reduce($diff, function ($output, $element) use (&$converter) {
            $state = $element['diff'];

            if (!empty($state)) {
                if (isset($state['value'])) {
                    $value = getStringValue($state['value']);
                } else {
                    $oldValue = getStringValue($state['oldValue']);
                    $newValue = getStringValue($state['newValue']);
                }

                if ($state['itemState'] === 'changed') {
                    $output[$element['name']] = [['oldValue' => $oldValue, 'newValue' => $newValue], 'changed'];
                } elseif ($state['itemState'] === 'added') {
                    $output[$element['name']] = [$value, 'added'];
                } elseif ($state['itemState'] === 'deleted') {
                    $output[$element['name']] = [$value, 'removed'];
                } else {
                    $output[$element['name']] = $value;
                }
            }

            if (!empty($element['children'])) {
                $output[$element['name']] = $converter($element['children']);
            }

            return $output;
        }, []);
        return $result;
    };
    return json_encode($converter($diff), JSON_PRETTY_PRINT);
}
