<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaySchedule;

class PayScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaySchedule::factory()->create([
            'name' => 'Monthly Pay Schedule',
            'pay_frequency' => 'Monthly',
            'paydays' => 'Monday',
            'first_paydate' => '2024-11-04',
            'day_rate_month' => 'Calander Month',
            'status' => 'Active',
        ]);

        PaySchedule::factory()->create([
            'name' => 'December Pay Schedule',
            'pay_frequency' => 'Monthly',
            'paydays' => 'Monday',
            'first_paydate' => '2024-12-02',
            'day_rate_month' => 'Calander Month',
            'status' => 'Active',
        ]);
    }
}
