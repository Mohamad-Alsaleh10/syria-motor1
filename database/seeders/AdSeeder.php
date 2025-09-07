<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ad; // تأكد من استيراد نموذج الإعلان
use App\Models\Car; // تأكد من استيراد نموذج السيارة
use App\Models\User; // تأكد من استيراد نموذج المستخدم

class AdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // تأكد من وجود سيارات ومستخدمين
        if (Car::count() == 0) {
            $this->call(CarSeeder::class);
        }
        if (User::count() == 0) {
            $this->call(UserSeeder::class);
        }

        $carIds = Car::pluck('id')->all();
        $userIds = User::pluck('id')->all();

        // إنشاء 15 إعلان بيع وهمي
        for ($i = 0; $i < 15; $i++) {
            $carId = $carIds[array_rand($carIds)];
            $userId = $userIds[array_rand($userIds)];
            $price = \Faker\Factory::create()->numberBetween(10000, 100000);
            $status = \Faker\Factory::create()->randomElement(['active', 'pending', 'sold', 'expired']);
            $publishedAt = \Faker\Factory::create()->dateTimeBetween('-1 year', 'now');
            $expiresAt = null;
            if ($status != 'sold') {
                $expiresAt = \Faker\Factory::create()->dateTimeBetween($publishedAt, '+1 year');
            }

            Ad::create([
                'car_id' => $carId,
                'user_id' => $userId,
                'price' => $price,
                'status' => $status,
                'published_at' => $publishedAt,
                'expires_at' => $expiresAt,
            ]);
        }
    }
}
