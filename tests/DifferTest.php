<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;
use Differ\getDiff;

class DifferTest extends TestCase
{
    private $firstFile;
    private $secondFile;

    protected function setUp(): void
    {
        $firstFile = file_get_contents(__DIR__ . '/fixtures/beforeTree.json');
        $this->firstFile = json_decode($firstFile, true);

        $secondFile = file_get_contents(__DIR__ . '/fixtures/afterTree.json');
        $this->secondFile = json_decode($secondFile, true);
    }

    public function testGetAst()
    {
        $data = file_get_contents(__DIR__ . '/fixtures/AST.array');
        $expected = unserialize($data);

        $actual = \Differ\getAst($this->firstFile, $this->secondFile);
        $this->assertEquals($expected, $actual);
    }

    public function testConvertToText()
    {
        $data = file_get_contents(__DIR__ . '/fixtures/pretty.fmt');
        $expected = unserialize($data);

        $class = new \ReflectionClass('\Differ\DiffRenderer');
        $method = $class->getMethod('convertToText');
        $method->setAccessible(true);

        $diff = \Differ\getAst($this->firstFile, $this->secondFile);
        $render = new \Differ\DiffRenderer();
        $actual = $method->invoke($render, $diff);
        $this->assertEquals($expected, $actual);
    }

    public function testConvertToPlain()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/plain.fmt');

        $class = new \ReflectionClass('\Differ\DiffRenderer');
        $method = $class->getMethod('convertToPlain');
        $method->setAccessible(true);

        $diff = \Differ\getAst($this->firstFile, $this->secondFile);
        $render = new \Differ\DiffRenderer();
        $actual = $method->invoke($render, $diff);
        $this->assertEquals($expected, $actual);
    }

    public function testConvertToJson()
    {
        $data = file_get_contents(__DIR__ . '/fixtures/json.fmt');
        $expected = unserialize($data);

        $class = new \ReflectionClass('\Differ\DiffRenderer');
        $method = $class->getMethod('convertToJson');
        $method->setAccessible(true);

        $diff = \Differ\getAst($this->firstFile, $this->secondFile);
        $render = new \Differ\DiffRenderer();
        $actual = $method->invoke($render, $diff);
        $this->assertEquals($expected, $actual);
    }
}
