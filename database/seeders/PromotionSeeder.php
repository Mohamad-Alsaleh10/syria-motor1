<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Promotion; // تأكد من استيراد نموذج العرض الترويجي
use App\Models\User;     // تأكد من استيراد نموذج المستخدم
use Faker\Factory as Faker; // استيراد Faker

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // تأكد من وجود مستخدمين لربط العروض الترويجية بهم
        if (User::count() == 0) {
            $this->call(UserSeeder::class);
        }

        $userIds = User::pluck('id')->all();

        // أنواع العروض الترويجية الممكنة
        $promotionTypes = ['discount', 'free_service', 'bundle_offer', 'cashback'];

        // حالات العروض الترويجية الممكنة
        $statuses = ['active', 'expired', 'paused'];

        // إنشاء 10 عروض ترويجية وهمية
        for ($i = 0; $i < 10; $i++) {
            $userId = $faker->randomElement($userIds);
            $type = $faker->randomElement($promotionTypes);
            $startDate = $faker->dateTimeBetween('-3 months', 'now');
            $endDate = $faker->dateTimeBetween($startDate, '+3 months');
            $status = $faker->randomElement($statuses);

            $value = null;
            if ($type === 'discount') {
                $value = $faker->randomFloat(2, 5, 50); // نسبة خصم
            } elseif ($type === 'free_service') {
                $value = null; // لا توجد قيمة رقمية مباشرة لخدمة مجانية
            } elseif ($type === 'bundle_offer') {
                $value = $faker->randomFloat(2, 10, 200); // قيمة توفير الحزمة
            } elseif ($type === 'cashback') {
                $value = $faker->randomFloat(2, 5, 100); // مبلغ استرداد نقدي
            }

            $quantity = null;
            if ($faker->boolean(40)) { // 40% فرصة لوجود كمية محدودة
                $quantity = $faker->numberBetween(10, 200);
            }

            Promotion::create([
                'user_id' => $userId, // الشركة/المعرض الذي أنشأ العرض
                'title' => $faker->sentence(3) . ' ' . ucfirst(str_replace('_', ' ', $type)),
                'description' => $faker->boolean(80) ? $faker->paragraph(2) : null, // 80% فرصة لوجود وصف
                'type' => $type,
                'value' => $value,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'quantity' => $quantity,
                'status' => $status,
            ]);
        }
    }
}
