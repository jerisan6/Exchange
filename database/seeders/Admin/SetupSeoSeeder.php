<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\SetupSeo;
use Illuminate\Database\Seeder;

class SetupSeoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $setup_seos = array(
            array('id' => '1','slug' => 'adcrypto-seo','title' => 'adCrypto - Cryptocurrency Exchange and Buy Sell Full Solution','desc' => 'Unlock the gateway to your cryptocurrency business venture with adCrypto—a comprehensive solution tailored for entrepreneurs looking to embark on their crypto journey. Compatible with both Android and iOS platforms, adCrypto offers a seamless experience complemented by an intuitive website and efficient admin panels. Packed with essential features including a cryptocurrency exchange, buying and selling crypto, and smooth withdrawals','tags' => '["adcrypto"]','image' => 'seeder/seo.webp','last_edit_by' => '1','created_at' => '2024-01-30 07:32:06','updated_at' => '2024-01-31 12:41:07')
        );

        SetupSeo::insert($setup_seos);
    }
}
