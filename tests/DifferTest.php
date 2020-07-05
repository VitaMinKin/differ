<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\genDiff;

class DifferTest extends TestCase
{
    private function getFixturePath($fileName)
    {
        return __DIR__ . '/fixtures/' . $fileName;
    }

    private function readConfig($filePath)
    {
        return trim(file_get_contents($filePath));
    }

    public function testGenDiff()
    {
        $firstPathToConfig = $this->getFixturePath('beforeTree.json');
        $secondPathToConfig = $this->getFixturePath('afterTree.json');

        $fixturePath = $this->getFixturePath('prettyNested.txt');
        $expected = $this->readConfig($fixturePath);
        $actual = genDiff($firstPathToConfig, $secondPathToConfig);
        $this->assertEquals($expected, $actual);

        $fixturePath = $this->getFixturePath('plain.txt');
        $expected = $this->readConfig($fixturePath);
        $actual = genDiff($firstPathToConfig, $secondPathToConfig, 'plain');
        $this->assertEquals($expected, $actual);

        $expected = $this->getFixturePath('config.json');
        $actual = genDiff($firstPathToConfig, $secondPathToConfig, 'json');
        $this->assertJsonStringEqualsJsonFile($expected, $actual);

        $fixturePath = $this->getFixturePath('yamlPlain.txt');
        $firstPathToConfig = $this->getFixturePath('before.yml');
        $secondPathToConfig = $this->getFixturePath('after.yml');
        $expected = $this->readConfig($fixturePath);
        $actual = genDiff($firstPathToConfig, $secondPathToConfig, 'errorRequest');
        $this->assertEquals($expected, $actual);
    }
}
