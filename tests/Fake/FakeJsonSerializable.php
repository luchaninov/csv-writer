<?php

namespace Luchaninov\CsvWriter\Tests\Fake;

class FakeJsonSerializable implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return ['k' => 'v'];
    }
}
