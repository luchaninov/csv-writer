<?php /** @noinspection SpellCheckingInspection */

namespace Luchaninov\CsvWriter;

use DateTimeInterface;
use Generator;
use Gupalo\Json\Json;
use JsonSerializable;
use Stringable;

class CsvWriter
{
    protected string $separator = ',';
    protected string $delimiter = '"';
    protected string $lineSeparator = "\n";
    protected bool $oneline = true;

    public function write(string $filename, iterable $items, ?callable $normalizeFunction = null): void
    {
        $this->ensureDir(dirname($filename));

        $rows = $this->generateRows($items, $normalizeFunction);

        $isFirst = true;
        $f = fopen($filename, 'wb');
        foreach ($rows as $row) {
            fwrite($f, ($isFirst ? '' : $this->lineSeparator) . $row);
            if ($isFirst) {
                $isFirst = false;
            }
        }
        fclose($f);
    }

    public function generate(iterable $items, ?callable $normalizeFunction = null): string
    {
        return implode($this->lineSeparator, iterator_to_array($this->generateRows($items, $normalizeFunction)));
    }

    private function generateRows(iterable $items, ?callable $normalizeFunction = null): Generator
    {
        $keys = [];
        foreach ($items as $item) {
            $a = $normalizeFunction ? $normalizeFunction($item) : $this->normalize($item);
            if (!is_array($a) || empty($a)) {
                continue;
            }
            if (empty($keys)) {
                $keys = array_keys($a);

                $cols = [];
                foreach ($keys as $key) {
                    $cols[] = $this->normalizeCol($key);
                }
                yield implode($this->separator, $cols);
            }

            $cols = [];
            foreach ($keys as $key) {
                $cols[] = $this->normalizeCol($item[$key]);
            }

            yield implode($this->separator, $cols);
        }
    }

    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    public function setDelimiter(string $delimiter): self
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    public function setLineSeparator(string $lineSeparator): self
    {
        $this->lineSeparator = $lineSeparator;

        return $this;
    }

    public function setOneline(bool $oneline): self
    {
        $this->oneline = $oneline;

        return $this;
    }

    private function ensureDir(string $dirname): void
    {
        if (!is_dir($dirname) && !mkdir($dirname, 0777, true) && !is_dir($dirname)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dirname));
        }
    }

    private function normalize($item): array
    {
        if (is_string($item) && (str_starts_with($item, '{') || str_starts_with($item, '['))) {
            return Json::toArray($item);
        }

        if (is_array($item)) {
            return $item;
        }

        if ($item instanceof JsonSerializable) {
            return $item->jsonSerialize();
        }

        return [];
    }

    private function normalizeCol(mixed $item): string
    {
        $col = $item ?? '';
        if ($col instanceof DateTimeInterface) {
            $col = $col->format('Y-m-d H:i:s');
        } elseif ($col instanceof Stringable) {
            $col = $col->__toString();
        } elseif ($col instanceof JsonSerializable) {
            $col = $col->jsonSerialize();
        }
        if (is_array($col) || is_object($col)) {
            $col = Json::toString($col);
        } elseif (!is_string($col)) {
            $col = (string)$col;
        }

        if ($this->oneline) {
            $col = str_replace(["\t", "\r", "\n"], ['\\t', '\\r', '\\n'], $col);
            if (str_contains($col, $this->separator) || str_starts_with($col, $this->delimiter)) {
                $col = $this->delimiter.str_replace($this->delimiter, $this->delimiter.$this->delimiter, $col).$this->delimiter;
            }
        } elseif (
            str_starts_with($col, $this->delimiter) ||
            str_contains($col, $this->separator) ||
            str_contains($col, "\t") ||
            str_contains($col, "\r") ||
            str_contains($col, "\n")
        ) {
            $col = $this->delimiter.str_replace($this->delimiter, $this->delimiter.$this->delimiter, $col).$this->delimiter;
        }

        return $col;
    }
}
