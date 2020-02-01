<?php

namespace Differ;
use function funct\Collection\union;

function getFileContent($file)
{
    $pathToFile = realpath($file);
    if ($pathToFile !== false) {
        $fileContent = file_get_contents($pathToFile);
        return json_decode($fileContent, true);
    } else {
        echo "file '{$file}' not found or not readable";
        //подумай над тем, как выбросить тут исключение!
        exit;
    }
    return;
}

function compareFiles($f1, $f2)
{
    $filesUnion = union(array_keys($f1), array_keys($f2));
    $result = array_reduce($filesUnion, function ($acc, $item) use ($f1, $f2) {
        if (!isset($f1[$item])) {
            $acc .= " - $item: {$f2[$item]}\r\n";
        } elseif (!isset($f2[$item])) {
            $acc .= " + $item: {$f1[$item]}\r\n";
        } elseif ($f1[$item] === $f2[$item]) {
            $acc .= "   $item: {$f2[$item]}\r\n";
        } else {
            $acc .= " + $item: {$f1[$item]}\r\n - $item: {$f2[$item]}\r\n";
        }
        return $acc;
    }, "");
    return "{\r\n$result} \r\n";
}

function genDiff($pathToFile1, $pathToFile2)
{
    $firstFile = getFileContent($pathToFile1);
    $secondFile = getFileContent($pathToFile2);

    return compareFiles($firstFile, $secondFile);
}
