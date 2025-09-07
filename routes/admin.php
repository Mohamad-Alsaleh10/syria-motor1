<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdController;
use App\Http\Controllers\Admin\RentalAdController;
use App\Http\Controllers\Admin\AuctionController;
use App\Http\Controllers\Admin\WorkshopController;
use App\Http\Controllers\Admin\ServiceRequestController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\RatingController;

// مجموعة مسارات المدير، محمية بواسطة middleware 'auth' و 'admin'
Route::middleware(['auth', 'admin'])->group(function () {
    // لوحة تحكم المدير
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // إدارة المستخدمين (مسارات فردية لعمليات CRUD)
    Route::get('admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('admin/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
    Route::get('admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    // إدارة إعلانات البيع (مسارات فردية لعمليات CRUD)
    Route::get('admin/ads', [AdController::class, 'index'])->name('admin.ads.index');
    Route::get('admin/ads/create', [AdController::class, 'create'])->name('admin.ads.create');
    Route::post('admin/ads', [AdController::class, 'store'])->name('admin.ads.store');
    Route::get('admin/ads/{ad}', [AdController::class, 'show'])->name('admin.ads.show');
    Route::get('admin/ads/{ad}/edit', [AdController::class, 'edit'])->name('admin.ads.edit');
    Route::put('admin/ads/{ad}', [AdController::class, 'update'])->name('admin.ads.update');
    Route::delete('admin/ads/{ad}', [AdController::class, 'destroy'])->name('admin.ads.destroy');

    // إدارة إعلانات التأجير (مسارات فردية لعمليات CRUD)
    Route::get('admin/rental-ads', [RentalAdController::class, 'index'])->name('admin.rental-ads.index');
    Route::get('admin/rental-ads/create', [RentalAdController::class, 'create'])->name('admin.rental-ads.create');
    Route::post('admin/rental-ads', [RentalAdController::class, 'store'])->name('admin.rental-ads.store');
    Route::get('admin/rental-ads/{rental_ad}', [RentalAdController::class, 'show'])->name('admin.rental-ads.show');
    Route::get('admin/rental-ads/{rental_ad}/edit', [RentalAdController::class, 'edit'])->name('admin.rental-ads.edit');
    Route::put('admin/rental-ads/{rental_ad}', [RentalAdController::class, 'update'])->name('admin.rental-ads.update');
    Route::delete('admin/rental-ads/{rental_ad}', [RentalAdController::class, 'destroy'])->name('admin.rental-ads.destroy');

    // إدارة المزادات (مسارات فردية لعمليات CRUD)
    Route::get('admin/auctions', [AuctionController::class, 'index'])->name('admin.auctions.index');
    Route::get('admin/auctions/create', [AuctionController::class, 'create'])->name('admin.auctions.create');
    Route::post('admin/auctions', [AuctionController::class, 'store'])->name('admin.auctions.store');
    Route::get('admin/auctions/{auction}', [AuctionController::class, 'show'])->name('admin.auctions.show');
    Route::get('admin/auctions/{auction}/edit', [AuctionController::class, 'edit'])->name('admin.auctions.edit');
    Route::put('admin/auctions/{auction}', [AuctionController::class, 'update'])->name('admin.auctions.update');
    Route::delete('admin/auctions/{auction}', [AuctionController::class, 'destroy'])->name('admin.auctions.destroy');

    // إدارة الورش (مسارات فردية لعمليات CRUD)
    Route::get('admin/workshops', [WorkshopController::class, 'index'])->name('admin.workshops.index');
    Route::get('admin/workshops/create', [WorkshopController::class, 'create'])->name('admin.workshops.create');
    Route::post('admin/workshops', [WorkshopController::class, 'store'])->name('admin.workshops.store');
    Route::get('admin/workshops/{workshop}', [WorkshopController::class, 'show'])->name('admin.workshops.show');
    Route::get('admin/workshops/{workshop}/edit', [WorkshopController::class, 'edit'])->name('admin.workshops.edit');
    Route::put('admin/workshops/{workshop}', [WorkshopController::class, 'update'])->name('admin.workshops.update');
    Route::delete('admin/workshops/{workshop}', [WorkshopController::class, 'destroy'])->name('admin.workshops.destroy');

    // إدارة طلبات الخدمة (مسارات فردية لعمليات CRUD)
    Route::get('admin/service-requests', [ServiceRequestController::class, 'index'])->name('admin.service-requests.index');
    Route::get('admin/service-requests/create', [ServiceRequestController::class, 'create'])->name('admin.service-requests.create');
    Route::post('admin/service-requests', [ServiceRequestController::class, 'store'])->name('admin.service-requests.store');
    Route::get('admin/service-requests/{service_request}', [ServiceRequestController::class, 'show'])->name('admin.service-requests.show');
    Route::get('admin/service-requests/{service_request}/edit', [ServiceRequestController::class, 'edit'])->name('admin.service-requests.edit');
    Route::put('admin/service-requests/{service_request}', [ServiceRequestController::class, 'update'])->name('admin.service-requests.update');
    Route::delete('admin/service-requests/{service_request}', [ServiceRequestController::class, 'destroy'])->name('admin.service-requests.destroy');

    // إدارة العروض الترويجية (مسارات فردية لعمليات CRUD)
    Route::get('admin/promotions', [PromotionController::class, 'index'])->name('admin.promotions.index');
    Route::get('admin/promotions/create', [PromotionController::class, 'create'])->name('admin.promotions.create');
    Route::post('admin/promotions', [PromotionController::class, 'store'])->name('admin.promotions.store');
    Route::get('admin/promotions/{promotion}', [PromotionController::class, 'show'])->name('admin.promotions.show');
    Route::get('admin/promotions/{promotion}/edit', [PromotionController::class, 'edit'])->name('admin.promotions.edit');
    Route::put('admin/promotions/{promotion}', [PromotionController::class, 'update'])->name('admin.promotions.update');
    Route::delete('admin/promotions/{promotion}', [PromotionController::class, 'destroy'])->name('admin.promotions.destroy');

    // إدارة المعاملات المالية (مسارات فردية لعمليات CRUD)
    Route::get('admin/transactions', [TransactionController::class, 'index'])->name('admin.transactions.index');
    Route::get('admin/transactions/create', [TransactionController::class, 'create'])->name('admin.transactions.create');
    Route::post('admin/transactions', [TransactionController::class, 'store'])->name('admin.transactions.store');
    Route::get('admin/transactions/{transaction}', [TransactionController::class, 'show'])->name('admin.transactions.show');
    Route::get('admin/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('admin.transactions.edit');
    Route::put('admin/transactions/{transaction}', [TransactionController::class, 'update'])->name('admin.transactions.update');
    Route::delete('admin/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('admin.transactions.destroy');

    // إدارة الرسائل (مسارات فردية لعمليات CRUD)
    Route::get('admin/messages', [MessageController::class, 'index'])->name('admin.messages.index');
    Route::get('admin/messages/create', [MessageController::class, 'create'])->name('admin.messages.create');
    Route::post('admin/messages', [MessageController::class, 'store'])->name('admin.messages.store');
    Route::get('admin/messages/{message}', [MessageController::class, 'show'])->name('admin.messages.show');
    Route::get('admin/messages/{message}/edit', [MessageController::class, 'edit'])->name('admin.messages.edit');
    Route::put('admin/messages/{message}', [MessageController::class, 'update'])->name('admin.messages.update');
    Route::delete('admin/messages/{message}', [MessageController::class, 'destroy'])->name('admin.messages.destroy');

    // إدارة التقييمات (مسارات فردية لعمليات CRUD)
    Route::get('admin/ratings', [RatingController::class, 'index'])->name('admin.ratings.index');
    Route::get('admin/ratings/create', [RatingController::class, 'create'])->name('admin.ratings.create');
    Route::post('admin/ratings', [RatingController::class, 'store'])->name('admin.ratings.store');
    Route::get('admin/ratings/{rating}', [RatingController::class, 'show'])->name('admin.ratings.show');
    Route::get('admin/ratings/{rating}/edit', [RatingController::class, 'edit'])->name('admin.ratings.edit');
    Route::put('admin/ratings/{rating}', [RatingController::class, 'update'])->name('admin.ratings.update');
    Route::delete('admin/ratings/{rating}', [RatingController::class, 'destroy'])->name('admin.ratings.destroy');
});
