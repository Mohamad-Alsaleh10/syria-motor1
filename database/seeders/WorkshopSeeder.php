<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Workshop; // تأكد من استيراد نموذج الورشة
use App\Models\User;    // تأكد من استيراد نموذج المستخدم (لمالك الورشة)
use Faker\Factory as Faker; // استيراد Faker

class WorkshopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // تأكد من وجود مستخدمين لربط الورش بهم
        if (User::count() == 0) {
            $this->call(UserSeeder::class);
        }

        $userIds = User::pluck('id')->all();

        // قائمة بالخدمات المحتملة التي تقدمها الورش
        $possibleServices = [
            'ميكانيكا عامة',
            'كهرباء سيارات',
            'دهان وسمكرة',
            'صيانة فرامل',
            'تغيير زيت وفلاتر',
            'إصلاح تكييف',
            'فحص كمبيوتر',
            'إصلاح إطارات',
        ];

        // إنشاء 10 ورش وهمية
        for ($i = 0; $i < 10; $i++) {
            $selectedServices = $faker->randomElements($possibleServices, $faker->numberBetween(2, 5)); // اختيار 2 إلى 5 خدمات عشوائياً

            Workshop::create([
                'user_id' => $faker->randomElement($userIds), // ربط الورشة بمستخدم عشوائي كمالك
                'name' => $faker->company() . ' لخدمات السيارات',
                'description' => $faker->paragraph(3),
                'location' => $faker->city(),
                'services' => json_encode($selectedServices), // تحويل مصفوفة الخدمات إلى JSON
                'images' => json_encode([ // مسارات الصور الوهمية للورشة
                    'https://placehold.co/600x400/000000/FFFFFF?text=ورشة-' . ($i + 1),
                    'https://placehold.co/600x400/000000/FFFFFF?text=ورشة-صيانة-' . ($i + 1),
                ]),
            ]);
        }
    }
}
