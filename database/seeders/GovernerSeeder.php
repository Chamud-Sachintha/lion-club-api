<?php

namespace Database\Seeders;

use App\Helpers\AppHelper;
use Illuminate\Database\Seeder;
use App\Models\Governer;
use Illuminate\Support\Facades\Hash;

class GovernerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $governerTable = new Governer();

        $governerTable->first_name = "test";
        $governerTable->last_name = "test last";
        $governerTable->email = "test123@gmail.com";
        $governerTable->password = Hash::make(123);
        $governerTable->create_time = (new AppHelper())->get_date_and_time();
        $governerTable->flag = 'G';

        $governerTable->save();
    }
}
