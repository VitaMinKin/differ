<?php

namespace Differ;

use function funct\Collection\union;

function getFileContent($path)
{
    $pathToFile = realpath($path);

    if ($pathToFile === false) {
        throw new \Exception("file cannot be read along path '{$path}'");
    }

    $fileContent = file_get_contents($pathToFile);
    return json_decode($fileContent, true);
}

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

function genDiff($pathToFile1, $pathToFile2)
{
    try {
        $firstFile = getFileContent($pathToFile1);
        $secondFile = getFileContent($pathToFile2);
    } catch (\Exception $e) {
        echo $e;
        exit;
    }

    $data = compareData($firstFile, $secondFile);
    $result = implode(PHP_EOL, $data);
    return "{\r\n$result\r\n}";
}
