<?php

namespace App\Console\Commands;

use App\Libs\BusinessClient;
use App\Libs\PrivateClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CalculateCommissionOfCurrencyTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:currency_transaction_commission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate commission of currency transactions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $response = Http::get('https://developers.paysera.com/tasks/api/currency-exchange-rates');
        $currencyRates = json_decode($response->body(), true)['rates'];

        $fields = [
            'date',
            'client_id',
            'client_type',
            'transaction_type',
            'amount',
            'currency'
        ];

        $row = 1;

        $fees = [];

        if (($handle = fopen(public_path('example.csv'), "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $row++;
                $transactions = $this->formData($fields, $data);

                $clientType = $transactions['client_type'];
                $amount = $transactions['amount']/$currencyRates[$transactions['currency']];
                $transactions['transaction_type'] === 'deposit' ? $percent = 0.03 : $percent = 0.5;

                if ($clientType === 'business') {
                    $businessClient = new BusinessClient($amount, $percent);
                    $result = $businessClient->calculateTransactionFee();
                }
                else {
                    $privateClient = new PrivateClient($transactions, $currencyRates, $amount);
                    $result = $privateClient->calculateTransactionFee();
                }
                array_push($fees, $result);
            }
        }
        fclose($handle);
        dd($fees);
    }

    public function formData($fields, $data)
    {
        $result = [];
        $length = count($data);

        for ($i=0; $i < $length; $i++) {
            $result[$fields[$i]] = $data[$i];
        }

        return $result;
    }
}
