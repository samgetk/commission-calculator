<?php

namespace App\Console\Commands;

use App\Services\CommissionCalculatorService;
use App\Services\CurrencyConverterService;
use Illuminate\Console\Command;
use League\Csv\Reader;

class CalculateCommission extends Command
{

    /**
     * @param CommissionCalculatorService $commissionCalculator
     */
    public function __construct(protected CommissionCalculatorService $commissionCalculator)
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:commissions {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate commissions based on input CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $csvFile = $this->argument('file');
        $operations = $this->loadOperationsFromCSV($csvFile);

        if (empty($operations)) {
            $this->error("No operations found or CSV file is empty.");
            return 1;
        }

        try {
            $commissions = $this->commissionCalculator->calculateCommission($operations);

            $this->info('Calculated Commissions:');
            foreach ($commissions as $commission) {
                $this->line(number_format($commission, 2));
            }
        } catch (\Exception $e) {
            $this->error("Error calculating commissions: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * @param $csvFile
     * @return array
     */
    private function loadOperationsFromCSV($csvFile)
    {
        $operations = [];
        if (!file_exists($csvFile)) {
            $this->error("CSV file not found: {$csvFile}");
            return $operations;
        }

        try {
            // Create a CSV reader instance
            $csv = Reader::createFromPath($csvFile, 'r');
            $csv->setHeaderOffset(null); // No header row

            // Fetch records from the CSV
            $records = $csv->getRecords();

            foreach ($records as $record) {
                $operations[] = $record;
            }
        } catch (\Exception $e) {
            $this->error("Error reading CSV file: " . $e->getMessage());
        }

        return $operations;
    }
}
