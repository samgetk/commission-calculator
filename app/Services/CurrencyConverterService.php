<?php

namespace App\Services;

use App\Repositories\CurrencyConverterRepository;
use GuzzleHttp\Exception\GuzzleException;

class CurrencyConverterService
{
    /**
     * @param CurrencyConverterRepository $currencyConverterRepository
     */
    public function __construct(protected CurrencyConverterRepository $currencyConverterRepository)
    {
    }

    /**
     * @param $amount
     * @param $fromCurrency
     * @param $toCurrency
     * @return float|int|mixed
     * @throws GuzzleException
     */
    public function convert($amount, $fromCurrency, $toCurrency)
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $exchangeRate = $this->getExchangeRate($fromCurrency, $toCurrency);
        return $amount * $exchangeRate;
    }

    /**
     * @param $fromCurrency
     * @param $toCurrency
     * @return float|int|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getExchangeRate($fromCurrency, $toCurrency)
    {
        if ($fromCurrency === 'EUR') {
            return $this->currencyConverterRepository->fetchRate($toCurrency);
        } elseif ($toCurrency === 'EUR') {
            return 1 / $this->currencyConverterRepository->fetchRate($fromCurrency);
        } else {
            $rateFromEur = 1 / $this->currencyConverterRepository->fetchRate($fromCurrency);
            $rateToEur = $this->currencyConverterRepository->fetchRate($toCurrency);
            return $rateFromEur / $rateToEur;
        }
    }


}
