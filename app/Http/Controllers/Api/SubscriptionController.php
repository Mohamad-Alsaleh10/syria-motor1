<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Transaction; // لاستخدام نموذج المعاملة
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // لاستخدام التواريخ

class SubscriptionController extends Controller
{
    // سعر الاشتراك الشهري (يمكن جعله قابلاً للتكوين في قاعدة البيانات أو ملف الإعدادات)
    const MONTHLY_SUBSCRIPTION_PRICE = 20000; // 20,000 ليرة سورية

    /**
     * عرض حالة الاشتراك الحالية للمستخدم.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $activeSubscription = $user->subscriptions()->where('status', 'active')->latest('end_date')->first();

        return response()->json([
            'message' => 'تم جلب حالة الاشتراك.',
            'is_subscribed' => $user->is_subscribed,
            'subscription_ends_at' => $user->subscription_ends_at,
            'active_subscription_details' => $activeSubscription,
        ]);
    }

    /**
     * اشتراك المستخدم في الخدمة الشهرية.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscribe(Request $request)
    {
        $user = $request->user();
        $wallet = $user->wallet;

        // التحقق من صلاحية المستخدم للاشتراك (مثلاً، الأفراد، الشركات، الورش)
        if (!in_array($user->accountType->name, ['individual', 'company', 'workshop'])) {
            return response()->json(['message' => 'نوع حسابك لا يسمح بالاشتراك في هذه الخدمة.'], 403);
        }

        // التحقق مما إذا كان المستخدم لديه اشتراك نشط بالفعل
        if ($user->hasActiveSubscription()) {
            return response()->json(['message' => 'لديك بالفعل اشتراك نشط ينتهي في ' . $user->subscription_ends_at->format('Y-m-d H:i:s') . '.'], 400);
        }

        $subscriptionPrice = self::MONTHLY_SUBSCRIPTION_PRICE;

        // التحقق من رصيد المحفظة
        if (!$wallet || $wallet->balance < $subscriptionPrice) {
            return response()->json(['message' => 'رصيدك في المحفظة غير كافٍ للاشتراك. يرجى شحن المحفظة.'], 400);
        }

        DB::beginTransaction();
        try {
            // خصم مبلغ الاشتراك من المحفظة
            $wallet->balance -= $subscriptionPrice;
            $wallet->save();

            // تسجيل المعاملة
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'subscription_payment',
                'amount' => -$subscriptionPrice, // مبلغ سالب لأنه خصم
                'status' => 'completed',
                'description' => 'دفع اشتراك شهري بقيمة ' . $subscriptionPrice . ' ليرة سورية.',
                'transactionable_id' => null, // يمكن ربطه بالاشتراك لاحقاً
                'transactionable_type' => null,
            ]);

            // تحديد تواريخ الاشتراك
            $startDate = now();
            $endDate = $startDate->copy()->addMonth(); // اشتراك لمدة شهر

            // إنشاء سجل الاشتراك
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'amount' => $subscriptionPrice,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'transaction_id' => $transaction->id, // ربط المعاملة بالاشتراك
            ]);

            // تحديث بيانات المستخدم
            $user->is_subscribed = true;
            $user->subscription_ends_at = $endDate;
            $user->save();

            DB::commit();

            return response()->json([
                'message' => 'تم الاشتراك بنجاح. اشتراكك ساري حتى ' . $endDate->format('Y-m-d H:i:s') . '.',
                'new_balance' => $wallet->balance,
                'subscription' => $subscription,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'فشل عملية الاشتراك: ' . $e->getMessage()], 500);
        }
    }

    /**
     * إلغاء اشتراك المستخدم.
     * (يمكن أن يكون الإلغاء فورياً أو عند نهاية الفترة الحالية)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request)
    {
        $user = $request->user();
        $activeSubscription = $user->subscriptions()->where('status', 'active')->latest('end_date')->first();

        if (!$activeSubscription) {
            return response()->json(['message' => 'ليس لديك اشتراك نشط لإلغائه.'], 400);
        }

        DB::beginTransaction();
        try {
            $activeSubscription->status = 'cancelled';
            $activeSubscription->save();

            // يمكن تعيين is_subscribed إلى false فوراً أو عند انتهاء الفترة
            // هنا سنقوم بتعيينها عند نهاية الفترة الحالية للحفاظ على الخدمة حتى ذلك الحين
            // أو يمكنك جعلها false فوراً إذا كان الإلغاء يعني التوقف الفوري للخدمة
            // $user->is_subscribed = false;
            // $user->subscription_ends_at = null;
            // $user->save();

            DB::commit();

            return response()->json([
                'message' => 'تم إلغاء الاشتراك بنجاح. سيبقى اشتراكك نشطاً حتى ' . $activeSubscription->end_date->format('Y-m-d H:i:s') . '.',
                'subscription' => $activeSubscription,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'فشل عملية الإلغاء: ' . $e->getMessage()], 500);
        }
    }

    /**
     * وظيفة مساعدة لإنهاء الاشتراكات المنتهية.
     * يمكن استدعاؤها عبر Cron Job أو Event Listener.
     *
     * @return void
     */
    public function expireSubscriptions()
    {
        $expiredSubscriptions = Subscription::where('status', 'active')
            ->where('end_date', '<=', now())
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            DB::beginTransaction();
            try {
                $subscription->status = 'expired';
                $subscription->save();

                $user = $subscription->user;
                if ($user) {
                    $user->is_subscribed = false;
                    $user->subscription_ends_at = null;
                    $user->save();
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error("Failed to expire subscription ID {$subscription->id}: " . $e->getMessage());
            }
        }
    }
}
