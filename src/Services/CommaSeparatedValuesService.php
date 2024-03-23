<?php

namespace PhilipSchlender\CommaSeparatedValues\Services;

use PhilipSchlender\CommaSeparatedValues\Exceptions\CommaSeparatedValuesException;

class CommaSeparatedValuesService implements CommaSeparatedValuesServiceInterface
{
    /**
     * @param array<int,array<string,string>> $records
     *
     * @throws CommaSeparatedValuesException
     */
    public function arrayToCsv(array $records, bool $useHeader = true, string $separator = ',', string $enclosure = '"', string $escape = '\\', string $eol = "\n"): string
    {
        if (empty($records)) {
            return '';
        }

        try {
            $handle = $this->openTemporaryFile();

            if ($useHeader) {
                $header = array_keys($records[0]);

                $this->writeRecordToTemporaryFile($handle, $header, $separator, $enclosure, $escape, $eol);
            }

            $numberOfColumns = count($records[0]);

            foreach ($records as $record) {
                $numberOfRecordColumns = count($record);

                if ($numberOfRecordColumns !== $numberOfColumns) {
                    throw new CommaSeparatedValuesException('The records must have the same number of columns for each record.');
                }

                $this->writeRecordToTemporaryFile($handle, $record, $separator, $enclosure, $escape, $eol);
            }

            $this->rewindTemporaryFile($handle);

            $csv = $this->readContentFromTemporaryFile($handle);
        } catch (\Throwable $throwable) {
            throw $throwable;
        } finally {
            if (isset($handle)) {
                $this->closeTemporaryFile($handle);
            }
        }

        return $csv;
    }

    /**
     * @return array<int,array<string,string>>
     *
     * @throws CommaSeparatedValuesException
     */
    public function csvToArray(string $csv, bool $hasHeader = true, string $separator = ',', string $enclosure = '"', string $escape = '\\'): array
    {
        if (empty($csv)) {
            return [];
        }

        try {
            $handle = $this->openTemporaryFile();

            $this->writeContentToTemporaryFile($handle, $csv);

            $this->rewindTemporaryFile($handle);

            if ($hasHeader) {
                $header = $this->readRecordFromTemporaryFile($handle, $separator, $enclosure, $escape);
            } else {
                $record = $this->readRecordFromTemporaryFile($handle, $separator, $enclosure, $escape);

                $header = $this->getDefaultHeader(count($record));

                $this->rewindTemporaryFile($handle);
            }

            $numberOfColumns = count($header);

            $records = [];

            while (true) {
                $record = $this->readRecordFromTemporaryFile($handle, $separator, $enclosure, $escape);

                if (empty($record)) {
                    break;
                }

                $numberOfRecordColumns = count($record);

                if ($numberOfRecordColumns !== $numberOfColumns) {
                    throw new CommaSeparatedValuesException('The records must have the same number of columns for each record.');
                }

                $records[] = array_combine($header, $record);
            }
        } catch (\Throwable $throwable) {
            throw $throwable;
        } finally {
            if (isset($handle)) {
                $this->closeTemporaryFile($handle);
            }
        }

        return $records;
    }

    /**
     * @return array<int,string>
     */
    protected function getDefaultHeader(int $numberOfColumns): array
    {
        $header = [];

        for ($i = 0; $i < $numberOfColumns; ++$i) {
            $header[] = $this->getColumnName($i);
        }

        return $header;
    }

    protected function getColumnName(int $index): string
    {
        $columnName = '';

        do {
            $character = chr(65 + ($index % 26));
            $columnName = sprintf('%s%s', $character, $columnName);

            $index = ((int) floor($index / 26)) - 1;

            if (0 === $index) {
                $character = chr(65);
                $columnName = sprintf('%s%s', $character, $columnName);
            }
        } while ($index > 0);

        return $columnName;
    }

    /**
     * @return resource
     *
     * @throws CommaSeparatedValuesException
     */
    protected function openTemporaryFile()
    {
        $handle = fopen('php://temp/', 'r+');

        if (!is_resource($handle)) {
            throw new CommaSeparatedValuesException('Failed to open temporary file.');
        }

        return $handle;
    }

    /**
     * @param resource $handle
     *
     * @throws CommaSeparatedValuesException
     */
    protected function readContentFromTemporaryFile($handle): string
    {
        $content = '';

        while (!feof($handle)) {
            $contentChunk = fread($handle, 1024);

            if (!is_string($contentChunk)) {
                throw new CommaSeparatedValuesException('Failed to read content from temporary file.');
            }

            $content .= $contentChunk;
        }

        return $content;
    }

    /**
     * @param resource $handle
     *
     * @throws CommaSeparatedValuesException
     */
    protected function writeContentToTemporaryFile($handle, string $content): void
    {
        if (!is_int(fwrite($handle, $content))) {
            throw new CommaSeparatedValuesException('Failed to write content to temporary file.');
        }
    }

    /**
     * @param resource $handle
     *
     * @return array<int,string>
     *
     * @throws CommaSeparatedValuesException
     */
    protected function readRecordFromTemporaryFile($handle, string $separator, string $enclosure, string $escape): array
    {
        if (feof($handle)) {
            return [];
        }

        $record = fgetcsv($handle, null, $separator, $enclosure, $escape);

        if (!is_array($record)) {
            throw new CommaSeparatedValuesException('Failed to read row from temporary file.');
        }

        if (1 === count($record) && is_null($record[0])) {
            throw new CommaSeparatedValuesException('Failed to read row from temporary file.');
        }

        return $record;
    }

    /**
     * @param resource                 $handle
     * @param array<int|string,string> $record
     *
     * @throws CommaSeparatedValuesException
     */
    protected function writeRecordToTemporaryFile($handle, array $record, string $separator, string $enclosure, string $escape, string $eol): void
    {
        if (!is_int(fputcsv($handle, $record, $separator, $enclosure, $escape, $eol))) {
            throw new CommaSeparatedValuesException('Failed to write row to temporary file.');
        }
    }

    /**
     * @param resource $handle
     *
     * @throws CommaSeparatedValuesException
     */
    protected function rewindTemporaryFile($handle): void
    {
        if (!rewind($handle)) {
            throw new CommaSeparatedValuesException('Failed to rewind temporary file.');
        }
    }

    /**
     * @param resource $handle
     *
     * @throws CommaSeparatedValuesException
     */
    protected function closeTemporaryFile($handle): void
    {
        if (!fclose($handle)) {
            throw new CommaSeparatedValuesException('Failed to close temporary file.');
        }
    }
}
