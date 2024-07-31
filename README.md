# Commission Calculation System

## Requirements

- PHP 8.2+
- Composer
- Laravel 11.9

## Installation

1. Clone the repository:
    ```sh
    git clone https://github.com/samgetk/commission-calculator.git
    ```
2. Navigate to the project directory:
    ```sh
    cd commission-calculator
    ```
3. Install dependencies:
    ```sh
    composer install
    ```
4. Copy the example environment file to create your `.env` file:
    ```sh
    cp .env.example .env
   ```
5. Environment variables
    - To use the exchange API correctly, please set the correct values in your `.env` file:
    ```env
    EXCHANGE_RATE_API_URL=https://api.exchangeratesapi.io/latest
    EXCHANGE_RATE_API_KEY=<EXCHANGE_RATE_API_KEY>
    ```
   Make sure to replace `<EXCHANGE_RATE_API_KEY>` with your actual API key.

## Usage

1. Calculate commissions from an input CSV file:
    ```sh
    php artisan calculate:commissions input.csv
    ```

## Testing

1. Run the tests:
    ```sh
    php artisan test
    ```

## Current Currencies

The system currently supports the following currencies with their respective decimal places:
- `EUR` (Euro) - 2 decimal places
- `USD` (US Dollar) - 2 decimal places
- `JPY` (Japanese Yen) - 0 decimal places

**Add a new currency**:
```sh
php artisan currency:add <CURRENCY_CODE> <DECIMAL_PLACES>
```
Example:
```sh
php artisan currency:add GBP 2
```
This command adds a new currency with the specified decimal places to the commission configuration.

