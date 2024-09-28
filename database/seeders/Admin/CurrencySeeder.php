<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\Currency;
use App\Models\Admin\CurrencyHasNetwork;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies = array(
            array('admin_id' => '1','name' => 'Bitcoin','code' => 'BTC','symbol' => '₿','type' => 'CRYPTO','flag' => 'seeder/bitcoin.webp','rate' => '1.00000000','sender' => '1','receiver' => '1','default' => '1','status' => '1','created_at' => '2023-11-30 04:40:46','updated_at' => '2023-11-30 04:54:29'),
            array('admin_id' => '1','name' => 'Ethereum','code' => 'ETH','symbol' => 'Ξ','type' => 'CRYPTO','flag' => 'seeder/ethereum.webp','rate' => '15.00000000','sender' => '1','receiver' => '1','default' => '0','status' => '1','created_at' => '2023-11-30 04:52:47','updated_at' => '2023-11-30 04:55:31'),
            array('admin_id' => '1','name' => 'Tether','code' => 'USDT','symbol' => '₮','type' => 'CRYPTO','flag' => 'seeder/tether.webp','rate' => '38017.77000000','sender' => '1','receiver' => '1','default' => '0','status' => '1','created_at' => '2023-11-30 04:57:30','updated_at' => '2023-11-30 04:57:32'),
            array('admin_id' => '1','name' => 'Dogecoin','code' => 'DOGE','symbol' => 'Ɖ','type' => 'CRYPTO','flag' => 'seeder/doge-coin.webp','rate' => '466020.56000000','sender' => '1','receiver' => '1','default' => '0','status' => '1','created_at' => '2023-11-30 04:58:49','updated_at' => '2023-11-30 04:59:08')
        );

        Currency::insert($currencies);

        //currency has network
        $currency_has_networks = array(
            array('currency_id' => '1','network_id' => '5','fees' => '10.0000000000000000','created_at' => '2023-11-30 04:54:29','updated_at' => NULL),
            array('currency_id' => '1','network_id' => '4','fees' => '5.0000000000000000','created_at' => '2023-11-30 04:54:29','updated_at' => NULL),
            array('currency_id' => '2','network_id' => '3','fees' => '8.0000000000000000','created_at' => '2023-11-30 04:55:31','updated_at' => NULL),
            array('currency_id' => '2','network_id' => '2','fees' => '4.0000000000000000','created_at' => '2023-11-30 04:55:31','updated_at' => NULL),
            array('currency_id' => '3','network_id' => '5','fees' => '10.0000000000000000','created_at' => '2023-11-30 04:57:31','updated_at' => NULL),
            array('currency_id' => '3','network_id' => '4','fees' => '5.0000000000000000','created_at' => '2023-11-30 04:57:31','updated_at' => NULL),
            array('currency_id' => '4','network_id' => '5','fees' => '10.0000000000000000','created_at' => '2023-11-30 04:59:08','updated_at' => NULL),
            array('currency_id' => '4','network_id' => '4','fees' => '15.0000000000000000','created_at' => '2023-11-30 04:59:08','updated_at' => NULL),
            array('currency_id' => '4','network_id' => '2','fees' => '8.0000000000000000','created_at' => '2023-11-30 04:59:08','updated_at' => NULL)
        );

        CurrencyHasNetwork::insert($currency_has_networks);
        
    }
}
