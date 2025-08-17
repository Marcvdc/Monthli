<?php

declare(strict_types=1);

use Monthli\ReportGenerator;
use PHPUnit\Framework\TestCase;

class ReportGeneratorTest extends TestCase
{
    public function test_pdf_render_with_demo_data(): void
    {
        $snapshots = [
            ['month' => '2024-01', 'gross_value' => 1000, 'inflows' => 100, 'mom_pct' => 0.1, 'ytd' => 100],
            ['month' => '2024-02', 'gross_value' => 1100, 'inflows' => 50, 'mom_pct' => 0.05, 'ytd' => 150],
        ];

        $generator = new ReportGenerator;
        $pdf = $generator->generate($snapshots);

        $this->assertNotEmpty($pdf);
        $this->assertStringContainsString('%PDF', $pdf);
    }
}
