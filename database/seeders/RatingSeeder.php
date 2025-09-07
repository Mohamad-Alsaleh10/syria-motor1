<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rating; // تأكد من استيراد نموذج التقييم
use App\Models\User;   // تأكد من استيراد نموذج المستخدم
use App\Models\Workshop; // يمكن تقييم الورش أيضًا
use Faker\Factory as Faker; // استيراد Faker

class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // تأكد من وجود مستخدمين وورش لربط التقييمات بهم
        if (User::count() == 0) {
            $this->call(UserSeeder::class);
        }
        if (Workshop::count() == 0) {
            $this->call(WorkshopSeeder::class);
        }

        $userIds = User::pluck('id')->all();
        $workshopIds = Workshop::pluck('id')->all();

        // أنواع الكيانات التي يمكن تقييمها
        // تأكد من أن هذه النماذج موجودة ولديها بيانات في قاعدة البيانات
        $rateableTypes = [
            User::class,
            Workshop::class,
            // يمكنك إضافة نماذج أخرى هنا إذا كانت قابلة للتقييم
            // مثال: App\Models\Car::class,
        ];

        // إنشاء 20 تقييمًا وهميًا
        for ($i = 0; $i < 20; $i++) {
            $userId = $faker->randomElement($userIds); // المستخدم الذي قام بالتقييم

            // اختيار كيان عشوائي للتقييم (rateable)
            $rateableType = $faker->randomElement($rateableTypes);
            $rateableId = null;

            if ($rateableType === User::class) {
                // إذا كان الكيان الذي يتم تقييمه هو مستخدم، اختر مستخدمًا عشوائيًا
                $rateableId = $faker->randomElement($userIds);
                // تأكد أن المستخدم الذي قام بالتقييم ليس هو نفسه الذي يتم تقييمه
                while ($userId == $rateableId && count($userIds) > 1) {
                    $rateableId = $faker->randomElement($userIds);
                }
            } elseif ($rateableType === Workshop::class) {
                // إذا كان الكيان الذي يتم تقييمه هو ورشة، اختر ورشة عشوائية
                $rateableId = $faker->randomElement($workshopIds);
            }

            // تأكد من أننا حصلنا على rateableId قبل إنشاء التقييم
            if ($rateableId) {
                Rating::create([
                    'user_id' => $userId, // المستخدم الذي قام بالتقييم
                    'rateable_id' => $rateableId,
                    'rateable_type' => $rateableType,
                    'stars' => $faker->numberBetween(1, 5), // التقييم من 1 إلى 5 نجوم
                    'comment' => $faker->boolean(80) ? $faker->paragraph(1) : null, // 80% فرصة لوجود تعليق
                ]);
            }
        }
    }
}
