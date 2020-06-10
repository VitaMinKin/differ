<?php

namespace Differ\parsers;

use Symfony\Component\Yaml\Yaml;

function jsonParse($json)
{
    $parsed = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error = json_last_error();
        throw new \Exception("Error in json data: '{$error}' \n");
    }

    return $parsed;
}

function yamlParse($yml)
{
    $parsed = Yaml::parse($yml, Yaml::PARSE_OBJECT_FOR_MAP);
    return (array) $parsed;
}

function parseConfig(array $file)
{
    $extension = $file['extension'];
    $content = $file['content'];

    if ($extension == 'json') {
        return jsonParse($content);
    }

    if ($extension == 'yml') {
        return yamlParse($content);
    } else {
        throw new \Exception("Extension '{$extension}' is not supported!");
    }

    return false;
}
