<?php

namespace Differ\loader;

use SplFileInfo;

function readFromFile($fileLink)
{
    $file = new SplFileInfo($fileLink);

    $pathToFile = $file->getRealPath();

    return file_get_contents($pathToFile);
}
