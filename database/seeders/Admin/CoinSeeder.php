<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\Coin;
use Illuminate\Database\Seeder;

class CoinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $coins = array(
            array('slug' => 'btc','name' => 'BTC','title' => 'The original cryptocurrency.','last_edit_by' => '1','status' => '1','created_at' => '2023-11-29 09:04:44','updated_at' => '2023-11-29 09:04:44'),
            array('slug' => 'ethereum','name' => 'Ethereum','title' => 'The first Bitcoin alternative on our list','last_edit_by' => '1','status' => '1','created_at' => '2023-11-29 09:05:28','updated_at' => '2023-11-29 09:05:28'),
            array('slug' => 'sol','name' => 'SOL','title' => 'SOL is the native coin of the Solana platform','last_edit_by' => '1','status' => '1','created_at' => '2023-11-29 09:06:25','updated_at' => '2023-11-29 09:06:25'),
            array('slug' => 'usdt','name' => 'USDT','title' => 'Tether (USDT) was one of the first and most popular of the stablecoins.','last_edit_by' => '1','status' => '1','created_at' => '2023-11-29 09:06:49','updated_at' => '2023-11-29 09:06:49'),
            array('slug' => 'cardano','name' => 'Cardano','title' => 'Cardano (ADA) is notable for its early embrace of proof-of-stake validation.','last_edit_by' => '1','status' => '1','created_at' => '2023-11-29 09:07:47','updated_at' => '2023-11-29 09:07:47'),
            array('slug' => 'doge','name' => 'DOGE','title' => 'Baby Doge Coin Price in India Today','last_edit_by' => '1','status' => '1','created_at' => '2023-11-29 09:08:03','updated_at' => '2023-11-29 09:08:03')
        );
        Coin::insert($coins);
    }
}
