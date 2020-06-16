<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;
use Differ\genDiff;

use function Differ\genDiff;

class DifferTest extends TestCase
{
    private $firstFile;
    private $secondFile;

    private function getFixturePath($fileName)
    {
        return __DIR__ . '/fixtures/' . $fileName;
    }

    private function readFile($filePath)
    {
        return trim(file_get_contents($filePath));
    }

    protected function setUp(): void
    {
        $firstFixturePath = $this->getFixturePath('beforeTree.json');
        $firstFile = $this->readFile($firstFixturePath);
        $this->firstFile = json_decode($firstFile, true);

        $secondFixturePath = $this->getFixturePath('afterTree.json');
        $secondFile = $this->readFile($secondFixturePath);
        $this->secondFile = json_decode($secondFile, true);
    }

    private function buildDiff()
    {
        return \Differ\buildDiff($this->firstFile, $this->secondFile);
    }

    public function testBuildDiff()
    {
        $fixturePath = $this->getFixturePath('AST.array');
        $data = $this->readFile($fixturePath);
        $expected = unserialize($data);

        $actual = $this->buildDiff();
        $this->assertEquals($expected, $actual);
    }

    public function testConvertToText()
    {
        $fixturePath = $this->getFixturePath('prettyNested.txt');
        $expected = $this->readFile($fixturePath);

        $diff = $this->buildDiff();
        $actual = \Differ\Formatters\pretty\convertToText($diff);

        $this->assertEquals($expected, $actual);
    }

    public function testConvertToPlain()
    {
        $fixturePath = $this->getFixturePath('plain.txt');
        $expected = $this->readFile($fixturePath);

        $diff = $this->buildDiff();
        $actual = \Differ\Formatters\plain\convertToPlain($diff);

        $this->assertEquals($expected, $actual);
    }

    public function testConvertToJson()
    {
        $expected = $this->getFixturePath('config.json');

        $diff = $this->buildDiff();
        $actual = \Differ\Formatters\json\convertToJson($diff);

        $this->assertJsonStringEqualsJsonFile($expected, $actual);
    }

    public function testRender()
    {
        $fixturePath = $this->getFixturePath('prettyNested.txt');
        $expected = $this->readFile($fixturePath);

        $diff = $this->buildDiff();

        $actual = \Differ\renderer\render($diff);
        $this->assertEquals($expected, $actual);

        $actual = \Differ\renderer\render($diff, 'pretty');
        $this->assertEquals($expected, $actual);

        $actual = \Differ\renderer\render($diff, 'anyString');
        $this->assertEquals($expected, $actual);
    }


    public function testGenDiff()
    {
        $fixturePath = $this->getFixturePath('prettyPlain.txt');
        $expected = $this->readFile($fixturePath);

        $firstConfig = $this->getFixturePath('before.json');
        $secondConfig = $this->getFixturePath('after.json');
        $actual = genDiff($firstConfig, $secondConfig);

        $this->assertEquals($expected, $actual);

        $fixturePath = $this->getFixturePath('yamlPlain.txt');
        $expected = $this->readFile($fixturePath);
        $firstConfig = $this->getFixturePath('before.yml');
        $secondConfig = $this->getFixturePath('after.yml');

        $actual = genDiff($firstConfig, $secondConfig);
        $this->assertEquals($expected, $actual);
    }
}
