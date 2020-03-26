<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;
use Differ\genDiff;

use function Differ\genDiff;

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
        $data = file_get_contents(__DIR__ . '/fixtures/prettyNested.fmt');
        $expected = unserialize($data);

        $diff = \Differ\getAst($this->firstFile, $this->secondFile);

        $actual = \Differ\Formatters\pretty\convertToText($diff);
        $this->assertEquals($expected, $actual);
    }

    public function testConvertToPlain()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/plain.fmt');
        $diff = \Differ\getAst($this->firstFile, $this->secondFile);

        $actual = \Differ\Formatters\plain\convertToPlain($diff);
        $this->assertEquals($expected, $actual);
    }

    public function testConvertToJson()
    {
        $data = file_get_contents(__DIR__ . '/fixtures/jsonNested.fmt');
        $expected = unserialize($data);

        $diff = \Differ\getAst($this->firstFile, $this->secondFile);
        $actual = \Differ\Formatters\json\convertToJson($diff);
        $this->assertEquals($expected, $actual);
    }

    public function testRender()
    {
        $data = file_get_contents(__DIR__ . '/fixtures/prettyNested.fmt');
        $expected = unserialize($data);

        $diff = \Differ\getAst($this->firstFile, $this->secondFile);

        $actual = \Differ\renderer\render($diff);
        $this->assertEquals($expected, $actual);

        $actual = \Differ\renderer\render($diff, 'pretty');
        $this->assertEquals($expected, $actual);

        $actual = \Differ\renderer\render($diff, 'anyString');
        $this->assertEquals($expected, $actual);
    }


    public function testGenDiff()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/prettyPlain.fmt');
        $actual = genDiff(__DIR__ . '/fixtures/before.json', __DIR__ . '/fixtures/after.json');
        $this->assertEquals($expected, $actual);

        $expected = file_get_contents(__DIR__ . '/fixtures/yamlPlain.fmt');
        $actual = genDiff(__DIR__ . '/fixtures/before.yml', __DIR__ . '/fixtures/after.yml');
        $this->assertEquals($expected, $actual);
    }
}
