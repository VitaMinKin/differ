<?php

namespace Differ\loader;

use SplFileInfo;

function readFromFile($fileLink)
{
    $file = new SplFileInfo($fileLink);

    $pathToFile = $file->getRealPath();

    return file_get_contents($pathToFile);
}

function getExtension(string $filePath)
{
    $splittenPath = explode('.', $filePath);
    $extension = $splittenPath[count($splittenPath) - 1];
    return $extension;
}
