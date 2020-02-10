<?php

namespace Differ;

use function funct\Collection\union;

use function Differ\parsers\getFileContent;

function compareData(array $f1, array $f2)
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
}

function genDiff($format = null, $pathToFile1, $pathToFile2)
{
    try {
        $firstFile = getFileContent($format, $pathToFile1);
        $secondFile = getFileContent($format, $pathToFile2);
    } catch (\Exception $e) {
        printf($e);
        exit;
    }

    $data = compareData($firstFile, $secondFile);
    $result = implode(PHP_EOL, $data);
    return "{\r\n$result\r\n}";
}
