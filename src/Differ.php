<?php

namespace Differ;

use function Differ\parsers\loadFile;
use function Differ\parsers\parseContent;

function getAst(array $firstContent, array $secondContent)
{
    $ast = function ($firstContent, $secondContent) use (&$ast) {
        $paramsList = array_keys(array_merge($firstContent, $secondContent));

        return array_map(function ($elem) use ($firstContent, $secondContent, &$ast) {
            $item = ['name' => $elem, 'diff' => [], 'children' => []];

            if (!isset($firstContent[$elem])) {
                $item['diff'] = [
                    'itemState' => 'added',
                    'value' => $secondContent[$elem]
                ];
            } elseif (!isset($secondContent[$elem])) {
                $item['diff'] = [
                    'itemState' => 'deleted',
                    'value' => $firstContent[$elem]
                ];
            } elseif ($firstContent[$elem] === $secondContent[$elem]) {
                $item['diff'] = [
                    'itemState' => 'unchanged',
                    'value' => $firstContent[$elem]
                ];
            } else {
                if ((is_array($firstContent[$elem])) && (is_array($secondContent[$elem]))) {
                    $item['children'] = $ast($firstContent[$elem], $secondContent[$elem]);
                } else {
                    $item['diff'] = [
                        'itemState' => 'changed',
                        'oldValue' => $firstContent[$elem],
                        'newValue' => $secondContent[$elem]
                    ];
                }
            }

            return $item;
        }, $paramsList);
    };

    return $ast($firstContent, $secondContent);
}

function genDiff($pathToFirstFile, $pathToSecondFile, $outputFormat = 'default')
{
    try {
        $firstFile = loadFile($pathToFirstFile);
        $secondFile = loadFile($pathToSecondFile);

        $firstContent = parseContent($firstFile);
        $secondContent = parseContent($secondFile);

        if ($firstContent === false) {
            throw new \Exception("file '{$pathToFirstFile}' is not valid \n");
        } elseif ($secondContent === false) {
            throw new \Exception("file '{$pathToSecondFile}' is not valid \n");
        }
    } catch (\Exception $e) {
        printf($e->getMessage());
        exit;
    }

    $ast = getAst($firstContent, $secondContent);

    $render = \Differ\renderer\render($ast, $outputFormat);
    return $render;
}
