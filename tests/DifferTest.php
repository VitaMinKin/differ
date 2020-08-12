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

    public function genDiffProvider()
    {
        return [
           'pretty nested format' => ['beforeTree.json', 'afterTree.json', 'prettyNested.txt', null],
            'plain format' => ['beforeTree.json', 'afterTree.json', 'plain.txt', 'plain'],
            'yaml format' => ['before.yml', 'after.yml', 'yamlPlain.txt', 'unknownFormat'],
            'json format' => ['beforeTree.json', 'afterTree.json', 'config.json', 'json']
        ];
    }

    /**
     * @dataProvider genDiffProvider
     */

    public function testGenDiff($fixture1, $fixture2, $resultFixture, $format)
    {
        $pathToFirstFixture = $this->getFixturePath($fixture1);
        $pathToSecondFixture = $this->getFixturePath($fixture2);
        $pathToResultFixture = $this->getFixturePath($resultFixture);

        $expected = $this->readFixture($pathToResultFixture);
        $actual = genDiff($pathToFirstFixture, $pathToSecondFixture, $format);

        $this->assertEquals($expected, $actual);
    }
}
