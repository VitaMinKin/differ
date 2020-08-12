<?php

namespace Differ\parsers;

use Symfony\Component\Yaml\Yaml;

function parseConfig($content, $format)
{
    switch ($format) {
        case 'json':
            $parsedData = json_decode($content, false, JSON_THROW_ON_ERROR);
            break;
        case 'yaml':
        case 'yml':
            $parsedData = Yaml::parse($content, Yaml::PARSE_OBJECT_FOR_MAP + Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);
            break;
        default:
            throw new \Exception("Unsupported {$format} config format");
    }

    return $parsedData;
}
