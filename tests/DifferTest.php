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

    public function testBuildDiff()
    {
        $data = file_get_contents(__DIR__ . '/fixtures/AST.array');
        $expected = unserialize($data);

        $actual = \Differ\buildDiff($this->firstFile, $this->secondFile);
        $this->assertEquals($expected, $actual);
    }

    public function testConvertToText()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/prettyNested.fmt');

        $diff = \Differ\buildDiff($this->firstFile, $this->secondFile);
        $actual = \Differ\Formatters\pretty\convertToText($diff);

        $this->assertEquals($expected, $actual);
    }

    public function testConvertToPlain()
    {
        $expected = file_get_contents(__DIR__ . '/fixtures/plain.fmt');
        $diff = \Differ\buildDiff($this->firstFile, $this->secondFile);

        $actual = \Differ\Formatters\plain\convertToPlain($diff);
        $this->assertEquals($expected, $actual);
    }

    public function testConvertToJson()
    {
        $expected = __DIR__ . '/fixtures/config.json';
        $diff = \Differ\buildDiff($this->firstFile, $this->secondFile);
        $actual = \Differ\Formatters\json\convertToJson($diff);

        $this->assertJsonStringEqualsJsonFile($expected, $actual);
    }

    public function testRender()
    {
        $data = file_get_contents(__DIR__ . '/fixtures/prettyNested.fmt');

        $expected = $data;

        $diff = \Differ\buildDiff($this->firstFile, $this->secondFile);

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
