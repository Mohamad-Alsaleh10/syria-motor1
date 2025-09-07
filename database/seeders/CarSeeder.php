<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Car; // تأكد من استيراد نموذج السيارة
use App\Models\User; // تأكد من استيراد نموذج المستخدم (لمالك السيارة)
use Faker\Factory as Faker; // استيراد Faker

class CarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // تأكد من وجود مستخدمين لربط السيارات بهم
        if (User::count() == 0) {
            $this->call(UserSeeder::class);
        }

        $userIds = User::pluck('id')->all();

        // إنشاء 20 سيارة وهمية
        for ($i = 0; $i < 20; $i++) {
            Car::create([
                'user_id' => $faker->randomElement($userIds), // ربط السيارة بمستخدم عشوائي
                'make' => $faker->randomElement(['Toyota', 'Honda', 'Ford', 'BMW', 'Mercedes-Benz', 'Audi', 'Nissan', 'Hyundai', 'Kia', 'Chevrolet']),
                'model' => $faker->word(),
                'year' => $faker->numberBetween(2000, 2024),
                'condition' => $faker->randomElement(['new', 'used']), // 'new' or 'used'
                'description' => $faker->paragraph(3),
                'location' => $faker->city(), // موقع السيارة (مدينة)
                'images' => json_encode([ // مسارات الصور الوهمية
                    'https://placehold.co/600x400/000000/FFFFFF?text=' . $faker->word(),
                    'https://placehold.co/600x400/000000/FFFFFF?text=' . $faker->word(),
                ]),
            ]);
        }
    }
}
