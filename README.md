CSV Writer
==========

Create CSV/TSV files/strings from arrays or objects

How to Install
--------------

Install the `luchaninov/csv-writer` package using [composer](http://getcomposer.org/):

```shell
$ composer require luchaninov/csv-writer
```

Basic Usage
-----------

You can convert named array to CSV or TSV

```php
$items = [
    ['k1' => 'v1_1', 'k2' => 'v1_2', 'k3' => 'v1_3'],
    ['k1' => 'v2_1', 'k2' => 'v2_2', 'k3' => 'v2_3'],
    ['k1' => 'v3_1', 'k2' => 'v3_2', 'k3' => 'v3_3'],
];
$s = (new CsvWriter())->generate($items);
/*
k1,k2,k3
v1_1,v1_2,v1_3
v2_1,v2_2,v2_3
v3_1,v3_2,v3_3
*/
```

Need TSV? Change `CsvWriter` to `TsvWriter`.

Need to write to file?
```php
(new \Luchaninov\CsvWriter\CsvWriter())->write($filename, $items);
```
It uses generators so almost no memory is used.

Advanced Usage
--------------

Don't worry if you have non-strings as values. It will try to stringify them.
```php
$items = [
    ['k1' => new \DateTime('2000-01-02 03:04:05'), 'k2' => ['a', 'b'], 'k3' => ['a' => 'b', 'c' => 'd']],
    ['k1' => 1, 'k2' => -2.34, 'k3' => "test\ttest\rtest\ntest"],
    'this_will_be_skipped',
    ['k1' => '"v3_1', 'k2' => new FakeStringable(), 'k3' => new FakeJsonSerializable()],
];
$s = new CsvWriter())->generate($items);
/*
k1,k2,k3
2000-01-02 03:04:05,"[""a"",""b""]","{""a"":""b"",""c"":""d""}"
1,-2.34,test\\ttest\\rtest\\ntest
"""v3_1",fakestringable,{"k":"v"}
*/
```

It is "one item - one line" by default. `\t`, `\r`, `\n` are escaped and fit to one line.
If you need multiline then `$csvWriter->setOneline(false)`.

If your items are not arrays but JsonSerializable objects or JSON lines - that's ok.
If your object needs special serialization logic then pass `$normalizeFunction` param - callable that converts your
objects to array.

See `tests` for more examples. Also look at `src` - the logic is quite simple.
