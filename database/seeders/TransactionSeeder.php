<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transaction; // تأكد من استيراد نموذج المعاملة
use App\Models\User;       // تأكد من استيراد نموذج المستخدم
use App\Models\Ad;         // مثال على نموذج يمكن أن يكون 'transactionable'
use App\Models\Auction;    // مثال آخر
use App\Models\RentalAd;   // مثال آخر
use App\Models\ServiceRequest; // مثال آخر
use Faker\Factory as Faker; // استيراد Faker

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // تأكد من وجود مستخدمين ونماذج أخرى يمكن ربط المعاملات بها
        if (User::count() == 0) {
            $this->call(UserSeeder::class);
        }
        if (Ad::count() == 0) {
            $this->call(AdSeeder::class);
        }
        if (Auction::count() == 0) {
            $this->call(AuctionSeeder::class);
        }
        if (RentalAd::count() == 0) {
            $this->call(RentalAdSeeder::class);
        }
        if (ServiceRequest::count() == 0) {
            $this->call(ServiceRequestSeeder::class);
        }

        $userIds = User::pluck('id')->all();

        // أنواع المعاملات الممكنة
        $transactionTypes = ['charge', 'debit', 'withdrawal_request', 'commission'];

        // حالات المعاملات الممكنة
        $statuses = ['completed', 'pending', 'failed'];

        // النماذج التي يمكن أن تكون 'transactionable'
        $transactionableModels = [
            Ad::class,
            Auction::class,
            RentalAd::class,
            ServiceRequest::class,
            // يمكنك إضافة نماذج أخرى هنا حسب الحاجة
        ];

        // إنشاء 25 معاملة وهمية
        for ($i = 0; $i < 25; $i++) {
            $userId = $faker->randomElement($userIds);
            $type = $faker->randomElement($transactionTypes);
            $amount = $faker->randomFloat(2, 10, 5000);
            $status = $faker->randomElement($statuses);
            $description = $faker->boolean(70) ? $faker->sentence() : null; // 70% فرصة لوجود وصف

            $transactionableId = null;
            $transactionableType = null;

            // 60% فرصة لربط المعاملة بكيان 'transactionable'
            if ($faker->boolean(60)) {
                $chosenModelClass = $faker->randomElement($transactionableModels);
                $chosenModelInstance = $chosenModelClass::inRandomOrder()->first();

                if ($chosenModelInstance) {
                    $transactionableId = $chosenModelInstance->id;
                    $transactionableType = $chosenModelClass;
                }
            }

            Transaction::create([
                'user_id' => $userId,
                'type' => $type,
                'amount' => $amount,
                'status' => $status,
                'description' => $description,
                'transactionable_id' => $transactionableId,
                'transactionable_type' => $transactionableType,
            ]);
        }
    }
}
