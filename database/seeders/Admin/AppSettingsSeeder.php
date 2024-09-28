<?php

namespace Database\Seeders\Admin;

use Illuminate\Database\Seeder;
use App\Models\Admin\AppSettings;

class AppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'version'               => '1.0.0',
            'splash_screen_image'   => 'seeder/splash-screen.webp'
        ];

        AppSettings::firstOrCreate($data);
    }
}
