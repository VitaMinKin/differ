<?php

namespace Differ\renderer;

use function Differ\Formatters\pretty\convertToPretty;
use function Differ\Formatters\plain\convertToPlain;
use function Differ\Formatters\json\convertToJson;

function render(array $diff, $outputFormat)
{
    switch ($outputFormat) {
        case 'plain':
            return convertToPlain($diff);
        case 'json':
            return convertToJson($diff);
        default:
            return convertToPretty($diff);
    }
}
