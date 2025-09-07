<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Message; // تأكد من استيراد نموذج الرسالة
use App\Models\User;   // تأكد من استيراد نموذج المستخدم
use Faker\Factory as Faker; // استيراد Faker

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // تأكد من وجود مستخدمين (نحتاج على الأقل مستخدمين اثنين للمراسلة)
        if (User::count() < 2) {
            $this->call(AdminUserSeeder::class);
        }

        $userIds = User::pluck('id')->all();

        // إنشاء 20 رسالة وهمية
        for ($i = 0; $i < 20; $i++) {
            $senderId = $faker->randomElement($userIds);
            $receiverId = $faker->randomElement($userIds);

            // تأكد أن المرسل والمستقبل مختلفان
            // هذا يمنع رسالة من أن تكون مرسلة ومستقبلة من نفس الشخص،
            // إلا إذا كان هناك مستخدم واحد فقط في قاعدة البيانات (حالة نادرة في الـ seeding)
            while ($senderId == $receiverId && count($userIds) > 1) {
                $receiverId = $faker->randomElement($userIds);
            }

            $isRead = $faker->boolean(70); // 70% فرصة أن تكون الرسالة مقروءة

            Message::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'content' => $faker->paragraph(2), // محتوى الرسالة
                'is_read' => $isRead,
                'created_at' => $faker->dateTimeBetween('-3 months', 'now'),
                'updated_at' => $faker->dateTimeBetween('-3 months', 'now'),
            ]);
        }
    }
}
