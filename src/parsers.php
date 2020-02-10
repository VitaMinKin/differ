<?php

namespace Differ\parsers;

use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

function getFileContent($format, $path)
{
    $file = new SplFileInfo($path);

    if ($file->isFile()) {
        $pathToFile = $file->getRealPath();
    } else {
        throw new \Exception("the passed path '{$path}' does not contain a file name");
    }

    if ($pathToFile === false) {
        throw new \Exception("file cannot be read along path '{$path}'");
    }

    $extension = $file->getExtension();

    $fileContent = file_get_contents($pathToFile);
    if ($fileContent === false) {
        throw new \Exception("file '{$path}' cannot be read");
    }

    /*
    Сопоставить представленный формат расширению файла, ругаться при несоответствии!
    */

    if (($format == 'json') || ($extension == 'json')) {
        return json_decode($fileContent, true);
    } elseif (($format == 'yaml') || ($extension == 'yml')) {
        $parsed = Yaml::parse($fileContent, Yaml::PARSE_OBJECT_FOR_MAP);
        return (array) $parsed;
    } else {
        throw new \Exception("file format not defined!");
    }
}
