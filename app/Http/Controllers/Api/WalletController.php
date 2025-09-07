<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\User; // لاستخدام نموذج المستخدم
use App\Models\AccountType; // لاستخدام نموذج أنواع الحسابات
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification; // لاستخدام الإشعارات (سنقوم بإعدادها لاحقاً)
use App\Notifications\WalletTransactionNotification; // سننشئ هذا الإشعار لاحقاً

class WalletController extends Controller
{
    // نسبة التطبيق الافتراضية (يمكن جعلها قابلة للتكوين في قاعدة البيانات أو ملف الإعدادات)
    // لم نعد نستخدمها لخصم عمولة الإعلانات، ولكن قد تكون مفيدة لخدمات أخرى.
    const APP_COMMISSION_RATE = 0.02; // 2%

    /**
     * عرض رصيد المحفظة وسجل المعاملات للمستخدم المصادق عليه.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $wallet = $user->wallet;

        if (!$wallet) {
            // هذا السيناريو يجب ألا يحدث إذا تم إنشاء المحفظة عند التسجيل
            // ولكن كإجراء احترازي، يمكننا إنشاء واحدة هنا إذا لم تكن موجودة.
            $wallet = $user->wallet()->create(['balance' => 0]);
        }

        $transactions = $user->transactions()->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'message' => 'تم جلب بيانات المحفظة وسجل المعاملات بنجاح.',
            'balance' => $wallet->balance,
            'transactions' => $transactions,
        ]);
    }

    /**
     * شحن رصيد المحفظة.
     * (يمكن ربطها بـ Sham Cash API أو استخدام كود شحن يدوي)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function charge(Request $request)
    {
        $user = $request->user();
        $wallet = $user->wallet;

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:100', // الحد الأدنى للشحن
            'payment_method' => 'required|string|in:sham_cash,manual_code', // طريقة الدفع
            'code' => 'required_if:payment_method,manual_code|string|nullable', // كود الشحن اليدوي
            // يمكن إضافة حقول لبيانات Sham Cash API هنا
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            $amount = $request->amount;
            $description = 'شحن رصيد المحفظة عبر ' . $request->payment_method;
            $transactionStatus = 'completed';

            if ($request->payment_method === 'sham_cash') {
                // هنا يتم استدعاء Sham Cash API
                // في هذا المثال، سنفترض نجاح العملية
                // $shamCashResponse = $this->callShamCashApi($amount, $user->phone_number);
                // if (!$shamCashResponse->success) {
                //     throw new \Exception('فشل عملية الشحن عبر شام كاش: ' . $shamCashResponse->message);
                // }
                $description .= ' (عبر API)';
            } elseif ($request->payment_method === 'manual_code') {
                // هنا يتم التحقق من صحة الكود يدوياً
                // في هذا المثال، سنفترض أن الكود صالح
                if (!$this->isValidChargeCode($request->code)) {
                    throw new \Exception('كود الشحن غير صالح أو مستخدم مسبقاً.');
                }
                $description .= ' (كود يدوي: ' . $request->code . ')';
            }

            $wallet->balance += $amount;
            $wallet->save();

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'charge',
                'amount' => $amount,
                'status' => $transactionStatus,
                'description' => $description,
                'transactionable_id' => null, // لا يوجد عنصر محدد هنا
                'transactionable_type' => null,
            ]);

            DB::commit();

            // إرسال إشعار فوري (سنتعامل مع الإشعارات لاحقاً)
            // Notification::send($user, new WalletTransactionNotification('charge', $amount, $wallet->balance));

            return response()->json([
                'message' => 'تم شحن المحفظة بنجاح.',
                'new_balance' => $wallet->balance,
                'transaction' => $transaction,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'فشل عملية الشحن: ' . $e->getMessage()], 500);
        }
    }

    /**
     * طلب سحب رصيد من المحفظة.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdraw(Request $request)
    {
        $user = $request->user();
        $wallet = $user->wallet;

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1000', // الحد الأدنى للسحب
            'withdrawal_method' => 'required|string|in:sham_cash,cash_agent', // طريقة السحب
            // يمكن إضافة تفاصيل حساب شام كاش أو بيانات الوكيل هنا
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $amount = $request->amount;

        if ($wallet->balance < $amount) {
            return response()->json(['message' => 'رصيدك غير كافٍ لإتمام عملية السحب.'], 400);
        }

        DB::beginTransaction();
        try {
            // لا يتم خصم الرصيد فعلياً إلا بعد موافقة الإدارة على طلب السحب
            // يتم تسجيل طلب سحب كمعاملة معلقة
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal_request',
                'amount' => $amount,
                'status' => 'pending', // طلب السحب يكون معلقاً للمراجعة
                'description' => 'طلب سحب رصيد بقيمة ' . $amount . ' عبر ' . $request->withdrawal_method,
                'transactionable_id' => null,
                'transactionable_type' => null,
            ]);

            // إرسال إشعار للمدير بوجود طلب سحب جديد
            // Notification::send(User::where('account_type_id', AccountType::where('name', 'admin')->first()->id)->get(), new WalletTransactionNotification('withdrawal_request', $amount, $wallet->balance, $user->name));

            DB::commit();

            return response()->json([
                'message' => 'تم تقديم طلب السحب بنجاح. سيتم مراجعته من قبل الإدارة.',
                'withdrawal_request' => $transaction,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'فشل عملية السحب: ' . $e->getMessage()], 500);
        }
    }

    /**
     * خصم عمولة التطبيق تلقائياً عند إتمام صفقة (خاصة لخدمات الورش).
     * هذه الدالة ستُستدعى داخلياً من وحدات التحكم الأخرى عند إتمام الصفقة.
     *
     * @param  \App\Models\User  $sellerOrLessorUser  المستخدم الذي سيتم خصم العمولة من رصيده
     * @param  float  $dealAmount  قيمة الصفقة الإجمالية
     * @param  \Illuminate\Database\Eloquent\Model  $transactionable  النموذج المرتبط بالصفقة (ServiceRequest)
     * @param  string  $description  وصف المعاملة
     * @return bool
     */
    public static function deductCommission(User $sellerOrLessorUser, float $dealAmount, $transactionable, string $description)
    {
        $commissionAmount = $dealAmount * self::APP_COMMISSION_RATE;
        $wallet = $sellerOrLessorUser->wallet;

        if (!$wallet) {
            \Log::error("Wallet not found for user ID: {$sellerOrLessorUser->id} during commission deduction.");
            return false;
        }

        if ($wallet->balance < $commissionAmount) {
            // في حال عدم كفاية الرصيد، يمكن تسجيل دين على المستخدم أو إرسال إشعار
            \Log::warning("Insufficient balance for commission deduction for user ID: {$sellerOrLessorUser->id}. Required: {$commissionAmount}, Available: {$wallet->balance}");
            // يمكن هنا إنشاء معاملة بحالة 'pending' أو 'failed' وإرسال إشعار للمستخدم
            Transaction::create([
                'user_id' => $sellerOrLessorUser->id,
                'type' => 'commission',
                'amount' => -$commissionAmount, // مبلغ سالب لأنه خصم
                'status' => 'failed_insufficient_funds',
                'description' => $description . ' (فشل بسبب عدم كفاية الرصيد)',
                'transactionable_id' => $transactionable->id,
                'transactionable_type' => get_class($transactionable),
            ]);
            // Notification::send($sellerOrLessorUser, new WalletTransactionNotification('commission_failed', $commissionAmount, $wallet->balance));
            return false;
        }

        DB::beginTransaction();
        try {
            $wallet->balance -= $commissionAmount;
            $wallet->save();

            Transaction::create([
                'user_id' => $sellerOrLessorUser->id,
                'type' => 'commission',
                'amount' => -$commissionAmount, // مبلغ سالب لأنه خصم
                'status' => 'completed',
                'description' => $description,
                'transactionable_id' => $transactionable->id,
                'transactionable_type' => get_class($transactionable),
            ]);

            DB::commit();
            // Notification::send($sellerOrLessorUser, new WalletTransactionNotification('commission_deducted', $commissionAmount, $wallet->balance));
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error deducting commission for user ID: {$sellerOrLessorUser->id}. Error: " . $e->getMessage());
            return false;
        }
    }

