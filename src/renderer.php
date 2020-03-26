<?php

namespace Differ\renderer;

use function Differ\Formatters\pretty\convertToText;
use function Differ\Formatters\plain\convertToPlain;
use function Differ\Formatters\json\convertToJson;

function render(array $diff, $format = 'default')
{
    switch ($format) {
        case 'plain':
            return convertToPlain($diff);
            break;
        case 'json':
            return convertToJson($diff);
            break;
        default:
            return convertToText($diff);
        break;
    }
}
