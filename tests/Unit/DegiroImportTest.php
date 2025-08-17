<?php

namespace Tests\Unit;

use App\Services\Import\DegiroDetector;
use App\Services\Import\DegiroDividendsParser;
use App\Services\Import\DegiroPositionsParser;
use App\Services\Import\DegiroTransactionsParser;
use PHPUnit\Framework\TestCase;

class DegiroImportTest extends TestCase
{
    private string $fixturesPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixturesPath = __DIR__.'/../Fixtures/csv/degiro';
    }

    public function test_detector_identifies_files(): void
    {
        $detector = new DegiroDetector;

        $this->assertSame(DegiroDetector::TYPE_POSITIONS, $detector->detect($this->fixturesPath.'/positions.csv'));
        $this->assertSame(DegiroDetector::TYPE_TRANSACTIONS, $detector->detect($this->fixturesPath.'/transactions.csv'));
        $this->assertSame(DegiroDetector::TYPE_DIVIDENDS, $detector->detect($this->fixturesPath.'/dividends.csv'));
    }

    public function test_parse_positions(): void
    {
        $parser = new DegiroPositionsParser;
        $content = file_get_contents($this->fixturesPath.'/positions.csv');
        $result = $parser->parse($content);

        $this->assertCount(1, $result['rows']);
        $this->assertSame('ABC', $result['rows'][0]['product']);
        $this->assertSame(10, $result['rows'][0]['quantity']);
        $this->assertSame(md5('ABC10'), $result['rows'][0]['hash']);
        $this->assertEmpty($result['errors']);
    }

    public function test_parse_transactions(): void
    {
        $parser = new DegiroTransactionsParser;
        $content = file_get_contents($this->fixturesPath.'/transactions.csv');
        $result = $parser->parse($content);

        $this->assertCount(1, $result['rows']);
        $this->assertSame('2023-01-01', $result['rows'][0]['date']);
        $this->assertSame('buy', $result['rows'][0]['type']);
        $this->assertSame('ABC', $result['rows'][0]['product']);
        $this->assertSame(10, $result['rows'][0]['quantity']);
        $this->assertSame(100.0, $result['rows'][0]['price']);
        $this->assertSame(md5('2023-01-01ABC10100'), $result['rows'][0]['hash']);
        $this->assertEmpty($result['errors']);
    }

    public function test_parse_dividends(): void
    {
        $parser = new DegiroDividendsParser;
        $content = file_get_contents($this->fixturesPath.'/dividends.csv');
        $result = $parser->parse($content);

        $this->assertCount(1, $result['rows']);
        $this->assertSame('2023-01-01', $result['rows'][0]['date']);
        $this->assertSame('ABC', $result['rows'][0]['product']);
        $this->assertSame(5.0, $result['rows'][0]['amount']);
        $this->assertSame(md5('2023-01-01ABC5'), $result['rows'][0]['hash']);
        $this->assertEmpty($result['errors']);
    }

    public function test_error_bucket_for_unknown_columns(): void
    {
        $csv = "Product,Quantity,Extra\nABC,10,foo";
        $parser = new DegiroPositionsParser;
        $result = $parser->parse($csv);

        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('Unknown column: Extra', $result['errors'][0]);
    }
}
