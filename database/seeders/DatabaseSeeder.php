<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $users = [
            [
                'name'=>"Zin Maung Nyunt(KoKo)",
                'email'=>"zinmgnyunt99@gmail.com",
                'password'=>"password",
                'role'=>"husband",
            ],
            [
                'name'=>"Thinzar Soe(ThaeLay)",
                'email'=>"thinzarsoe14720@gmail.com",
                'password'=>"password",
                'role'=>"wife",
            ]
        ];
        collect($users)->each(function($user){
            User::create($user);
        });
    }
}
