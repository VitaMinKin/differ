<?php

namespace Differ;

use function funct\Collection\union;
use function funct\Collection\flatten;
use function Differ\parsers\getFileContent;

function getDiff(array $f1, array $f2)
{

    $funct = function ($f1, $f2) use (&$funct) {
        $itog = array_merge($f1, $f2);
        $itog = array_keys($itog);

        $map = array_map(function ($elem) use ($f1, $f2, &$funct) {
            if (!isset($f1[$elem])) {

                return [$elem => ['change' => 'added', 'value' => $f2[$elem]]];
            } elseif (!isset($f2[$elem])) {

                return [$elem => ['change' => 'deleted']];
            } elseif ($f1[$elem] === $f2[$elem]) {

                return [$elem => ['change' => 'unchanged', 'value' => $f1[$elem]]];
            } else {
                if ((is_array($f1[$elem])) && (is_array($f2[$elem]))) {

                    return [$elem => $funct($f1[$elem], $f2[$elem])];
                } else {

                    return [$elem => ['change' => 'changed', 'oldValue' => $f1[$elem], 'newValue' => $f2[$elem]]];
                }
            }
        }, $itog);
    return $map;
    };

    $lolo = $funct($f1, $f2);
    print_r ($lolo);
    return $lolo;
}

/*function compareData(array $f1, array $f2)
{
    $filesUnion = union(array_keys($f1), array_keys($f2));

    $result = array_reduce($filesUnion, function ($acc, $item) use ($f1, $f2) {
        if (!isset($f1[$item])) {
            $acc[] = " + $item: {$f2[$item]}";
        } elseif (!isset($f2[$item])) {
            $acc[] = " - $item: {$f1[$item]}";
        } elseif ($f1[$item] === $f2[$item]) {
            $acc[] = "   $item: {$f2[$item]}";
        } else {
            $acc[] = " - $item: {$f1[$item]}";
            $acc[] = " + $item: {$f2[$item]}";
        }
        return $acc;
    }, []);
    return $result;
}*/

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
