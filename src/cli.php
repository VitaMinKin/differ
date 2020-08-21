<?php

namespace Differ\cli;

use function Differ\Differ\genDiff;

function run()
{
    $cli = file_get_contents(__DIR__ . '/cli.docopt');

    $params = [
        'argv' => array_slice($_SERVER['argv'], 1),
        'help' => true,
        'version' => 'differ 0.0.1',
        'optionsFirst' => false,
    ];

    $arguments = \Docopt::handle($cli, $params)->args;

    ['<firstFile>' => $pathToFile1,
    '<secondFile>' => $pathToFile2,
    '--format' => $outputFormat] = $arguments;

    try {
        $differ = genDiff($pathToFile1, $pathToFile2, $outputFormat);
        echo $differ;
    } catch (\Exception $e) {
        echo "Differ library runtime error {$e->getCode()}: ", $e->getMessage(), "\n";
    }
}
