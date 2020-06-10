<?php

namespace Differ;

use function Differ\loader\readFromFile;
use function Differ\parsers\parseConfig;

function getAst(array $firstConfig, array $secondConfig)
{
    $ast = function ($firstConfig, $secondConfig) use (&$ast) {
        $configNames = array_keys(array_merge($firstConfig, $secondConfig));

        return array_map(function ($elementName) use ($firstConfig, $secondConfig, &$ast) {
            $resultParameter = ['name' => $elementName, 'diff' => [], 'children' => []];
            $comparedParameter1 = isset($firstConfig[$elementName]) ? $firstConfig[$elementName] : null;
            $comparedParameter2 = isset($secondConfig[$elementName]) ? $secondConfig[$elementName] : null;

            if (!isset($comparedParameter1)) {
                $resultParameter['diff'] = [
                    'itemState' => 'added', //ЭТО КОНСТАНТЫ!!!
                    'value' => $comparedParameter2
                ];
                return $resultParameter;
            }

            if (!isset($comparedParameter2)) {
                $resultParameter['diff'] = [
                    'itemState' => 'deleted',
                    'value' => $comparedParameter1
                ];
                return $resultParameter;
            }

            if ($comparedParameter1 === $comparedParameter2) {
                $resultParameter['diff'] = [
                    'itemState' => 'unchanged',
                    'value' => $comparedParameter1
                ];
                return $resultParameter;
            }

            if ((is_array($comparedParameter1)) && (is_array($comparedParameter2))) {
                $resultParameter['children'] = $ast($comparedParameter1, $comparedParameter2);
            } else {
                $resultParameter['diff'] = [
                    'itemState' => 'changed',
                    'oldValue' => $comparedParameter1,
                    'newValue' => $comparedParameter2
                ];
            }
            return $resultParameter;
        }, $configNames);
    };

    return $ast($firstConfig, $secondConfig);
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

    $ast = getAst($firstConfig, $secondConfig);

    $render = \Differ\renderer\render($ast, $outputFormat);
    return $render;
}
