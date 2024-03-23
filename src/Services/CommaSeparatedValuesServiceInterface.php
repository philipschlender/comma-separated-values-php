<?php

namespace PhilipSchlender\CommaSeparatedValues\Services;

interface CommaSeparatedValuesServiceInterface
{
    /**
     * @param array<int,array<string,string>> $records
     */
    public function arrayToCsv(array $records, bool $useHeader = true, string $separator = ',', string $enclosure = '"', string $escape = '\\', string $eol = "\n"): string;

    /**
     * @return array<int,array<string,string>>
     */
    public function csvToArray(string $csv, bool $hasHeader = true, string $separator = ',', string $enclosure = '"', string $escape = '\\'): array;
}
