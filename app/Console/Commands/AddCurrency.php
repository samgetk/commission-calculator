<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AddCurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:add {code} {decimal_places}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new currency to the commission configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $code = strtoupper($this->argument('code'));
        $decimalPlaces = (int)$this->argument('decimal_places');

        $configFilePath = config_path('commission.php');

        $config = include $configFilePath;

        $config['currencies'][$code] = $decimalPlaces;
        $newConfigContent = '<?php return ' . var_export($config, true) . ';';

        File::put($configFilePath, $newConfigContent);

        $this->info("Currency {$code} with {$decimalPlaces} decimal places added successfully.");

    }
}
