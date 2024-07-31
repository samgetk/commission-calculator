<?php

namespace App\Services;

use GuzzleHttp\Client;
use Exception;

class CurrencyConverter
{
    protected $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client();
    }

    public function convert($amount, $fromCurrency, $toCurrency)
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $exchangeRate = $this->getExchangeRate($fromCurrency, $toCurrency);
        return $amount * $exchangeRate;
    }

    protected function getExchangeRate($fromCurrency, $toCurrency)
    {
        if ($fromCurrency === 'EUR') {
            return $this->fetchRate($toCurrency);
        } elseif ($toCurrency === 'EUR') {
            return 1 / $this->fetchRate($fromCurrency);
        } else {
            $rateFromEur = 1 / $this->fetchRate($fromCurrency);
            $rateToEur = $this->fetchRate($toCurrency);
            return $rateFromEur / $rateToEur;
        }
    }

    protected function fetchRate($currency)
    {
        try {
            $apiKey = env('EXCHANGE_RATE_API_KEY');
            $response = $this->httpClient->get(env('EXCHANGE_RATE_API_URL'), [
                'query' => [
                    'access_key' => $apiKey,
                ],
            ]);
            $data = json_decode($response->getBody(), true);
            if (!isset($data['rates'][$currency])) {
                throw new Exception("Exchange rate for {$currency} not found.");
            }
            return $data['rates'][$currency];
        } catch (Exception $e) {
            throw new Exception("Error fetching exchange rate: " . $e->getMessage());
        }
    }
}
