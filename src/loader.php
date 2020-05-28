<?php

namespace Differ\loader;

use SplFileInfo;

function readFromFile($fileLink)
{
    $file = new SplFileInfo($fileLink);

    $pathToFile = $file->getRealPath();
    if (!$pathToFile) {
        throw new \Exception("Invalid file path received {$fileLink} \n");
    }

    if (!$file->isFile()) {
        throw new \Exception("the passed path '{$fileLink}' does not contain a file name! \n");
    }

    if (!$file->isReadable()) {
        throw new \Exception("file '{$file->getFilename()}' cannot be read! \n");
    }

    $fileContent = file_get_contents($pathToFile);
    $fileExtension = $file->getExtension();

    return ['content' => $fileContent, 'extension' => $fileExtension];
}
