<?php

namespace Differ\builder;

use stdClass;

const DIFF_ELEMENT_ADDED = 'added';
const DIFF_ELEMENT_REMOVED = 'deleted';
const DIFF_ELEMENT_CHANGED = 'changed';
const DIFF_ELEMENT_UNCHANGED = 'unchanged';
const DIFF_ELEMENT_NESTED = 'nested';

function convertToArray(object $object)
{
    return json_decode(json_encode($object), true);
}

function buildDiff(stdClass $firstConfig, stdClass $secondConfig)
{
    $getDiff = function (array $firstConfig, array $secondConfig) use (&$getDiff) {
        $configKeys = array_keys(array_merge($firstConfig, $secondConfig));

        return array_map(function ($elementName) use ($firstConfig, $secondConfig, &$getDiff) {
            $node = ['name' => $elementName, 'children' => []];
            $firstProperty = $firstConfig[$elementName] ?? null;
            $secondProperty = $secondConfig[$elementName] ?? null;

            if (!isset($firstProperty)) {
                $node['type'] = DIFF_ELEMENT_ADDED;
                $node['value'] = $secondProperty;
                return $node;
            }

            if (!isset($secondProperty)) {
                $node['type'] = DIFF_ELEMENT_REMOVED;
                $node['value'] = $firstProperty;
                return $node;
            }

            if ($firstProperty === $secondProperty) {
                $node['type'] = DIFF_ELEMENT_UNCHANGED;
                $node['value'] = $firstProperty;
                return $node;
            }

            if ((is_array($firstProperty)) && (is_array($secondProperty))) {
                $node['type'] = DIFF_ELEMENT_NESTED;
                $node['children'] = $getDiff($firstProperty, $secondProperty);
            } else {
                $node['type'] = DIFF_ELEMENT_CHANGED;
                $node['oldValue'] = $firstProperty;
                $node['newValue'] = $secondProperty;
            }
            return $node;
        }, $configKeys);
    };

    return $getDiff(convertToArray($firstConfig), convertToArray($secondConfig));
}
