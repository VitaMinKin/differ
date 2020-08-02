<?php

namespace Differ\Formatters\json;

function convertToJson(array $diff)
{
    return json_encode($diff, JSON_PRETTY_PRINT);
}
