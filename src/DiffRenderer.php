<?php

namespace Differ;

class DiffRenderer
{
    private $format;

    public function __construct($options = ['format' => 'text'])
    {
        $this->format = $options['format'];
    }

    public function render(array $diff)
    {
        switch ($this->format) {
            case 'text':
                return self::convertToText($diff);
            break;
            case 'plain':
                return self::convertToPlain($diff);
            break;
        }
    }

    private static function getStringValue($item)
    {
        if (is_array($item)) {
            return json_encode($item);
        } else {
            return $item;
        }
    }

    private static function convertToText(array $diff)
    {
        $converter = function ($diff, $depth = "") use (&$converter) {
            $prefix = ['unchanged' => '   ', 'added' => ' + ', 'deleted' => ' - '];

            $prefix = array_map(function ($item) use ($depth) {
                return $depth . $item;
            }, $prefix);

            $result = array_reduce($diff, function ($output, $element) use (&$converter, $prefix) {
                $state = $element['diff'];
                if (!empty($state)) {
                    if (isset($state['value'])) {
                        $value = self::getStringValue($state['value']);
                    } else {
                        $oldValue = self::getStringValue($state['oldValue']);
                        $newValue = self::getStringValue($state['newValue']);
                    }

                    if ($state['itemState'] === 'changed') {
                        $output .= "{$prefix['deleted']}{$element['name']}: $oldValue\n";
                        $output .= "{$prefix['added']}{$element['name']}: $newValue\n";
                    } else {
                        $output .= "{$prefix[$state['itemState']]}{$element['name']}: $value\n";
                    }
                }

                if (!empty($element['children'])) {
                    $output .= "{$prefix['unchanged']}{$element['name']}: {\n";
                    $output .= $converter($element['children'], $prefix['unchanged']);
                    $output .= "{$prefix['unchanged']}}\n";
                }

                return $output;
            }, '');

            return $result;
        };

        $outputString = $converter($diff);
        return "{\n$outputString}\n";
    }


    private static function isComplexValue($item)
    {
        if (is_array($item)) {
            return 'complex value';
        } else {
            return $item;
        }
    }

    private static function convertToPlain(array $diff)
    {
        $converter = function ($diff, $parentName = '') use (&$converter) {
            $result = array_reduce ($diff, function ($output, $element) use (&$converter, $parentName) {
                $propertyName = "$parentName{$element['name']}";

                $state = $element['diff'];

                if (!empty($state)) {
                    if (isset($state['value'])) {
                        $value = self::isComplexValue($state['value']);
                    } else {
                        $oldValue = self::isComplexValue($state['oldValue']);
                        $newValue = self::isComplexValue($state['newValue']);
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
}
