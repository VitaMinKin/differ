<?php

namespace Differ;

use function funct\Collection\union;
use function funct\Collection\flatten;
use function Differ\parsers\getFileContent;

function getDiff(array $f1, array $f2)
{

    $funct = function ($f1, $f2) use (&$funct) {
        $paramsList = array_merge($f1, $f2);
        $paramsList = array_keys($paramsList);

        $diff = array_map(function ($elem) use ($f1, $f2, &$funct) {
            $item = ['name' => $elem,
                'diff' => [],
                'children' => []
            ];

            if (!isset($f1[$elem])) {
                $item['diff'] = ['itemState' => 'added', 'value' => $f2[$elem]];
            } elseif (!isset($f2[$elem])) {
                $item['diff'] = ['itemState' => 'deleted', 'value' => $f1[$elem]];
            } elseif ($f1[$elem] === $f2[$elem]) {
                $item['diff'] = ['itemState' => 'unchanged', 'value' => $f1[$elem]];
            } else {
                if ((is_array($f1[$elem])) && (is_array($f2[$elem]))) {
                    $item['children'] = $funct($f1[$elem], $f2[$elem]);
                } else {
                    $item['diff'] = ['itemState' => 'changed', 'oldValue' => $f1[$elem], 'newValue' => $f2[$elem]];
                }
            }

            return $item;

        }, $paramsList);

        //return array_merge(...$diff);
        return $diff;
    };

    return $funct($f1, $f2);
}

function genDiff($pathToFile1, $pathToFile2, $format = null)
{
    try {
        $firstFile = getFileContent($pathToFile1);
        $secondFile = getFileContent($pathToFile2);
    } catch (\Exception $e) {
        printf($e);
        exit;
    }

    $data = compareData($firstFile, $secondFile);
    $result = implode(PHP_EOL, $data);
    return "{\n$result\n}\n";
}
