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
            $resultParameter = ['name' => $elementName, 'diff' => [], 'children' => []];
            $comparedParameter1 = isset($firstConfig[$elementName]) ? $firstConfig[$elementName] : null;
            $comparedParameter2 = isset($secondConfig[$elementName]) ? $secondConfig[$elementName] : null;

            if (!isset($comparedParameter1)) {
                $resultParameter['diff'] = [
                    'itemState' => DIFF_ELEMENT_ADDED,
                    'value' => $comparedParameter2
                ];
                return $resultParameter;
            }

            if (!isset($comparedParameter2)) {
                $resultParameter['diff'] = [
                    'itemState' => DIFF_ELEMENT_REMOVED,
                    'value' => $comparedParameter1
                ];
                return $resultParameter;
            }

            if ($comparedParameter1 === $comparedParameter2) {
                $resultParameter['diff'] = [
                    'itemState' => DIFF_ELEMENT_UNCHANGED,
                    'value' => $comparedParameter1
                ];
                return $resultParameter;
            }

            if ((is_array($comparedParameter1)) && (is_array($comparedParameter2))) {
                $resultParameter['children'] = $getDiff($comparedParameter1, $comparedParameter2);
            } else {
                $resultParameter['diff'] = [
                    'itemState' => DIFF_ELEMENT_CHANGED,
                    'oldValue' => $comparedParameter1,
                    'newValue' => $comparedParameter2
                ];
            }
            return $resultParameter;
        }, $configKeys);
    };

    return $getDiff($firstConfig, $secondConfig);
}

function genDiff($fileLink1, $fileLink2, $outputFormat = 'default')
{
    try {
        $config1 = readFromFile($fileLink1);
        $config2 = readFromFile($fileLink2);
    } catch (\Exception $e) {
        printf($e->getMessage());
        exit;
    }

    try {
        $firstConfig = parseConfig($config1);
    } catch (\Exception $e) {
        printf("\nError in file $fileLink1: {$e->getMessage()} \n");
        exit;
    }

    try {
        $secondConfig = parseConfig($config2);
    } catch (\Exception $e) {
        printf("\nError in file $fileLink2: {$e->getMessage()} \n");
        exit;
    }

    $diff = buildDiff($firstConfig, $secondConfig);

    $render = \Differ\renderer\render($diff, $outputFormat);
    return $render;
}
