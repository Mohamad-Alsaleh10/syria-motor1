<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet; // لاستخدام نموذج المحفظة
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\AccountType; // لاستخدام نموذج أنواع الحسابات

class AuthController extends Controller
{
    /**
     * تسجيل مستخدم جديد.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'nullable|string|unique:users|regex:/^([0-9\s\-\+\(\)]*)$/|min:10', // يمكن أن يكون رقم الهاتف اختيارياً
            'password' => 'required|string|min:8|confirmed',
            'account_type' => 'required|string|in:individual,company,workshop', // يجب أن يكون أحد الأنواع المحددة
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // البحث عن نوع الحساب
        $accountType = AccountType::where('name', $request->account_type)->first();

        if (!$accountType) {
            return response()->json(['message' => 'نوع الحساب غير صالح.'], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'account_type_id' => $accountType->id,
            'is_verified' => false, // افتراضياً غير موثق
            'verification_documents' => null, // لا توجد وثائق عند التسجيل
        ]);

        // إنشاء محفظة جديدة للمستخدم
        $user->wallet()->create(['balance' => 0]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل المستخدم بنجاح.',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * تسجيل دخول المستخدم.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'بيانات الاعتماد غير صحيحة.',
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح.',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * تسجيل خروج المستخدم.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح.',
        ]);
    }

    /**
     * الحصول على بيانات المستخدم المصادق عليه.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('accountType', 'wallet'), // تحميل نوع الحساب والمحفظة
        ]);
    }
}