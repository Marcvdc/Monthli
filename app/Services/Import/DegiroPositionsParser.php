<?php

namespace App\Services\Import;

class DegiroPositionsParser
{
    public function parse(string $csv): array
    {
        $rows = [];
        $errors = [];

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $csv);
        rewind($stream);

        $header = fgetcsv($stream, 0, ',', '"', '\\');
        $expected = ['Product', 'Quantity'];
        $unknown = array_diff($header, $expected);

        foreach ($unknown as $column) {
            $errors[] = "Unknown column: {$column}";
        }

        while (($data = fgetcsv($stream, 0, ',', '"', '\\')) !== false) {
            if (count($data) === 1 && $data[0] === null) {
                continue;
            }

            $row = array_combine($header, $data);
            if (empty($row['Product']) || empty($row['Quantity'])) {
                $errors[] = 'Invalid row: '.implode(',', $data);
                continue;
            }

            $rows[] = [
                'product' => $row['Product'],
                'quantity' => (int) $row['Quantity'],
                'hash' => md5($row['Product'].$row['Quantity']),
            ];
        }

        fclose($stream);

        return ['rows' => $rows, 'errors' => $errors];
    }
}
