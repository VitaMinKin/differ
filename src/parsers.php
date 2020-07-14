<?php

namespace Differ\parsers;

use Symfony\Component\Yaml\Yaml;

function parseConfig(string $config)
{
    $parsedData = json_decode($config, true);
    if ($parsedData !== null) {
        return $parsedData;
    }

    $parsedData = Yaml::parse($config, Yaml::PARSE_OBJECT_FOR_MAP);
    if ($parsedData !== null) {
        return (array) $parsedData;
    }

    return false;
}
