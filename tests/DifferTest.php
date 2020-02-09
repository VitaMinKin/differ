<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;
use Differ;

use function Differ\compareData;

class DifferTest extends TestCase
{
    public function testCompareFiles()
    {
        $testArray1 = json_decode(file_get_contents(__DIR__ . '/fixtures/before.json'), true);
        $testArray2 = json_decode(file_get_contents(__DIR__ . '/fixtures/after.json'), true);
        $str = file_get_contents(__DIR__ . '/fixtures/result.txt');
        $str = "   " . trim($str);
        $expected = explode(PHP_EOL, $str);

        $this->assertEquals($expected, compareData($testArray1, $testArray2));
    }
}
