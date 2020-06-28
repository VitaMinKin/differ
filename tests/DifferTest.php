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
        $firstConfig = $this->getFixturePath('beforeTree.json');
        $secondConfig = $this->getFixturePath('afterTree.json');

        $fixturePath = $this->getFixturePath('prettyNested.txt');
        $expected = $this->readConfig($fixturePath);
        $actual = genDiff($firstConfig, $secondConfig);
        $this->assertEquals($expected, $actual);

        $fixturePath = $this->getFixturePath('plain.txt');
        $expected = $this->readConfig($fixturePath);
        $actual = genDiff($firstConfig, $secondConfig, 'plain');
        $this->assertEquals($expected, $actual);

        $expected = $this->getFixturePath('config.json');
        $actual = genDiff($firstConfig, $secondConfig, 'json');
        $this->assertJsonStringEqualsJsonFile($expected, $actual);

        $fixturePath = $this->getFixturePath('yamlPlain.txt');
        $firstConfig = $this->getFixturePath('before.yml');
        $secondConfig = $this->getFixturePath('after.yml');
        $expected = $this->readConfig($fixturePath);
        $actual = genDiff($firstConfig, $secondConfig, 'errorRequest');
        $this->assertEquals($expected, $actual);
    }
}
