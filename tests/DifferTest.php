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
           'pretty nested format' => ['nestedBefore.json', 'nestedAfter.json', 'expectedNestedPretty.txt', null],
            'plain format' => ['nestedBefore.json', 'nestedAfter.json', 'expectedPlain.txt', 'plain'],
            'yaml format' => ['plainBefore.yml', 'plainAfter.yml', 'expectedPlainYml.txt', 'unknownFormat'],
            'json format' => ['nestedBefore.json', 'nestedAfter.json', 'expectedNested.json', 'json']
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
