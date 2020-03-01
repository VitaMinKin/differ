<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use Differ\getDiff;

class DifferTest extends TestCase
{
    private $firstFile;
    private $secondFile;

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
            ['name' => 'common',
            'diff' => [],
            'children' => [
                ['name' => 'setting1',
                'diff' => ['itemState' => 'unchanged', 'value' => 'Value 1'],
                'children' => []],
                ['name' => 'setting2',
                'diff' => ['itemState' => 'deleted', 'value' => '200'],
                'children' => []],
                ['name' => 'setting3',
                'diff' => ['itemState' => 'unchanged', 'value' => true],
                'children' => []],
                ['name' => 'setting6',
                'diff' => ['itemState' => 'deleted', 'value' => ['key'=> 'value']],
                'children' => []],
                ['name' => 'setting4',
                'diff' => ['itemState' => 'added', 'value' => 'blah blah'],
                'children' => []],
                ['name' => 'setting5',
                'diff' => ['itemState' => 'added', 'value' => ['key5' => 'value5']],
                'children' => []]
            ]],
            ['name' => 'group1',
            'diff' => [],
            'children' => [
                ['name' => 'baz',
                'diff' => ['itemState' => 'changed', 'oldValue' => 'bas', 'newValue' => "bars"],
                'children' => []],
                ['name' => 'foo',
                'diff' => ['itemState' => 'unchanged', 'value' => 'bar'],
                'children' => []]
            ]],
            ['name' => 'group2',
            'diff' => ['itemState' => 'deleted', 'value' => ['abc'=> '12345']],
            'children' => []],
            ['name' => 'group3',
            'diff' => ['itemState' => 'added', 'value' => ['fee' => '100500']],
            'children' => []
            ]
        ];

        $actual = \Differ\getDiff($this->firstFile, $this->secondFile);
        $this->assertEquals($expected, $actual);
    }

    public function testDiffRenderer()
    {
        $expected = <<<EXP
{
   common: {
      setting1: Value 1
    - setting2: 200
      setting3: 1
    - setting6: {"key":"value"}
    + setting4: blah blah
    + setting5: {"key5":"value5"}
   }
   group1: {
    - baz: bas
    + baz: bars
      foo: bar
   }
 - group2: {"abc":"12345"}
 + group3: {"fee":"100500"}
}

EXP;

        $diff = \Differ\getDiff($this->firstFile, $this->secondFile);
        $render = new \Differ\DiffRenderer();
        $actual = $render->render($diff);
        $this->assertEquals($expected, $actual);
    }
}
