<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\Network;
use Illuminate\Database\Seeder;

class NetworkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $networks = array(
            array('coin_id' => '1','slug' => 'bitcoin','name' => 'Bitcoin','arrival_time' => '5','description' => 'The first and most well-known cryptocurrency, designed as a decentralized digital currency.','last_edit_by' => '1','status' => '1','created_at' => '2023-11-29 09:12:44','updated_at' => '2023-11-29 09:12:44'),
            array('coin_id' => '2','slug' => 'eth','name' => 'ETH','arrival_time' => '3','description' => 'A decentralized platform that enables the creation and execution of smart contracts and decentralized applications (DApps).','last_edit_by' => '1','status' => '1','created_at' => '2023-11-29 09:13:21','updated_at' => '2023-11-29 09:13:21'),
            array('coin_id' => '2','slug' => 'binance-coin','name' => 'Binance Coin','arrival_time' => '5','description' => 'Originally created as a utility token for the Binance exchange, BNB has expanded its use cases.','last_edit_by' => '1','status' => '1','created_at' => '2023-11-29 09:13:44','updated_at' => '2023-11-29 09:13:44'),
            array('coin_id' => '1','slug' => 'cardano','name' => 'Cardano','arrival_time' => '4','description' => 'A blockchain platform that aims to provide a more secure and scalable infrastructure for the development of decentralized applications and smart contracts.','last_edit_by' => '1','status' => '1','created_at' => '2023-11-29 09:14:07','updated_at' => '2023-11-29 09:14:07'),
            array('coin_id' => '4','slug' => 'ripple','name' => 'Ripple','arrival_time' => '3','description' => 'Developed for fast and low-cost international money transfers, Ripple focuses on providing solutions for financial institutions.','last_edit_by' => '1','status' => '1','created_at' => '2023-11-29 09:14:28','updated_at' => '2023-11-29 09:14:28')
        );
        Network::insert($networks);
    }
}
