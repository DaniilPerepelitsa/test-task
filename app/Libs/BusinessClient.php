<?php


namespace App\Libs;


class BusinessClient
{
    /**
     * @var float
     *
     *Percentage that is charged from the client during the transaction
     */
    private $percent;

    /**
     * @var float
     *
     *Transaction amount
     */
    private $amount;

    public function __construct($amount, $percent)
    {
        $this->amount = $amount;
        $this->percent = $percent;
    }

    public function calculateTransactionFee() {
        return $this->amount/100*$this->percent;
    }
}
