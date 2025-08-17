<?php

declare(strict_types=1);

use Monthli\SnapshotExporter;
use PHPUnit\Framework\TestCase;

class SnapshotExporterTest extends TestCase
{
    public function test_exports_csv(): void
    {
        $snapshots = [
            ['month' => '2024-01', 'gross_value' => 1000, 'inflows' => 100, 'mom_pct' => 0.1, 'ytd' => 100],
        ];

        $csv = (new SnapshotExporter)->export($snapshots);

        $this->assertStringContainsString('month,gross_value,inflows,mom_pct,ytd', $csv);
        $this->assertStringContainsString('2024-01,1000,100,0.1,100', $csv);
    }
}
