<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Auction; // تأكد من استيراد نموذج المزاد
use App\Models\Car; // تأكد من استيراد نموذج السيارة
use App\Models\User; // تأكد من استيراد نموذج المستخدم (البائع والفائز المحتمل)
use Faker\Factory as Faker; // استيراد Faker

class AuctionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // تأكد من وجود مستخدمين وسيارات لربط المزادات بهم
        if (User::count() == 0) {
            $this->call(UserSeeder::class);
        }
        if (Car::count() == 0) {
            $this->call(CarSeeder::class);
        }

        $carIds = Car::pluck('id')->all();
        $userIds = User::pluck('id')->all(); // جميع المستخدمين للبائع والفائز المحتمل

        // أنواع الحالة الممكنة للمزاد
        $statuses = ['pending', 'active', 'closed', 'cancelled'];

        // إنشاء 15 مزاد وهمي
        for ($i = 0; $i < 15; $i++) {
            $carId = $faker->randomElement($carIds);
            $sellerId = $faker->randomElement($userIds);

            $startingPrice = $faker->numberBetween(5000, 50000);
            $currentPrice = $startingPrice + $faker->numberBetween(0, 10000); // Current price >= starting price

            $startTime = $faker->dateTimeBetween('-1 month', 'now');
            $endTime = $faker->dateTimeBetween($startTime, '+1 month');

            $status = $faker->randomElement($statuses);
            $winnerId = null;

            // إذا كان المزاد "مغلقاً" (closed)، قم بتعيين فائز عشوائي
            if ($status === 'closed' && count($userIds) > 1) {
                $winnerId = $faker->randomElement($userIds);
                // تأكد أن الفائز ليس هو البائع
                while ($winnerId == $sellerId) {
                    $winnerId = $faker->randomElement($userIds);
                }
            }

            Auction::create([
                'car_id' => $carId,
                'user_id' => $sellerId, // البائع
                'starting_price' => $startingPrice,
                'current_price' => $currentPrice,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'winner_id' => $winnerId,
                'status' => $status,
            ]);
        }
    }
}
