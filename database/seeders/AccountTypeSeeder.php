<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AccountType;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AccountType::create(['name' => 'individual', 'description' => 'حساب فردي للمشترين والبائعين المستأجرين']);
        AccountType::create(['name' => 'company', 'description' => 'حساب شركة أو معرض لبيع وتأجير السيارات']);
        AccountType::create(['name' => 'workshop', 'description' => 'حساب ورشة صيانة وخدمات فنية']);
        AccountType::create(['name' => 'admin', 'description' => 'حساب مدير النظام']);
    }
}