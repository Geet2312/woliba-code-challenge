<?php

namespace Database\Seeders;

use App\Models\WellbeingPillar;
use Illuminate\Database\Seeder;

class WellbeingPillarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pillars = [
            ['name' => 'Physical'],
            ['name' => 'Mental'],
            ['name' => 'Social'],
            ['name' => 'Financial'],
            ['name' => 'Emotional'],
        ];

        foreach ($pillars as $pillar) {
            WellbeingPillar::firstOrCreate(['name' => $pillar['name']]);
        }
    }
}
