<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $regions = [
            [
                'name' => 'North Central'
            ],
            [
                'name' => 'South East'
            ],
            [
                'name' => 'North East'
            ],
            [
                'name' => 'South South'
            ],
            [
                'name' => 'North West'
            ],
            [
                'name' => 'South West'
            ]
        ];

        foreach ($regions as $r) {
             \App\Models\Region::create([
                 'name'=>$r['name'],
             ]);
        }
    }
}
