<?php

namespace PhilipSchlender\CommaSeparatedValues\Tests;

use PhilipSchlender\CommaSeparatedValues\Exceptions\CommaSeparatedValuesException;
use PhilipSchlender\CommaSeparatedValues\Services\CommaSeparatedValuesService;
use PhilipSchlender\CommaSeparatedValues\Services\CommaSeparatedValuesServiceInterface;

class CommaSeparatedValuesServiceTest extends TestCase
{
    protected CommaSeparatedValuesServiceInterface $commaSeparatedValuesService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commaSeparatedValuesService = new CommaSeparatedValuesService();
    }

    public function testArrayToCsv(): void
    {
        $records = [
            [
                'A' => '1',
                'B' => '2',
                'C' => '3',
            ],
            [
                'A' => '4',
                'B' => '5',
                'C' => '6',
            ],
            [
                'A' => '7',
                'B' => '8',
                'C' => '9',
            ],
        ];

        $expectedCsv = "A,B,C\n1,2,3\n4,5,6\n7,8,9\n";

        $csv = $this->commaSeparatedValuesService->arrayToCsv($records);

        $this->assertIsString($csv);
        $this->assertEquals($expectedCsv, $csv);
    }

    public function testArrayToCsvNoHeader(): void
    {
        $records = [
            [
                'A' => '1',
                'B' => '2',
                'C' => '3',
            ],
            [
                'A' => '4',
                'B' => '5',
                'C' => '6',
            ],
            [
                'A' => '7',
                'B' => '8',
                'C' => '9',
            ],
        ];

        $expectedCsv = "1,2,3\n4,5,6\n7,8,9\n";

        $csv = $this->commaSeparatedValuesService->arrayToCsv($records, false);

        $this->assertIsString($csv);
        $this->assertEquals($expectedCsv, $csv);
    }

    public function testArrayToCsvEmptyRecords(): void
    {
        $records = [];

        $expectedCsv = '';

        $csv = $this->commaSeparatedValuesService->arrayToCsv($records);

        $this->assertIsString($csv);
        $this->assertEquals($expectedCsv, $csv);
    }

    public function testArrayToCsvDifferentNumberOfColumns(): void
    {
        $this->expectException(CommaSeparatedValuesException::class);
        $this->expectExceptionMessage('The records must have the same number of columns for each record.');

        $records = [
            [
                'A' => '1',
                'B' => '2',
                'C' => '3',
            ],
            [
                'A' => '4',
                'B' => '5',
            ],
            [
                'A' => '6',
            ],
        ];

        $this->commaSeparatedValuesService->arrayToCsv($records);
    }

    public function testArrayToCsvRecordsContainEol(): void
    {
        $records = [
            [
                'A' => "1\n2",
                'B' => "3\n4",
                'C' => "5\n6",
            ],
        ];

        $expectedCsv = "A,B,C\n\"1\n2\",\"3\n4\",\"5\n6\"\n";

        $csv = $this->commaSeparatedValuesService->arrayToCsv($records);

        $this->assertIsString($csv);
        $this->assertEquals($expectedCsv, $csv);
    }

    public function testCsvToArray(): void
    {
        $csv = "A,B,C\n1,2,3\n4,5,6\n7,8,9\n";

        $expectedRecords = [
            [
                'A' => '1',
                'B' => '2',
                'C' => '3',
            ],
            [
                'A' => '4',
                'B' => '5',
                'C' => '6',
            ],
            [
                'A' => '7',
                'B' => '8',
                'C' => '9',
            ],
        ];

        $records = $this->commaSeparatedValuesService->csvToArray($csv);

        $this->assertIsArray($records);
        $this->assertEquals($expectedRecords, $records);
    }

    public function testCsvToArrayNoHeader(): void
    {
        $csv = "1,2,3\n4,5,6\n7,8,9\n";

        $expectedRecords = [
            [
                'A' => '1',
                'B' => '2',
                'C' => '3',
            ],
            [
                'A' => '4',
                'B' => '5',
                'C' => '6',
            ],
            [
                'A' => '7',
                'B' => '8',
                'C' => '9',
            ],
        ];

        $records = $this->commaSeparatedValuesService->csvToArray($csv, false);

        $this->assertIsArray($records);
        $this->assertEquals($expectedRecords, $records);
    }

    public function testCsvToArrayEmptyCsv(): void
    {
        $csv = '';

        $expectedRecords = [];

        $records = $this->commaSeparatedValuesService->csvToArray($csv);

        $this->assertIsArray($records);
        $this->assertEquals($expectedRecords, $records);
    }

    public function testCsvToArrayDifferentNumberOfColumns(): void
    {
        $this->expectException(CommaSeparatedValuesException::class);
        $this->expectExceptionMessage('The records must have the same number of columns for each record.');

        $csv = "A,B,C\n1,2,3\n4,5\n6\n";

        $this->commaSeparatedValuesService->csvToArray($csv);
    }

    public function testCsvToArrayCsvContainEol(): void
    {
        $csv = "A,B,C\n\"1\n2\",\"3\n4\",\"5\n6\"\n";

        $expectedRecords = [
            [
                'A' => "1\n2",
                'B' => "3\n4",
                'C' => "5\n6",
            ],
        ];

        $records = $this->commaSeparatedValuesService->csvToArray($csv);

        $this->assertIsArray($records);
        $this->assertEquals($expectedRecords, $records);
    }

    public function testCsvToArrayDefaultHeader(): void
    {
        $csv = "1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32\n";

        $expectedRecords = [
            [
                'A' => '1',
                'B' => '2',
                'C' => '3',
                'D' => '4',
                'E' => '5',
                'F' => '6',
                'G' => '7',
                'H' => '8',
                'I' => '9',
                'J' => '10',
                'K' => '11',
                'L' => '12',
                'M' => '13',
                'N' => '14',
                'O' => '15',
                'P' => '16',
                'Q' => '17',
                'R' => '18',
                'S' => '19',
                'T' => '20',
                'U' => '21',
                'V' => '22',
                'W' => '23',
                'X' => '24',
                'Y' => '25',
                'Z' => '26',
                'AA' => '27',
                'AB' => '28',
                'AC' => '29',
                'AD' => '30',
                'AE' => '31',
                'AF' => '32',
            ],
        ];

        $records = $this->commaSeparatedValuesService->csvToArray($csv, false);

        $this->assertIsArray($records);
        $this->assertEquals($expectedRecords, $records);
    }
}
