<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Price;
use App\Models\Product;
use ErrorException;
use http\Client\Response;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;
use stdClass;
use function PHPUnit\Framework\isEmpty;
use function PHPUnit\Framework\throwException;

class ProductPrices extends Controller
{
    const MESSAGES = [
        'successMessage' => 'We find a price',
        'errReadingFile' => 'Error reading the file',
        'errProdNotFind' => 'We don\'t find a price'
    ];

    public function getProductPrice(Request $request)
    {

        // validations
        $request->validate([
            'prod_code' => 'required',
            'account_id' => 'string'
        ]);

        // if prod_code not a array
        if(!is_array($request->prod_code)) {
            $request->prod_code = [$request->prod_code];
        }

        $feed = $this->readLiveFeed();
        // validation if feed is read successfully
        if (!$feed) {
            return Response()->json(['result' => self::MESSAGES['errReadingFile']], 500);
        }

        // exist account_id ?
        $ext_reference = isset($request->account_id) ? $this->getExtRef((int)$request->account_id) : null;

        $prices = [];

            foreach ($request->prod_code as $prod_code) {
                $prodFeed = $this->searchOnFeed($feed, $prod_code, $ext_reference);

                if(!$prodFeed) {
                    $prodDatabase = $this->searchOnDatabase($prod_code, $request->account_id);

                    if($prodDatabase) {
                        $prices[] = $prodDatabase;
                    }

                } else {
                    $prices[] = $prodFeed;
                }

            }

        // validate if we have prices from feed
        if (!empty($prices)) {
            return Response()->json([
                'result' => self::MESSAGES['successMessage'],
                'products_prices' => $prices
            ]);
        }


        return Response()->json(['result' => self::MESSAGES['errProdNotFind']], 404);

    }

    /**
     * Read the live feed
     * @return mixed
     */
    private function readLiveFeed(): mixed
    {

        // i'm using file_get_contents because i'm using a local file
        try {
            $feed = file_get_contents(storage_path('files/live_prices.json'));
        } catch (ErrorException $e) {
            return false;
        }

        return json_decode($feed);
    }

    /**
     * Search on feed for the product
     * @param $priceFeed
     * @param $prod_code
     * @param $account_id
     * @return stdClass|null
     */
    private function searchOnFeed($priceFeed, $prod_code, $account_id): stdClass|null
    {
        $result = [];

        foreach ($priceFeed as $price) {

            if ($account_id !== null) {

                if (isset($price->account) &&
                    $price->account === $account_id &&
                    $price->sku === $prod_code) {

                    $result[] = $price;

                }

            }

            // if don't exist account id search for public prices
            if (!isset($price->account) &&
                $price->sku === $prod_code) {

                $result[] = $price;
            }

        }

        if (count($result) < 1) {
            return null;
        }

        return $this->lowestPriceFeed($result);
    }

    /**
     * Get the lowest price
     * @param array $prices
     * @return array|int|mixed
     */
    private function lowestPriceFeed(array $prices): mixed
    {

        if (count($prices) < 1) {
            return $prices;
        }

        $lowerPrice = 0;

        foreach ($prices as $k => $price) {

            if ($k === 0) {
                $lowerPrice = $price;
                continue;
            }

            if ($lowerPrice->price > $price->price) {
                $lowerPrice = $price;
            }

        }

        return $lowerPrice;
    }

    /**
     * Search on database
     * @param mixed $prod_code
     * @param mixed $account_id
     * @return array|null
     */
    private function searchOnDatabase(mixed $prod_code, mixed $account_id): mixed
    {

            $pricesTable = Price::select('sku', 'value as price')
                ->join('products', 'products.id', 'prices.product_id')
                ->where('products.sku', $prod_code)
                ->orderBy('prices.value');

            if ($account_id !== null) {
                $pricesTable->leftJoin('accounts', 'accounts.id', 'prices.account_id')
                    ->where('accounts.id', (int) $account_id);
            }

            $choosedPrice = $pricesTable->first();

            if ($account_id === null) {
                $productTable = Product::select('sku', 'price')->where('sku', $prod_code)->first();

                if($choosedPrice === null || $productTable->price > $choosedPrice->price) {
                    $choosedPrice = $productTable;
                }

            }

        return !empty($choosedPrice) ? $choosedPrice : null;
    }

    /**
     * Get the external reference of account
     * @param int $account_id
     * @return mixed
     */
    private function getExtRef(int $account_id): mixed
    {
        return Account::where('id', $account_id)->value('external_reference');
    }

}
