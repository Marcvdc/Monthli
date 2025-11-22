#!/bin/bash

echo "🧪 Testing DEGIRO CSV Import Functionality"
echo ""

# Create portfolio if none exists
echo "📁 Creating test portfolio..."
cd docker && docker-compose exec app php artisan tinker --execute="
\$portfolio = App\\Models\\Portfolio::firstOrCreate(
    ['name' => 'Test Portfolio'],
    ['base_currency' => 'EUR', 'snapshot_day' => 1]
);
echo 'Portfolio: ' . \$portfolio->name . ' (ID: ' . \$portfolio->id . ')' . PHP_EOL;
"

echo ""
echo "📊 Initial counts..."
cd docker && docker-compose exec app php artisan tinker --execute="
echo 'Transactions: ' . App\\Models\\Transaction::count() . PHP_EOL;
echo 'Positions: ' . App\\Models\\Position::count() . PHP_EOL;
"

echo ""
echo "🔄 Testing CSV import..."
cd docker && docker-compose exec app php artisan tinker --execute="
\$portfolio = App\\Models\\Portfolio::first();
\$service = new App\\Services\\DegiroImportService();
\$csvPath = '/var/www/tests/Fixtures/csv/degiro_sample.csv';
\$results = \$service->importCsv(\$csvPath, \$portfolio);
echo 'Import Results:' . PHP_EOL;
echo '- Success: ' . \$results['success'] . PHP_EOL;
echo '- Duplicates: ' . \$results['duplicates'] . PHP_EOL;
echo '- Errors: ' . count(\$results['errors']) . PHP_EOL;
if (!empty(\$results['errors'])) {
    echo 'Error details:' . PHP_EOL;
    foreach (\$results['errors'] as \$error) {
        echo '  - ' . \$error . PHP_EOL;
    }
}
"

echo ""
echo "📈 Final counts..."
cd docker && docker-compose exec app php artisan tinker --execute="
echo 'Transactions: ' . App\\Models\\Transaction::count() . PHP_EOL;
echo 'Positions: ' . App\\Models\\Position::count() . PHP_EOL;
"

echo ""
echo "🔍 Sample transactions..."
cd docker && docker-compose exec app php artisan tinker --execute="
App\\Models\\Transaction::orderBy('executed_at', 'desc')->take(3)->get()->each(function(\$t) {
    echo \$t->executed_at->format('M j, Y') . ' | ' . \$t->type . ' | ' . (\$t->symbol ?? 'N/A') . ' | ' . \$t->quantity . ' @ ' . \$t->price . PHP_EOL;
});
"

echo ""
echo "✅ Test completed!"
