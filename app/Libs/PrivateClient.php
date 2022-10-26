<?php


namespace App\Libs;

use Carbon\Carbon;

class PrivateClient
{
    /**
     * @var array
     *
     * Transaction information
     */
    private $params;

    /**
     * @var array
     *
     * List of current exchange rates
     */
    private $currencyRates;

    /**
     * @var float
     *
     *Transaction amount
     */
    private $amount;

    /**
     * @var float
     *
     *Transaction amount that has no commission
     */
    private $noCommissionAmount;

    /**
     * @var int
     *
     *Number of days with free commission
     */
    private $numFreeDays;

    public function __construct($params, $currencyRates, $amount)
    {
        $this->params = $params;
        $this->currencyRates = $currencyRates;
        $this->amount = $amount;
        $this->noCommissionAmount = 1000;
        $this->numFreeDays = 3;
    }

    public function calculateTransactionFee() {
        $transactionCounts = [];

        $clientId = $this->params['client_id'];
        $percent = $this->transactionPercentage();

        if(!isset($transactionCounts[$clientId])){
            $transactionCounts[$clientId] = [];
        }

        $transactionDate = Carbon::parse($this->params['date']);
        $weekOfYear = $transactionDate->year. '-' .$transactionDate->weekOfYear;

        if(!isset($transactionCounts[$clientId][$weekOfYear])){
            $transactionCounts[$clientId][$weekOfYear] = 0;
        }

        if ($transactionCounts[$clientId][$weekOfYear] > $this->numFreeDays) {
            $result = $this->amount/100*$percent;
        }
        else {
            $result = $this->amount >= $this->noCommissionAmount
                ? ($this->amount - $this->noCommissionAmount)/100*0.3
                : 0;
        }

        $transactionCounts[$clientId][$weekOfYear]++;

        return $result;
    }

    public function transactionPercentage() {
        return $this->params['transaction_type'] === 'deposit' ? $percent = 0.03 : $percent = 0.5;
    }
}
