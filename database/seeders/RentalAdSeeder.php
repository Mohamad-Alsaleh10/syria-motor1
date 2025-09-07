<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RentalAd; // تأكد من استيراد نموذج إعلان التأجير
use App\Models\Car; // تأكد من استيراد نموذج السيارة
use App\Models\User; // تأكد من استيراد نموذج المستخدم

class RentalAdSeeder extends Seeder
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

        // إنشاء 10 إعلانات تأجير وهمية
        for ($i = 0; $i < 10; $i++) {
            $carId = $carIds[array_rand($carIds)];
            $userId = $userIds[array_rand($userIds)];

            RentalAd::create([
                'car_id' => $carId,
                'user_id' => $userId, // المستخدم الذي يعرض السيارة للإيجار
                'daily_price' => \Faker\Factory::create()->numberBetween(50, 200),
                'monthly_price' => \Faker\Factory::create()->numberBetween(1000, 4000),
                'rental_conditions' => \Faker\Factory::create()->paragraph(2),
                'location' => \Faker\Factory::create()->city(),
                'status' => \Faker\Factory::create()->randomElement(['active', 'pending', 'rented', 'rejected']),
            ]);
        }
    }
}