    // دالة مساعدة وهمية لاستدعاء Sham Cash API (لأغراض العرض فقط)
    private function callShamCashApi($amount, $phoneNumber)
    {
        // هنا يتم كتابة المنطق الفعلي لاستدعاء API شام كاش
        // مثلاً:
        // $client = new \GuzzleHttp\Client();
        // $response = $client->post('https://shamcash.com/api/charge', [
        //     'json' => [
        //         'api_key' => config('services.shamcash.key'),
        //         'amount' => $amount,
        //         'phone' => $phoneNumber,
        //     ]
        // ]);
        // return json_decode($response->getBody()->getContents());

        // لأغراض الاختبار، نفترض النجاح دائماً
        return (object)['success' => true, 'message' => 'Charge successful'];
    }

    // دالة مساعدة وهمية للتحقق من كود الشحن اليدوي (لأغراض العرض فقط)
    private function isValidChargeCode($code)
    {
        // هنا يتم التحقق من الكود في قاعدة بيانات أو نظام خارجي
        // مثلاً: التحقق من جدول أكواد الشحن، التأكد من أنه لم يستخدم من قبل
        // return \App\Models\ChargeCode::where('code', $code)->where('used', false)->exists();

        // لأغراض الاختبار، نفترض أن الكود "12345" صالح
        return $code === '12345';
    }
}
