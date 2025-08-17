<?php

namespace Monthli;

use Dompdf\Dompdf;
use Jenssegers\Blade\Blade;

class ReportGenerator
{
    private Blade $blade;

    public function __construct()
    {
        $views = __DIR__.'/../resources/views';
        $cache = sys_get_temp_dir().'/monthli_blade_cache';
        if (! is_dir($cache)) {
            mkdir($cache, 0777, true);
        }
        $this->blade = new Blade($views, $cache);
    }

    /**
     * Render the HTML for the report.
     *
     * @param  array<int,array{month:string,gross_value:float,inflows:float,mom_pct:float,ytd:float}>  $snapshots
     */
    public function renderHtml(array $snapshots): string
    {
        return $this->blade->render('report', ['snapshots' => $snapshots]);
    }

    /**
     * Generate a PDF for the given snapshots.
     *
     * @param  array<int,array{month:string,gross_value:float,inflows:float,mom_pct:float,ytd:float}>  $snapshots
     */
    public function generate(array $snapshots): string
    {
        $dompdf = new Dompdf;
        $dompdf->loadHtml($this->renderHtml($snapshots));
        $dompdf->render();

        return $dompdf->output();
    }
}
