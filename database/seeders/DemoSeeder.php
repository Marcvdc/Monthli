<?php

namespace Database\Seeders;

class DemoSeeder
{
    public function run(): array
    {
        return [
            'portfolio' => [
                ['symbol' => 'AAPL', 'quantity' => 10, 'price' => 150.00],
                ['symbol' => 'GOOG', 'quantity' => 5, 'price' => 2800.00],
            ],
        ];
    }
}
