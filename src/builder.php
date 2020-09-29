<?php

namespace Differ\builder;

use stdClass;

const DIFF_ELEMENT_ADDED = 'added';
const DIFF_ELEMENT_REMOVED = 'deleted';
const DIFF_ELEMENT_CHANGED = 'changed';
const DIFF_ELEMENT_UNCHANGED = 'unchanged';
const DIFF_ELEMENT_NESTED = 'nested';

function createNode($name, $type, $value, $oldValue = null, array $children = null)
{
    if (isset($children)) {
        return [
            'name' => $name,
            'type' => $type,
            'children' => $children
        ];
    }

    if (isset($oldValue)) {
        return [
            'name' => $name,
            'type' => $type,
            'oldValue' => $oldValue,
            'newValue' => $value
        ];
    }

    return [
        'name' => $name,
        'type' => $type,
        'value' => $value
    ];
}

function buildDiff(stdClass $firstConfig, stdClass $secondConfig)
{
    $generateDiff = function ($firstConfig, $secondConfig) use (&$generateDiff) {
        $beforeProperties = get_object_vars($firstConfig);
        $afterProperties = get_object_vars($secondConfig);
        $uniteProperties = array_merge($beforeProperties, $afterProperties);
        $configKeys = array_keys($uniteProperties);

        return array_map(function ($propertyName) use ($firstConfig, $secondConfig, $generateDiff) {
            if (!property_exists($firstConfig, $propertyName)) {
                return createNode($propertyName, DIFF_ELEMENT_ADDED, $secondConfig->$propertyName);
            }

            if (!property_exists($secondConfig, $propertyName)) {
                return createNode($propertyName, DIFF_ELEMENT_REMOVED, $firstConfig->$propertyName);
            }

            $beforeProperty = $firstConfig->$propertyName;
            $afterProperty = $secondConfig->$propertyName;

            if ($beforeProperty === $afterProperty) {
                return createNode($propertyName, DIFF_ELEMENT_UNCHANGED, $beforeProperty);
            }

            if ((is_object($beforeProperty)) && (is_object($afterProperty))) {
                $child = $generateDiff($beforeProperty, $afterProperty);
                return createNode($propertyName, DIFF_ELEMENT_NESTED, null, null, $child);
            }

            return createNode($propertyName, DIFF_ELEMENT_CHANGED, $afterProperty, $beforeProperty);
        }, $configKeys);
    };

    return $generateDiff($firstConfig, $secondConfig);
}
