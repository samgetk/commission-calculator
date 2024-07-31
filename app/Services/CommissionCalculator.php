<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class CommissionCalculator
{
    protected $config;
    protected $currencyConverter;

    public function __construct(CurrencyConverter $currencyConverter)
    {
        $this->config = Config::get('commission');
        $this->currencyConverter = $currencyConverter;
    }

    public function calculateCommission(array $operations)
    {
        $commissions = [];
        $weeklyWithdrawals = [];

        foreach ($operations as $operation) {
            [$date, $userId, $userType, $operationType, $amount, $currency] = $operation;
            $amount = (float) $amount;
            if ($operationType === 'deposit') {
                $commissions[] = $this->calculateDepositCommission($amount, $currency);
            } else {
                $commissions[] = $this->calculateWithdrawCommission($date, $userId, $userType, $amount, $currency, $weeklyWithdrawals);
            }
        }

        return $commissions;
    }

    protected function calculateDepositCommission($amount, $currency)
    {
        $fee = $amount * $this->config['deposit_fee'];
        return $this->roundUp($fee, $currency);
    }

    protected function calculateWithdrawCommission($date, $userId, $userType, $amount, $currency, &$weeklyWithdrawals)
    {
        $amountInEur = $this->currencyConverter->convert($amount, $currency, 'EUR');
        $week = date('oW', strtotime($date));
        $key = "{$userId}-{$week}";

        // Initialize weekly withdrawals if not set
        if (!isset($weeklyWithdrawals[$key])) {
            $weeklyWithdrawals[$key] = ['count' => 0, 'amount' => 0];
        }

        // Update the withdrawal count and amount
        $weeklyWithdrawals[$key]['count']++;
        $weeklyWithdrawals[$key]['amount'] += $amountInEur;

        $limitAmount = 1000.00;
        $limitCount = 3;

        if ($userType === 'private') {
            if ($weeklyWithdrawals[$key]['count'] <= $limitCount) {
                if ($weeklyWithdrawals[$key]['amount'] <= $limitAmount) {
                    $fee = 0;
                } else {
                    $exceededAmount = $weeklyWithdrawals[$key]['amount'] - $limitAmount;
                    if ($exceededAmount > $amountInEur) {
                        $fee = $amountInEur * $this->config['private_withdraw_fee'];
                    } else {
                        $fee = $exceededAmount * $this->config['private_withdraw_fee'];
                    }
                }
            } else {
                $fee = $amountInEur * $this->config['private_withdraw_fee'];
            }
        } else {
            $fee = $amountInEur * $this->config['business_withdraw_fee'];
        }

        $feeInOriginalCurrency = $this->currencyConverter->convert($fee, 'EUR', $currency);
        return $this->roundUp($feeInOriginalCurrency, $currency);
    }


    protected function roundUp($amount, $currency)
    {
        $decimalPlaces = $this->config['currencies'][$currency];
        return ceil($amount * pow(10, $decimalPlaces)) / pow(10, $decimalPlaces);
    }
}
