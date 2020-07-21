<?php

namespace Differ\Formatters\json;

use const Differ\DIFF_ELEMENT_ADDED;
use const Differ\DIFF_ELEMENT_CHANGED;
use const Differ\DIFF_ELEMENT_UNCHANGED;
use const Differ\DIFF_ELEMENT_REMOVED;

function convertToJson(array $diff)
{
    $converter = function ($diff) use (&$converter) {
        $result = array_reduce($diff, function ($output, $element) use (&$converter) {
            ['name' => $elementName, 'children' => $elementChildren] = $element;

            if (isset($element['itemState'])) {

                if ($element['itemState'] === DIFF_ELEMENT_CHANGED) {
                    $oldValue = $element['oldValue'];
                    $newValue = $element['newValue'];
                } else {
                    $value = $element['value'];
                }

                switch ($element['itemState']) {
                    case DIFF_ELEMENT_CHANGED:
                        $output[$elementName] = [
                            ['oldValue' => $oldValue, 'newValue' => $newValue],
                            DIFF_ELEMENT_CHANGED
                        ];
                        break;
                    case DIFF_ELEMENT_ADDED:
                            $output[$elementName] = [$value, DIFF_ELEMENT_ADDED];
                        break;
                    case DIFF_ELEMENT_REMOVED:
                        $output[$elementName] = [$value, 'removed'];
                        break;
                    default:
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
