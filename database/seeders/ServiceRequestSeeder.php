<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ServiceRequest; // تأكد من استيراد نموذج طلب الخدمة
use App\Models\User;           // تأكد من استيراد نموذج المستخدم
use App\Models\Workshop;        // تأكد من استيراد نموذج الورشة
use Faker\Factory as Faker;     // استيراد Faker

class ServiceRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // تأكد من وجود مستخدمين وورش لربط طلبات الخدمة بهم
        if (User::count() == 0) {
            $this->call(UserSeeder::class);
        }
        if (Workshop::count() == 0) {
            $this->call(WorkshopSeeder::class);
        }

        $userIds = User::pluck('id')->all();
        $workshopIds = Workshop::pluck('id')->all();

        // أنواع الحالات الممكنة لطلب الخدمة
        $statuses = ['pending', 'accepted', 'rejected', 'completed', 'cancelled'];

        // قائمة بأنواع الخدمات المحتملة للعناوين
        $serviceTitles = [
            'صيانة دورية للسيارة',
            'فحص شامل للمحرك',
            'إصلاح نظام الفرامل',
            'تغيير زيت وفلاتر',
            'مشكلة في تكييف الهواء',
            'فحص كهرباء السيارة',
            'إصلاح هيكل السيارة (سمكرة ودهان)',
            'فحص الإطارات والترصيص',
            'مشكلة في ناقل الحركة',
            'فحص نظام التعليق',
        ];

        // إنشاء 15 طلب خدمة وهمي
        for ($i = 0; $i < 15; $i++) {
            $userId = $faker->randomElement($userIds);
            // 70% فرصة لتعيين ورشة، وإلا تكون null
            $workshopId = $faker->boolean(70) ? $faker->randomElement($workshopIds) : null;
            $status = $faker->randomElement($statuses);
            $estimatedCost = null;

            // إذا كانت الحالة ليست 'pending' أو 'rejected' أو 'cancelled'، فربما يكون لها تكلفة تقديرية
            if ($status === 'accepted' || $status === 'completed') {
                $estimatedCost = $faker->randomFloat(2, 50, 1500);
            }

            ServiceRequest::create([
                'user_id' => $userId,
                'workshop_id' => $workshopId,
                'title' => $faker->randomElement($serviceTitles),
                'description' => $faker->paragraph(3),
                'status' => $status,
                'estimated_cost' => $estimatedCost,
            ]);
        }
    }
}
