<?php

namespace App\Services\Import;

class DegiroDetector
{
    public const TYPE_POSITIONS = 'positions';

    public const TYPE_TRANSACTIONS = 'transactions';

    public const TYPE_DIVIDENDS = 'dividends';

    /**
     * Detect the type of a DEGIRO CSV based on the header columns.
     */
    public function detect(string $path): ?string
    {
        if (! is_readable($path)) {
            return null;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return null;
        }

        $header = trim((string) fgets($handle));
        fclose($handle);

        if ($header === '') {
            return null;
        }

        $fields = array_map(
            static fn (?string $value): string => strtolower($value ?? ''), 
            str_getcsv($header, ',', '"', '\\')
        );

        if (in_array('type', $fields, true) && in_array('price', $fields, true)) {
            return self::TYPE_TRANSACTIONS;
        }

        if (in_array('amount', $fields, true)) {
            return self::TYPE_DIVIDENDS;
        }

        if (in_array('quantity', $fields, true)) {
            return self::TYPE_POSITIONS;
        }

        return null;
    }
}
