<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\SetupPage;
use Illuminate\Database\Seeder;

class SetupPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $setup_pages = array(
            array('slug' => 'home','title' => 'Home','url' => '/','menu_active' => json_encode(['index']),'block_routes' => json_encode(['index']),'route_name' => 'index','last_edit_by' => '1','status' => '1','default' => '1','created_at' => '2024-01-30 04:00:08','updated_at' => NULL),

            array('slug' => 'about','title' => 'About','url' => '/about','menu_active' => json_encode(['about']),'block_routes' => json_encode(['about']),'route_name' => 'about','last_edit_by' => '1','status' => '1','default' => '0','created_at' => '2024-01-30 04:00:08','updated_at' => NULL),

            array('slug' => 'service','title' => 'Service','url' => '/service','menu_active' => json_encode(['service']),'block_routes' => json_encode(['service']),'route_name' => 'service','last_edit_by' => '1','status' => '1','default' => '0','created_at' => '2024-01-30 04:00:08','updated_at' => NULL),

            array('slug' => 'web-journal','title' => 'Web Journal','url' => '/journal','menu_active' => json_encode(['journal','journal.details','journal.category','journals']),'block_routes' => json_encode(['journal','journal.details','journal.category','journals']),'route_name' => 'journal','last_edit_by' => '1','status' => '1','default' => '0','created_at' => '2024-01-30 04:00:08','updated_at' => NULL),

            array('slug' => 'contact','title' => 'Contact','url' => '/contact','menu_active' => json_encode(['contact']),'block_routes' => json_encode(['contact']),'route_name' => 'contact','last_edit_by' => '1','status' => '1','default' => '0','created_at' => '2024-01-30 04:00:08','updated_at' => NULL)
        ); 

        SetupPage::insert($setup_pages);
    }
}
