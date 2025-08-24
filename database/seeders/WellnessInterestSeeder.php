<?php

namespace Database\Seeders;

use App\Models\WellnessInterest;
use Illuminate\Database\Seeder;

class WellnessInterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $interests = [
            ['name' => 'Yoga'],
            ['name' => 'Meditation'],
            ['name' => 'Fitness'],
            ['name' => 'Nutrition'],
        ];

        foreach ($interests as $interest) {
            WellnessInterest::firstOrCreate(['name' => $interest['name']]);
        }
    }
}
