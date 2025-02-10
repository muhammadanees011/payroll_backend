<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PayItem;

class PayItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PayItem::factory()->create([
            'name' => 'Bonus',
            'code' => '3879',
            'is_benefit_in_kind' => null,
            'taxable' => false,
            'pensionable' => false,
            'subject_to_national_insurance' => false,
            'payment_type' => 'Gross Addition',
        ]);

        PayItem::factory()->create([
            'name' => 'Commission',
            'code' => '3880',
            'is_benefit_in_kind' => null,
            'taxable' => true,
            'pensionable' => true,
            'subject_to_national_insurance' => true,
            'payment_type' => 'Gross Addition',
        ]);
        
    }
}
