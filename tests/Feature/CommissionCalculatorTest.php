<?php

namespace Tests\Feature;

use League\Csv\Reader;
use Tests\TestCase;
use App\Services\CommissionCalculatorService;
use App\Services\CurrencyConverterService;
use Illuminate\Support\Facades\Config;
use Mockery;

class CommissionCalculatorTest extends TestCase
{
    protected $currencyConverterMock;
    protected $commissionCalculator;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the CurrencyConverterService
        $this->currencyConverterMock = Mockery::mock(CurrencyConverterService::class);
        $this->currencyConverterMock->shouldReceive('convert')->andReturnUsing(function ($amount, $from, $to) {
            // Simulate conversion rates
            if ($from === 'USD' && $to === 'EUR') {
                return $amount * 0.85; // 1 USD = 0.85 EUR
            } elseif ($from === 'EUR' && $to === 'USD') {
                return $amount * 1.15; // 1 EUR = 1.15 USD
            } elseif ($from === 'JPY' && $to === 'EUR') {
                return $amount * 0.0077; // 1 JPY = 0.0077 EUR
            } elseif ($from === 'EUR' && $to === 'JPY') {
                return $amount * 130; // 1 EUR = 130 JPY
            }
            return $amount;
        });

        // Mock the config
        Config::shouldReceive('get')->with('commission')->andReturn([
            'deposit_fee' => 0.0003,
            'private_withdraw_fee' => 0.003,
            'business_withdraw_fee' => 0.005,
            'currencies' => [
                'EUR' => 2,
                'USD' => 2,
                'JPY' => 0,
            ],
        ]);

        $this->commissionCalculator = new CommissionCalculatorService($this->currencyConverterMock);
    }

    public function testCalculateCommission()
    {
        $csvFilePath = base_path('input.csv');
        $csv = Reader::createFromPath($csvFilePath, 'r');
        $csv->setHeaderOffset(null); // No header row
        $records = $csv->getRecords();

        foreach ($records as $record) {
            $operations[] = $record;
        }


        $expectedCommissions = [
            0.60,  // 1200 EUR - (1000 EUR free) = 200 EUR, 0.3% of 200 EUR = 0.60 EUR
            3.00,  // 1000 EUR - 1000 EUR free limit exceeded, 0.3% of 1000 EUR = 3.00 EUR
            0.00,  // within 1000 EUR free limit for the new week
            0.06,  // 0.03% of 200 EUR = 0.06 EUR
            1.50,  // 0.5% of 300 EUR = 1.50 EUR
            0.00,  // 30000 JPY = 231 EUR, within 1000 EUR free limit
            0.70,  // 0.3% of 300 EUR in EUR, 30000 JPY + 300 EUR exceeds 1000 EUR free limit
            0.30,  // 100 USD = 85 EUR, within 1000 EUR free limit
            0.30,  // 0.3% of 100 EUR = 0.30 EUR
            3.00,  // 0.03% of 10000 EUR = 3.00 EUR
            0.00,  // within 1000 EUR free limit
            0.00,  // within 1000 EUR free limit
            8619.0, // 3000000 JPY exceeding 1000 EUR free limit
        ];

        $commissions = $this->commissionCalculator->calculateCommission($operations);
        $this->assertEquals($expectedCommissions, $commissions);
    }
}
