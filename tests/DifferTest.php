<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;

class DifferTest extends TestCase
{
    private function getFixturePath($fileName)
    {
        return __DIR__ . '/fixtures/' . $fileName;
    }

    private function readFixture($filePath)
    {
        return trim(file_get_contents($filePath));
    }

    public function testGenDiff()
    {
        $pathToFirstFixture = $this->getFixturePath('beforeTree.json');
        $pathToSecondFixture = $this->getFixturePath('afterTree.json');
        $pathToResultFixture = $this->getFixturePath('prettyNested.txt');

        $expected = $this->readFixture($pathToResultFixture);
        $actual = genDiff($pathToFirstFixture, $pathToSecondFixture);
        $this->assertEquals($expected, $actual);

        $pathToResultFixture = $this->getFixturePath('plain.txt');
        $expected = $this->readFixture($pathToResultFixture);
        $actual = genDiff($pathToFirstFixture, $pathToSecondFixture, 'plain');
        $this->assertEquals($expected, $actual);

        $expected = $this->getFixturePath('config.json');
        $actual = genDiff($pathToFirstFixture, $pathToSecondFixture, 'json');
        $this->assertJsonStringEqualsJsonFile($expected, $actual);

        $pathToResultFixture = $this->getFixturePath('yamlPlain.txt');
        $pathToFirstFixture = $this->getFixturePath('before.yml');
        $pathToSecondFixture = $this->getFixturePath('after.yml');
        $expected = $this->readFixture($pathToResultFixture);
        $actual = genDiff($pathToFirstFixture, $pathToSecondFixture, 'errorRequest');
        $this->assertEquals($expected, $actual);
    }
}
