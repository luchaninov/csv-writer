<?php

namespace Luchaninov\CsvWriter\Tests\Fake;

class FakeStringable implements \Stringable
{
    public function __toString(): string
    {
        return 'fakestringable';
    }
}
