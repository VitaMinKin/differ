<?php

namespace Differ\parsers;

use SplFileInfo;
use Symfony\Component\Yaml\Yaml;

function isFileValid(splFileInfo $file, $path) //ты должен вернуть предикат! Исключения нужно вызывать в другом месте....
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
        return (json_last_error() === JSON_ERROR_NONE) ? $parsed : false;
    } elseif ($extension == 'yml') {
        $parsed = Yaml::parse($fileContent, Yaml::PARSE_OBJECT_FOR_MAP);
        return (array) $parsed;
    } else {//по содержимому файла почему не определяем??
        throw new \Exception("Extension '{$extension}' is not supported!");
    }

    return false;
}

function loadFile($pathFromUser)
{
    $file = new SplFileInfo($pathFromUser);

    if (isFileValid($file, $pathFromUser)) {
        $realPath = $file->getRealPath();
        $fileContent = file_get_contents($realPath);
        $fileExtension = $file->getExtension();
    } else {
        throw new \Exception("file '{$pathFromUser}' is not valid \n");
    }
    return [$fileContent, $fileExtension];
}
