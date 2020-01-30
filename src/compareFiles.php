<?php

namespace genDiff\compareFiles;

function getFileContent($file)
{
    $fileWithPath = realpath($file);
    if ($fileWithPath !== false) {
        $fileContent = file_get_contents($fileWithPath);
        return json_decode($fileContent);
    } else {
        echo "file '{$file}' not found or not readable";
        //подумай над тем, как выбросить тут исключение!
        exit;
    }
    return;
}

function run(array $params)
{
    $firstFile = getFileContent($params[0]);
    $secondFile = getFileContent($params[1]);

    print_r ($firstFile);
   echo "это продолжение программы!";
}
