<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\AuctionController;
use App\Http\Controllers\Api\RentalAdController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\WorkshopController; // استيراد WorkshopController
use App\Http\Controllers\Api\ServiceRequestController; // استيراد ServiceRequestController

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// مسارات المصادقة
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// مسارات إعادة تعيين كلمة المرور (لا تتطلب مصادقة)
// هذا المسار يستقبل البريد الإلكتروني ويرسل رابط إعادة التعيين
Route::post('/forgot-password', [PasswordController::class, 'sendResetLinkEmail'])->name('password.email');

// هذا المسار يستقبل الرمز المميز والبريد الإلكتروني وكلمة المرور الجديدة لإعادة التعيين
Route::post('/reset-password', [PasswordController::class, 'reset'])->name('password.update');

// مسارات محمية تتطلب المصادقة
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

        //(بيع) مسارات إدارة إعلانات السيارات
    Route::get('/ads', [CarController::class, 'index']); // عرض جميع الإعلانات (مع الفلترة والبحث)
    Route::get('/ads/{id}', [CarController::class, 'show']); // عرض إعلان محدد
    Route::post('/ads', [CarController::class, 'store']); // إضافة إعلان بيع جديد
    Route::put('/ads/{id}', [CarController::class, 'update']); // تحديث إعلان بيع
    Route::delete('/ads/{id}', [CarController::class, 'destroy']); // حذف إعلان بيع

        // مسارات إدارة إعلانات تأجير السيارات
    Route::get('/rental-ads', [RentalAdController::class, 'index']); // عرض جميع إعلانات التأجير (مع الفلترة والبحث)
    Route::get('/rental-ads/{id}', [RentalAdController::class, 'show']); // عرض إعلان تأجير محدد
    Route::post('/rental-ads', [RentalAdController::class, 'store']); // إضافة إعلان تأجير جديد
    Route::put('/rental-ads/{id}', [RentalAdController::class, 'update']); // تحديث إعلان تأجير
    Route::delete('/rental-ads/{id}', [RentalAdController::class, 'destroy']); // حذف إعلان تأجير


        // مسارات إدارة المزادات
    Route::get('/auctions', [AuctionController::class, 'index']); // عرض جميع المزادات (مع الفلترة والبحث)
    Route::get('/auctions/{id}', [AuctionController::class, 'show']); // عرض مزاد محدد
    Route::post('/auctions', [AuctionController::class, 'store']); // إضافة مزاد جديد
    Route::post('/auctions/{id}/bid', [AuctionController::class, 'placeBid']); // المزايدة على مزاد
    Route::put('/auctions/{id}', [AuctionController::class, 'update']); // تحديث مزاد
    Route::delete('/auctions/{id}', [AuctionController::class, 'destroy']); // حذف مزاد


        // مسارات إدارة الورش
    Route::get('/workshops', [WorkshopController::class, 'index']); // عرض جميع الورش (مع الفلترة والبحث)
    Route::get('/workshops/{id}', [WorkshopController::class, 'show']); // عرض ورشة محددة
    Route::post('/workshops', [WorkshopController::class, 'store']); // تسجيل ورشة جديدة (للمستخدمين من نوع 'workshop')
    Route::put('/workshops/{id}', [WorkshopController::class, 'update']); // تحديث معلومات ورشة
    Route::delete('/workshops/{id}', [WorkshopController::class, 'destroy']); // حذف ورشة

    // مسارات إدارة طلبات الخدمة
    Route::get('/service-requests', [ServiceRequestController::class, 'index']); // عرض جميع طلبات الخدمة (للمستخدم أو الورشة أو المدير)
    Route::get('/service-requests/{id}', [ServiceRequestController::class, 'show']); // عرض طلب خدمة محدد
    Route::post('/service-requests', [ServiceRequestController::class, 'store']); // تقديم طلب خدمة جديد (للمستخدمين الأفراد)
    Route::put('/service-requests/{id}/status', [ServiceRequestController::class, 'updateStatus']); // تحديث حالة طلب الخدمة (للورشة أو المدير)
    Route::post('/service-requests/{id}/rate', [ServiceRequestController::class, 'rateWorkshop']); // تقييم ورشة بعد الخدمة
    // مسارات إدارة المحفظة الإلكترونية والمعاملات
    Route::get('/wallet', [WalletController::class, 'show']);
    Route::post('/wallet/charge', [WalletController::class, 'charge']);
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);

    // مسارات إدارة الاشتراكات
    Route::get('/subscription', [SubscriptionController::class, 'show']); // عرض حالة الاشتراك
    Route::post('/subscription/subscribe', [SubscriptionController::class, 'subscribe']); // الاشتراك
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']); // إلغاء الاشتراك


    // مثال على مسار محمي
    Route::get('/protected-route', function (Request $request) {
        return response()->json(['message' => 'أهلاً بك في المسار المحمي، ' . $request->user()->name . '!']);
    });
});
