<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;
use App\Models\Admin\AppOnboardScreens;

class AppOnboardScreenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $app_onboard_screens = array(
            array('id' => '1','title' => 'Make Your Crypto Transaction','sub_title' => 'Buy and Sales 100+ Cryptocurrencies with 20+ flat currencies market using bank transfers or your credit/debit card in your exchange type bitcoin establshed token area.','image' => 'seeder/onboard-screen.webp','status' => '1','last_edit_by' => '1','created_at' => '2024-01-11 09:13:30','updated_at' => '2024-01-11 09:13:30')
        );
        AppOnboardScreens::insert($app_onboard_screens);
    }
}
