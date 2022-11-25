<?php

namespace Luchaninov\CsvWriter\Tests;

use Luchaninov\CsvWriter\CsvWriter;
use Luchaninov\CsvWriter\Tests\Fake\FakeJsonSerializable;
use Luchaninov\CsvWriter\Tests\Fake\FakeStringable;
use PHPUnit\Framework\TestCase;

class CsvWriterTest extends TestCase
{
    public function testGenerate(): void
    {
        $items = [
            ['k1' => 'v1_1', 'k2' => 'v1_2', 'k3' => 'v1_3'],
            ['k1' => 'v2_1', 'k2' => 'v2_2', 'k3' => 'v2_3'],
            ['k1' => 'v3_1', 'k2' => 'v3_2', 'k3' => 'v3_3'],
        ];

        $expected = <<<EOD
        k1,k2,k3
        v1_1,v1_2,v1_3
        v2_1,v2_2,v2_3
        v3_1,v3_2,v3_3
        EOD;

        self::assertSame($expected, (new CsvWriter())->generate($items));
    }

    public function testGenerateWithNonStrings(): void
    {
        $items = [
            ['k1' => new \DateTime('2000-01-02 03:04:05'), 'k2' => ['a', 'b'], 'k3' => ['a' => 'b', 'c' => 'd']],
            ['k1' => 1, 'k2' => -2.34, 'k3' => "test\ttest\rtest\ntest"],
            'this_will_be_skipped',
            ['k1' => '"v3_1', 'k2' => new FakeStringable(), 'k3' => new FakeJsonSerializable()],
        ];

        $expected = <<<EOD
        k1,k2,k3
        2000-01-02 03:04:05,"[""a"",""b""]","{""a"":""b"",""c"":""d""}"
        1,-2.34,test\\ttest\\rtest\\ntest
        """v3_1",fakestringable,{"k":"v"}
        EOD;

        self::assertSame($expected, (new CsvWriter())->generate($items));
    }
}
