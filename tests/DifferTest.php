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

    public function testconvertToText()
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
        $class = new \ReflectionClass('\Differ\DiffRenderer');
        $method = $class->getMethod('convertToText');
        $method->setAccessible(true);

        $diff = \Differ\getDiff($this->firstFile, $this->secondFile);
        $render = new \Differ\DiffRenderer();
        $actual = $method->invoke($render, $diff);
        $this->assertEquals($expected, $actual);
    }

    public function testConvertToPlain()
    {
        $expected = <<<EXP
Property 'common.setting2' was removed
Property 'common.setting6' was removed
Property 'common.setting4' was added with value: 'blah blah'
Property 'common.setting5' was added with value: 'complex value'
Property 'group1.baz' was changed. From 'bas' to 'bars'
Property 'group2' was removed
Property 'group3' was added with value: 'complex value'

EXP;
        $class = new \ReflectionClass('\Differ\DiffRenderer');
        $method = $class->getMethod('convertToPlain');
        $method->setAccessible(true);

        $diff = \Differ\getDiff($this->firstFile, $this->secondFile);
        $render = new \Differ\DiffRenderer();
        $actual = $method->invoke($render, $diff);
        $this->assertEquals($expected, $actual);
    }

    public function testConvertToJson()
    {
        $expected = <<<EXP
{
    "common": {
        "setting1": "Value 1",
        "setting2": [
            "200",
            "removed"
        ],
        "setting3": true,
        "setting6": [
            "{\"key\":\"value\"}",
            "removed"
        ],
        "setting4": [
            "blah blah",
            "added"
        ],
        "setting5": [
            "{\"key5\":\"value5\"}",
            "added"
        ]
    },
    "group1": {
        "baz": [
            {
                "oldValue": "bas",
                "newValue": "bars"
            },
            "changed"
        ],
        "foo": "bar"
    },
    "group2": [
        "{\"abc\":\"12345\"}",
        "removed"
    ],
    "group3": [
        "{\"fee\":\"100500\"}",
        "added"
    ]
}
EXP;
        $class = new \ReflectionClass('\Differ\DiffRenderer');
        $method = $class->getMethod('convertToJson');
        $method->setAccessible(true);

        $diff = \Differ\getDiff($this->firstFile, $this->secondFile);
        $render = new \Differ\DiffRenderer();
        $actual = $method->invoke($render, $diff);
        $this->assertEquals($expected, $actual);
    }

}
