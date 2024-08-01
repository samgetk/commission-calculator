<?php

namespace App\Repositories;

use Exception;
use GuzzleHttp\Client;

class CurrencyConverterRepository
{
    /**
     * @var Client
     */
    protected $httpClient;

    /**
     *
     */
    public function __construct()
    {
        $this->httpClient = new Client();
    }


    /**
     * @param $currency
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function fetchRate($currency)
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
                throw new Exception("Exchange rate for {$currency} not found. Please check you API KEY!");
            }
            return $data['rates'][$currency];
        } catch (Exception $e) {
            throw new Exception("Error fetching exchange rate: " . $e->getMessage());
        }
    }
}
