<?php

namespace Monthli;

class SnapshotExporter
{
    /**
     * Export snapshot data to CSV format.
     *
     * @param array<int,array{month:string,gross_value:float,inflows:float,mom_pct:float,ytd:float}> $snapshots
     */
    public function export(array $snapshots): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['month', 'gross_value', 'inflows', 'mom_pct', 'ytd']);
        foreach ($snapshots as $snapshot) {
            fputcsv($handle, [
                $snapshot['month'],
                $snapshot['gross_value'],
                $snapshot['inflows'],
                $snapshot['mom_pct'],
                $snapshot['ytd'],
            ]);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        return $csv === false ? '' : $csv;
    }
}

