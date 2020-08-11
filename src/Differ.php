<?php

namespace Differ\Differ;

use stdClass;

use function Differ\loader\getExtension;
use function Differ\loader\readFromFile;
use function Differ\parsers\parseConfig;

const DIFF_ELEMENT_ADDED = 'added';
const DIFF_ELEMENT_REMOVED = 'deleted';
const DIFF_ELEMENT_CHANGED = 'changed';
const DIFF_ELEMENT_UNCHANGED = 'unchanged';
const DIFF_ELEMENT_NESTED = 'nested';

function buildDiff(array $firstConfig, array $secondConfig)
{
    $getDiff = function ($firstConfig, $secondConfig) use (&$getDiff) {
        $configKeys = array_keys(array_merge($firstConfig, $secondConfig));

        return array_map(function ($elementName) use ($firstConfig, $secondConfig, &$getDiff) {
            $node = ['name' => $elementName, 'children' => []];
            $comparedParameter1 = isset($firstConfig[$elementName]) ? $firstConfig[$elementName] : null;
            $comparedParameter2 = isset($secondConfig[$elementName]) ? $secondConfig[$elementName] : null;

            if (!isset($comparedParameter1)) {
                $node['type'] = DIFF_ELEMENT_ADDED;
                $node['value'] = $comparedParameter2;
                return $node;
            }

            if (!isset($comparedParameter2)) {
                $node['type'] = DIFF_ELEMENT_REMOVED;
                $node['value'] = $comparedParameter1;
                return $node;
            }

            if ($comparedParameter1 === $comparedParameter2) {
                $node['type'] = DIFF_ELEMENT_UNCHANGED;
                $node['value'] = $comparedParameter1;
                return $node;
            }

            if ((is_array($comparedParameter1)) && (is_array($comparedParameter2))) {
                $node['type'] = DIFF_ELEMENT_NESTED;
                $node['children'] = $getDiff($comparedParameter1, $comparedParameter2);
            } else {
                $node['type'] = DIFF_ELEMENT_CHANGED;
                $node['oldValue'] = $comparedParameter1;
                $node['newValue'] = $comparedParameter2;
            }
            return $node;
        }, $configKeys);
    };

    return $getDiff($firstConfig, $secondConfig);
}

function genDiff($fileLink1, $fileLink2, $outputFormat = 'text')
{
    $firstConfigContent = readFromFile($fileLink1);
    $secondConfigContent = readFromFile($fileLink2);

    $extension1 = getExtension($fileLink1);
    $extension2 = getExtension($fileLink2);

    $firstConfig = parseConfig($firstConfigContent, $extension1);
    $secondConfig = parseConfig($secondConfigContent, $extension2);

    $diff = buildDiff($firstConfig, $secondConfig);

    $difference = \Differ\renderer\render($diff, $outputFormat);
    return $difference;
}
