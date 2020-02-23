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

    private $firstFile;
    private $secondFile;
    private $diff;

    protected function setUp():void
    {
        $firstFile = file_get_contents(__DIR__ . '/fixtures/beforeTree.json');
        $this->firstFile = json_decode($firstFile, true);

        $secondFile = file_get_contents(__DIR__ . '/fixtures/afterTree.json');
        $this->secondFile = json_decode($secondFile, true);

    }

    public function testGenDiffNested()
    {
        $expected = [
            'common' => [
                'setting1' => ['itemState' => 'unchanged', 'value' => 'Value 1'],
                'setting2' => ['itemState' => 'deleted', 'value' => '200'],
                'setting3' => ['itemState' => 'unchanged', 'value' => true],
                'setting6' => ['itemState' => 'deleted', 'value' => ['key'=> 'value']],
                'setting4' => ['itemState' => 'added', 'value' => 'blah blah'],
                'setting5' => ['itemState' => 'added', 'value' => [
                        'key5' => 'value5'
                    ],
                ],
            ],
            'group1' => [
              'baz' => ['itemState' => 'changed', 'oldValue' => 'bas', 'newValue' => "bars"],
              'foo' => ['itemState' => 'unchanged', 'value' => 'bar'],
            ],
            'group2' => ['itemState' => 'deleted', 'value' => ['abc'=> '12345']],
            'group3' => ['itemState' => 'added', 'value' => [
                'fee' => '100500'
                ]
            ]
        ];

        $actual = \Differ\getDiff($this->firstFile, $this->secondFile);
        //$this->diff = $actual;
        $this->assertEquals($expected, $actual);
    }

    public function testRender()
    {
        $expected = <<<EXP
{
   common: {
      setting1: Value 1
    - setting2: 200
      setting3: true
    - setting6: {
         key: value
      }
    + setting4: blah blah
    + setting5: {
         key5: value5
      }
   }
   group1: {
    + baz: bars
    - baz: bas
      foo: bar
   }
 - group2: {
      abc: 12345
   }
 + group3: {
      fee: 100500
   }
}
EXP;

        $diff = \Differ\getDiff($this->firstFile, $this->secondFile);
        $actual = \Differ\render($diff);
        print_r ($actual);
        $this->assertEquals($expected, $actual);
    }
}
