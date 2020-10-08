<?php

namespace Differ\Differ;

use function Differ\loader\getFormat;
use function Differ\loader\read;
use function Differ\parsers\parseConfig;
use function Differ\builder\buildDiff;
use function Differ\renderer\render;

function genDiff($configPath1, $configPath2, $outputFormat = 'pretty')
{
    $firstConfigContent = read($configPath1);
    $secondConfigContent = read($configPath2);

    $firstConfigFormat = getFormat($configPath1);
    $secondConfigFormat = getFormat($configPath2);

    $firstConfig = parseConfig($firstConfigContent, $firstConfigFormat);
    $secondConfig = parseConfig($secondConfigContent, $secondConfigFormat);

    $diff = buildDiff($firstConfig, $secondConfig);

    return render($diff, $outputFormat);
}
