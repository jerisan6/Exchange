<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\TransactionSetting;
use Illuminate\Database\Seeder;

class TransactionSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $transaction_settings = array(
            array('admin_id' => '1','slug' => 'withdraw','title' => 'Withdraw Crypto Charges','fixed_charge' => '2.00','percent_charge' => '1.00','min_limit' => '0.000001','max_limit' => '1000.00','status' => '1','created_at' => NULL,'updated_at' => NULL),
            array('admin_id' => '1','slug' => 'exchange','title' => 'Exchange Crypto Charges','fixed_charge' => '1.00','percent_charge' => '2.00','min_limit' => '0.000001','max_limit' => '1000.00','status' => '1','created_at' => NULL,'updated_at' => NULL)
        );

        TransactionSetting::insert($transaction_settings);
    }
}
