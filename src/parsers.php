<?php

namespace Differ\parsers;

use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

function fileValid($file, $path)
{
    if (!$file->isFile()) {
        throw new \Exception("the passed path '{$path}' does not contain a file name! \n");
    }

    if (!$file->isReadable()) {
        throw new \Exception("file '{$file->getFilename()}' cannot be read! \n");
    }

    if (!$file->getRealPath()) {
        return false;
    }

    return true;
}

function parseContent($content)
{
    [$fileContent, $extension] = $content;
    if ($extension == 'json') {
        $parsed = json_decode($fileContent, true);
        return (json_last_error() !== JSON_ERROR_NONE) ? false : $parsed;
    } elseif ($extension == 'yml') {
        $parsed = Yaml::parse($fileContent, Yaml::PARSE_OBJECT_FOR_MAP);
        return (array) $parsed;
    } else {
        return false;
    }
}

function getFileContent($path)
{
    $file = new SplFileInfo($path);

    if (fileValid($file, $path)) {
        $extension = $file->getExtension();
        $pathToFile = $file->getRealPath();
        $fileContent = file_get_contents($pathToFile);
    } else {
        throw new \Exception("file '{$path}' is not valid \n");
    }
    return [$fileContent, $extension];
}
