<?php

namespace Differ\renderer;

use function Differ\Formatters\pretty\convertToText;
use function Differ\Formatters\plain\convertToPlain;
use function Differ\Formatters\json\convertToJson;

function render(array $diff, $outputFormat = 'text')
{
    switch ($outputFormat) {
        case 'plain':
            return convertToPlain($diff);
        case 'json':
            return convertToJson($diff);
        default:
            return convertToText($diff);
    }
}
