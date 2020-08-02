<?php

namespace Differ\parsers;

use Symfony\Component\Yaml\Yaml;

function convertToArray(object $object)
{
    return json_decode(json_encode($object), true);
}

function parseConfig($extension, $content)
{
    switch ($extension) {
        case 'json':
            $parsedData = json_decode($content, false, JSON_THROW_ON_ERROR);
            break;
        case 'yml':
            $parsedData = Yaml::parse($content, Yaml::PARSE_OBJECT_FOR_MAP + Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);
            break;
        default:
            throw new \Exception("Unsupported config file format");
    }

    return convertToArray($parsedData);
}
