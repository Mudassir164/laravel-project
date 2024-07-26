<?php

namespace Database\Seeders;

use App\Models\{Role,WebUser};
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WebUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        WebUser::truncate();
        $admin = [
            'email' => 'admin@pubactive.com',
            'role_id' => Role::ADMIN,
            'name' => 'Admin',
            'user_name' => 'admin',
            'password' => 12345678,
            'email_verified_at' => now(),
            'country_id' => 1,
            'city_id' => 2
        ];
        $data = [
                'email' => 'ralph@pubactive.com',
                'role_id' => Role::PUB_OWNER,
                'password' => 12345678,
                'pub_name' => "Mclaren's",
                'owner' => 'Ralph 1 Mclaren',
                'email_verified_at' => now(),
                'address' => '54C, Block 2 P.E.C.H.S., Karachi, Sindh 75400, Pakistan',
                'latitude' => '24.871698118129824', 
                'longitude' => '67.04873821217824',
                'phone' => '098723',
                'country_id' => 1,
                'city_id' => 2
        ];
        $sponsor = [
            'email' => 'sponser@pubactive.com',
            'role_id' => Role::SPONSOR,
            'password' => 12345678,
            'business_name' => "Nike Sports Wear",
            'email_verified_at' => now(),
            'address' => 'Street 2 Shop 13 Los Santos',
            'phone' => '098723',
            'country_id' => 1,
            'city_id' => 2
    ];
        WebUser::create($admin);
        WebUser::create($sponsor);
        WebUser::create($data);
    }
}
