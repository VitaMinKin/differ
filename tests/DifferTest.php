<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use Differ\getDiff;

class DifferTest extends TestCase
{
    /*public function testGenDiffJson()
    {
        $firstFile = __DIR__ . '/fixtures/before.json';
        $secondFile = __DIR__ .'/fixtures/after.json';
        $expected = file_get_contents(__DIR__ . '/fixtures/result4Json.txt');
        $result = genDiff($firstFile, $secondFile);

        $this->assertEquals($expected, $result);
    }

    public function testGenDiffYaml()
    {
        $firstFile = __DIR__ . '/fixtures/before.yml';
        $secondFile = __DIR__ .'/fixtures/after.yml';
        $expected = file_get_contents(__DIR__ . '/fixtures/result4yaml.txt');
        $result = genDiff($firstFile, $secondFile);

        $this->assertEquals($expected, $result);
    }*/

    /*public function testGetDiffFlat()
    {
        $firstFile = file_get_contents(__DIR__ . '/fixtures/before.json');
        $first = json_decode($firstFile, true);

        $secondFile = file_get_contents(__DIR__ . '/fixtures/after.json');
        $second = json_decode($secondFile, true);

        $expected = [
                'host' => ['diff' => 'unchanged', 'value' => 'hexlet.io'],
                'timeout' => ['diff' => 'changed', 'oldValue' => '50', 'newValue' => '20'],
                'proxy' => ['diff' => 'deleted'],
                'verbose' => ['diff' => 'added', 'value' => '1'],
                'time' => ['diff' => 'added', 'value' => '15s']
        ];

        $result = \Differ\getDiff($first, $second);
        $this->assertEquals($expected, $result);
    }*/

    public function testGenDiffNested()
    {
        $firstFile = file_get_contents(__DIR__ . '/fixtures/beforeTreeDeep.json');
        $first = json_decode($firstFile, true);

        $secondFile = file_get_contents(__DIR__ . '/fixtures/afterTreeDeep.json');
        $second = json_decode($secondFile, true);

        $expected = [
            ['common' => [
                ['setting1' => ['change' => 'unchanged', 'value' => 'Value 1']],
                ['setting2' => ['change' => 'deleted']],
                ['setting3' => ['change' => 'unchanged', 'value' => true]],
                ['setting6' => ['change' => 'deleted']],
                ['setting4' => ['change' => 'added', 'value' => 'blah blah']],
                ['setting5' => ['change' => 'added', 'value' => [
                        'key5' => 'value5'
                    ],
                ]],
            ]],
            ['group1' => [
              ['baz' => ['diff' => 'changed', 'oldValue' => 'bas', 'newValue' => "bars"]],
              ['foo' => ['diff' => 'unchanged', 'value' => 'bar']],
            ]],
            ['group2' => ['diff' => 'deleted']],
            ['group3' => ['diff' => 'added', 'value' => [
                'fee' => '100500'
                ]
            ]]
        ];

        $result = \Differ\getDiff($first, $second);
        $this->assertEquals($expected, $result);
    }
}
