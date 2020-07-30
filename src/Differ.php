<?php

namespace Differ;

use function Differ\loader\readFromFile;
use function Differ\parsers\parseConfig;

const DIFF_ELEMENT_ADDED = 'added';
const DIFF_ELEMENT_REMOVED = 'deleted';
const DIFF_ELEMENT_CHANGED = 'changed';
const DIFF_ELEMENT_UNCHANGED = 'unchanged';

function buildDiff(array $firstConfig, array $secondConfig)
{
    $getDiff = function ($firstConfig, $secondConfig) use (&$getDiff) {
        $configKeys = array_keys(array_merge($firstConfig, $secondConfig));

        return array_map(function ($elementName) use ($firstConfig, $secondConfig, &$getDiff) {
            $resultParameter = ['name' => $elementName, 'children' => []];
            $comparedParameter1 = isset($firstConfig[$elementName]) ? $firstConfig[$elementName] : null;
            $comparedParameter2 = isset($secondConfig[$elementName]) ? $secondConfig[$elementName] : null;

            if (!isset($comparedParameter1)) {
                $resultParameter['itemState'] = DIFF_ELEMENT_ADDED;
                $resultParameter['value'] = $comparedParameter2;
                return $resultParameter;
            }

            if (!isset($comparedParameter2)) {
                $resultParameter['itemState'] = DIFF_ELEMENT_REMOVED;
                $resultParameter['value'] = $comparedParameter1;
                return $resultParameter;
            }

            if ($comparedParameter1 === $comparedParameter2) {
                 $resultParameter['itemState'] = DIFF_ELEMENT_UNCHANGED;
                $resultParameter['value'] = $comparedParameter1;
                return $resultParameter;
            }

            if ((is_array($comparedParameter1)) && (is_array($comparedParameter2))) {
                $resultParameter['children'] = $getDiff($comparedParameter1, $comparedParameter2);
            } else {
                $resultParameter['itemState'] = DIFF_ELEMENT_CHANGED;
                $resultParameter['oldValue'] = $comparedParameter1;
                $resultParameter['newValue'] = $comparedParameter2;
            }
            return $resultParameter;
        }, $configKeys);
    };

    return $getDiff($firstConfig, $secondConfig);
}

function genDiff($fileLink1, $fileLink2, $outputFormat = 'text')
{
    $firstConfigContent = readFromFile($fileLink1);
    $secondConfigContent = readFromFile($fileLink2);

    $firstConfig = parseConfig($firstConfigContent);
    $secondConfig = parseConfig($secondConfigContent);

    $diff = buildDiff($firstConfig, $secondConfig);

    $difference = \Differ\renderer\render($diff, $outputFormat);
    return $difference;
}
