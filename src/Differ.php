<?php

namespace Differ;

use function Differ\parsers\loadFile;
use function Differ\parsers\parseContent;

function getAst(array $firstConfig, array $secondConfig)
{
    $ast = function ($firstConfig, $secondConfig) use (&$ast) {
        $configNames = array_keys(array_merge($firstConfig, $secondConfig));

        return array_map(function ($elementName) use ($firstConfig, $secondConfig, &$ast) {
            $resultParameter = ['name' => $elementName, 'diff' => [], 'children' => []];
            $comparedParameter1 = isset($firstConfig[$elementName]) ? $firstConfig[$elementName] : null;
            $comparedParameter2 = isset($secondConfig[$elementName]) ? $secondConfig[$elementName] : null;

            if (!isset($comparedParameter1)) {
                $resultParameter['diff'] = [
                    'itemState' => 'added',
                    'value' => $comparedParameter2
                ];
                return $resultParameter;
            }

            if (!isset($comparedParameter2)) {
                $resultParameter['diff'] = [
                    'itemState' => 'deleted',
                    'value' => $comparedParameter1
                ];
                return $resultParameter;
            }

            if ($comparedParameter1 === $comparedParameter2) {
                $resultParameter['diff'] = [
                    'itemState' => 'unchanged',
                    'value' => $comparedParameter1
                ];
                return $resultParameter;
            }

            if ((is_array($comparedParameter1)) && (is_array($comparedParameter2))) {
                $resultParameter['children'] = $ast($comparedParameter1, $comparedParameter2);
            } else {
                $resultParameter['diff'] = [
                    'itemState' => 'changed',
                    'oldValue' => $comparedParameter1,
                    'newValue' => $comparedParameter2
                ];
            }
            return $resultParameter;
        }, $configNames);
    };

    return $ast($firstConfig, $secondConfig);
}

function genDiff($pathToFirstFile, $pathToSecondFile, $outputFormat = 'default')
{
    try {
        $firstFile = loadFile($pathToFirstFile); //почему эта функция в модуле парсера???
        $secondFile = loadFile($pathToSecondFile);

        $firstContent = parseContent($firstFile);
        $secondContent = parseContent($secondFile);

        if ($firstContent === false) { //говнокод!
            throw new \Exception("file '{$pathToFirstFile}' is not valid \n");
        } elseif ($secondContent === false) {
            throw new \Exception("file '{$pathToSecondFile}' is not valid \n");
        } //надо думать
    } catch (\Exception $e) {
        printf($e->getMessage());
        exit;
    }

    $ast = getAst($firstContent, $secondContent);

    $render = \Differ\renderer\render($ast, $outputFormat);
    return $render;
}
