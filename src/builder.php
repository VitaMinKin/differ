<?php

namespace Differ\builder;

use stdClass;

const DIFF_ELEMENT_ADDED = 'added';
const DIFF_ELEMENT_REMOVED = 'deleted';
const DIFF_ELEMENT_CHANGED = 'changed';
const DIFF_ELEMENT_UNCHANGED = 'unchanged';
const DIFF_ELEMENT_NESTED = 'nested';

function getProperty(stdClass $config, $property)
{
    return property_exists($config, $property) ? $config->$property : null;
}

function createNode($name, $type, $value, $oldValue = null, array $children = null)
{
    $nodeData = [
        'name' => $name,
        'type' => $type
    ];

    switch ($type) {
        case DIFF_ELEMENT_ADDED:
        case DIFF_ELEMENT_REMOVED:
        case DIFF_ELEMENT_UNCHANGED:
            $nodeData['value'] = $value;
            break;
        case DIFF_ELEMENT_CHANGED:
            $nodeData['oldValue'] = $oldValue;
            $nodeData['newValue'] = $value;
            break;
        case DIFF_ELEMENT_NESTED:
            $nodeData['children'] = $children;
    }

    return $nodeData;
}

function buildDiff(stdClass $firstConfig, stdClass $secondConfig)
{
    $generateDiff = function ($firstConfig, $secondConfig) use (&$generateDiff) {
        $beforeProperties = get_object_vars($firstConfig);
        $afterProperties = get_object_vars($secondConfig);
        $uniteProperties = array_merge($beforeProperties, $afterProperties);
        $configKeys = array_keys($uniteProperties);

        return array_map(function ($propertyName) use ($firstConfig, $secondConfig, $generateDiff) {
            $beforeProperty = getProperty($firstConfig, $propertyName);
            $afterProperty = getProperty($secondConfig, $propertyName);

            if (!isset($beforeProperty)) {
                return createNode($propertyName, DIFF_ELEMENT_ADDED, $afterProperty);
            }

            if (!isset($afterProperty)) {
                return createNode($propertyName, DIFF_ELEMENT_REMOVED, $beforeProperty);
            }

            if ($beforeProperty === $afterProperty) {
                return createNode($propertyName, DIFF_ELEMENT_UNCHANGED, $beforeProperty);
            }

            if ((is_object($beforeProperty)) && (is_object($afterProperty))) {
                return createNode(
                    $propertyName,
                    DIFF_ELEMENT_NESTED,
                    null,
                    null,
                    $generateDiff($beforeProperty, $afterProperty)
                );
            } else {
                return createNode($propertyName, DIFF_ELEMENT_CHANGED, $afterProperty, $beforeProperty);
            }
        }, $configKeys);
    };

    return $generateDiff($firstConfig, $secondConfig);
}
