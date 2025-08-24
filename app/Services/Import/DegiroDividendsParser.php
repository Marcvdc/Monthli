<?php

namespace App\Services\Import;

class DegiroDividendsParser
{
    /**
     * @return array<string, mixed>
     */
    public function parse(string $csv): array
    {
        $rows = [];
        $errors = [];

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $csv);
        rewind($stream);

        $header = fgetcsv($stream, 0, ',', '"', '\\');
        $expected = ['Date', 'Product', 'Amount'];
        $unknown = array_diff($header, $expected);

        foreach ($unknown as $column) {
            $errors[] = "Unknown column: {$column}";
        }

        while (($data = fgetcsv($stream, 0, ',', '"', '\\')) !== false) {
            if (count($data) === 1 && $data[0] === null) {
                continue;
            }

            $row = array_combine($header, $data);
            if (empty($row['Date']) || empty($row['Product'])) {
                $errors[] = 'Invalid row: '.implode(',', $data);

                continue;
            }

            $rows[] = [
                'date' => $row['Date'],
                'product' => $row['Product'],
                'amount' => (float) $row['Amount'],
                'hash' => md5($row['Date'].$row['Product'].$row['Amount']),
            ];
        }

        fclose($stream);

        return ['rows' => $rows, 'errors' => $errors];
    }
}
