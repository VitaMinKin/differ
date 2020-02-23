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
            if (!isset($f1[$elem])) {
                return [$elem => ['itemState' => 'added', 'value' => $f2[$elem]]];
            } elseif (!isset($f2[$elem])) {
                return [$elem => ['itemState' => 'deleted', 'value' => $f1[$elem]]];
            } elseif ($f1[$elem] === $f2[$elem]) {
                return [$elem => ['itemState' => 'unchanged', 'value' => $f1[$elem]]];
            } else {
                if ((is_array($f1[$elem])) && (is_array($f2[$elem]))) {
                    return [$elem => $funct($f1[$elem], $f2[$elem])];
                } else {
                    return [$elem => ['itemState' => 'changed', 'oldValue' => $f1[$elem], 'newValue' => $f2[$elem]]];
                }
            }
        }, $paramsList);

    return array_merge(...$diff);
    };

    return $funct($f1, $f2);
}

function render(array $diff)
{
    $funct = function ($diff) use (&$funct) {
        $keys = array_keys($diff);
        $result = array_reduce($keys, function ($acc, $element) use ($diff, &$funct) {

            $item = $diff[$element];

            if (!isset($item['itemState'])) {
                $acc .= "   $element: {\n{$funct($item)}   }\n";
            } else {

                $itemState = $item['itemState'];

                if (isset($item['value'])){
                    if (is_array($item['value'])) {
                        $value = json_encode($item['value']);
                    } else {
                        $value = $item['value'];
                    }
                }

                switch ($itemState) {
                    case 'unchanged':
                        $acc .= "   $element: $value\n";
                    break;
                    case 'deleted':
                        $acc .= " - $element: $value\n";
                    break;
                    case 'added':
                        $acc .= " + $element: $value\n";
                    break;
                    case 'changed':
                        $acc .= " - $element: {$item['oldValue']}\n";
                        $acc .= " + $element: {$item['oldValue']}\n";
                    break;
                }
            }
            return $acc;
        }, '');
        return $result;
    };
    return $funct($diff);
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
