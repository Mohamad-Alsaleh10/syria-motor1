<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // تأكد من استيراد نموذج المستخدم
use App\Models\AccountType; // تأكد من استيراد نموذج نوع الحساب
use Illuminate\Support\Facades\Hash; // لاستخدام Hash لتعمية كلمة المرور

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. البحث عن أو إنشاء نوع الحساب 'admin'
        $adminAccountType = AccountType::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'حساب مدير النظام']
        );

        // 2. التحقق مما إذا كان المستخدم المدير موجودًا بالفعل
        // يفضل البحث عنه بواسطة البريد الإلكتروني لتجنب التكرار
        $adminUser = User::where('email', 'admin@example.com')->first();

        // 3. إذا لم يكن موجودًا، قم بإنشائه
        if (!$adminUser) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'phone_number' => null, // يمكنك إضافة رقم هاتف إذا أردت
                'password' => Hash::make('password'), // كلمة مرور آمنة للمدير
                'account_type_id' => $adminAccountType->id,
                'is_verified' => true, // المدير موثق افتراضياً
                'verification_documents' => null, // لا توجد وثائق توثيق للمدير
                'email_verified_at' => now(), // توثيق البريد الإلكتروني فوراً
                'is_subscribed' => false, // يمكن أن يكون المدير مشتركاً افتراضياً
                'subscription_ends_at' => null, // أو عدد سنوات أقل، للتجربة
            ]);

            $this->command->info('تم إنشاء مستخدم المدير بنجاح: admin@example.com بكلمة مرور "password".');
        } else {
            $this->command->info('مستخدم المدير (admin@example.com) موجود بالفعل.');
            // يمكنك تحديث بياناته هنا إذا أردت، مثلاً:
            // $adminUser->update([
            //     'password' => Hash::make('password'),
            //     'account_type_id' => $adminAccountType->id,
            // ]);
        }
    }
}