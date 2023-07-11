<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Price;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;

class importCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the csv file to database';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // $data[0]; // products.sku
        // $data[1]; // accounts.external_reference
        // $data[2]; // users.external_reference
        // $data[3]; // prices.quantity
        // $data[4]; // prices.value

        try {

            $file = fopen(storage_path() . "/files/import.csv", "r");

            $i = 0;

            while (($data = fgetcsv($file)) !== false) {
                // ignore header
                if($i === 0) {
                    $i++;
                    continue;
                }

                Price::create([
                    'product_id' => $this->getProduct($data[0]),
                    'account_id' => $this->getAccount($data[1]),
                    'user_id' => $this->getUser($data[2]),
                    'quantity' => $data[3],
                    'value' => $data[4]
                ]);

                $i++;
            }

            echo "File imported!";

        } catch(\ErrorException $e) {
            echo "Error: " . $e->getMessage();
        }

    }

    private function getProduct(mixed $value) : int | null
    {
        return Product::where('sku', $value)->value('id');
    }

    private function getAccount(mixed $value) : int | null
    {
        return Account::where('external_reference', $value)->value('id');
    }

    private function getUser(mixed $value) : int | null
    {
        return User::where('external_reference', $value)->value('id');
    }

}
