<?php

namespace Differ\builder;

use stdClass;

const DIFF_ELEMENT_ADDED = 'added';
const DIFF_ELEMENT_REMOVED = 'deleted';
const DIFF_ELEMENT_CHANGED = 'changed';
const DIFF_ELEMENT_UNCHANGED = 'unchanged';
const DIFF_ELEMENT_NESTED = 'nested';

function createNode($name, $type, $valueBefore, $valueAfter = null, array $children = [])
{
    return [
        'name' => $name,
        'type' => $type,
        'children' => $children,
        'valueBefore' => $valueBefore,
        'valueAfter' => $valueAfter
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
                return createNode($propertyName, DIFF_ELEMENT_ADDED, null, $secondConfig->$propertyName);
            }

            if (!property_exists($secondConfig, $propertyName)) {
                return createNode($propertyName, DIFF_ELEMENT_REMOVED, $firstConfig->$propertyName);
            }

            $propertyBefore = $firstConfig->$propertyName;
            $propertyAfter = $secondConfig->$propertyName;

            if ($propertyBefore === $propertyAfter) {
                return createNode($propertyName, DIFF_ELEMENT_UNCHANGED, $propertyBefore, $propertyAfter);
            }

            if ((is_object($propertyBefore)) && (is_object($propertyAfter))) {
                $child = $generateDiff($propertyBefore, $propertyAfter);
                return createNode($propertyName, DIFF_ELEMENT_NESTED, $propertyBefore, null, $child);
            }

            return createNode($propertyName, DIFF_ELEMENT_CHANGED, $propertyBefore, $propertyAfter);
        }, $configKeys);
    };

    return $generateDiff($firstConfig, $secondConfig);
}
