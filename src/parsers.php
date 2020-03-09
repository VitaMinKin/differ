<?php

namespace Differ\parsers;

use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

function getFileContent($path)
{
    $file = new SplFileInfo($path);

    if ($file->isFile()) {
        $pathToFile = $file->getRealPath();
    } else {
        throw new \Exception("the passed path '{$path}' does not contain a file name \n");
    }

    if ($pathToFile === false) {
        throw new \Exception("file cannot be read along path '{$path}' \n");
    }

    $extension = $file->getExtension();

    $fileContent = file_get_contents($pathToFile);
    if ($fileContent === false) {
        throw new \Exception("file '{$path}' cannot be read \n");
    }


    if ($extension == 'json') {
        $parsed = json_decode($fileContent, true);
        if (!$parsed) {
            throw new \Exception("file '{$path}' has invalid json format \n");
        } else {
            return $parsed;
        }
    } elseif ($extension == 'yml') {
        $parsed = Yaml::parse($fileContent, Yaml::PARSE_OBJECT_FOR_MAP);
        return (array) $parsed;
    } else {
        throw new \Exception("file format not defined! \n");
    }
}
