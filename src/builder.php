<?php

namespace Differ\builder;

use stdClass;

const DIFF_ELEMENT_ADDED = 'added';
const DIFF_ELEMENT_REMOVED = 'deleted';
const DIFF_ELEMENT_CHANGED = 'changed';
const DIFF_ELEMENT_UNCHANGED = 'unchanged';
const DIFF_ELEMENT_NESTED = 'nested';

function generateDiff($keys, $type, $propertiesBefore, $propertiesAfter, $acc)
{
    if (!empty($keys)) {
        $node = array_pop($keys);

        $nodeData = [
            'name' => $node,
            'type' => $type
        ];

        switch ($type) {
            case DIFF_ELEMENT_ADDED:
            case DIFF_ELEMENT_REMOVED:
            case DIFF_ELEMENT_UNCHANGED:
                $nodeData['value'] = $propertiesBefore[$node];
                break;
            case DIFF_ELEMENT_CHANGED:
                $nodeData['newValue'] = $propertiesAfter[$node];
                $nodeData['oldValue'] = $propertiesBefore[$node];
                break;
            case DIFF_ELEMENT_NESTED:
                $nodeData['children'] = buildDiff($propertiesBefore[$node], $propertiesAfter[$node]);
        }

        $acc[] = $nodeData;

        return generateDiff($keys, $type, $propertiesBefore, $propertiesAfter, $acc);
    }

    return $acc;
}

function buildDiff(stdClass $firstConfig, stdClass $secondConfig)
{
    $propertiesBefore = get_object_vars($firstConfig);
    $keysBefore = array_keys($propertiesBefore);

    $propertiesAfter = get_object_vars($secondConfig);
    $keysAfter = array_keys($propertiesAfter);

    $combinedKeys = array_reverse(
        array_unique(
            array_merge($keysBefore, $keysAfter)
        )
    );

    $addedKeys = array_filter($combinedKeys, fn($item) => !in_array($item, $keysBefore));
    $deletedKeys = array_filter($combinedKeys, fn($item) => !in_array($item, $keysAfter));

    $remainingKeys = array_filter(
        $combinedKeys,
        fn($item) =>
        !(in_array($item, $addedKeys) || in_array($item, $deletedKeys))
    );

    $unchangedKeys = array_filter($remainingKeys, fn($item) => $propertiesBefore[$item] === $propertiesAfter[$item]);

    $changedFlatFieldKeys = array_filter(
        $remainingKeys,
        fn($item) =>
        !(is_object($propertiesBefore[$item]) && is_object($propertiesAfter[$item]))
        && (!in_array($item, $unchangedKeys))
    );

    $nestedFieldKeys = array_filter(
        $remainingKeys,
        fn($item) =>
        is_object($propertiesBefore[$item]) && is_object($propertiesAfter[$item])
    );

    $diffAddedFields = generateDiff($addedKeys, DIFF_ELEMENT_ADDED, $propertiesAfter, null, []);
    $diffDelFields = generateDiff($deletedKeys, DIFF_ELEMENT_REMOVED, $propertiesBefore, null, $diffAddedFields);

    $diffUnchangedFields = generateDiff(
        $unchangedKeys,
        DIFF_ELEMENT_UNCHANGED,
        $propertiesBefore,
        null,
        $diffDelFields
    );

    $diffChangedFlattenFields = generateDiff(
        $changedFlatFieldKeys,
        DIFF_ELEMENT_CHANGED,
        $propertiesBefore,
        $propertiesAfter,
        $diffUnchangedFields
    );

    $diffNestedFields = generateDiff(
        $nestedFieldKeys,
        DIFF_ELEMENT_NESTED,
        $propertiesBefore,
        $propertiesAfter,
        $diffChangedFlattenFields
    );

    return $diffNestedFields;
}
