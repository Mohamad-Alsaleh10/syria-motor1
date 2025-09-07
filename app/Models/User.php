<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'account_type_id',
        'is_verified',
        'verification_documents',
        'is_subscribed', // أضف هذا
        'subscription_ends_at', // أضف هذا
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'verification_documents' => 'array',
        'is_verified' => 'boolean',
        'is_subscribed' => 'boolean', // أضف هذا
        'subscription_ends_at' => 'datetime', // أضف هذا
    ];

    // علاقة مع AccountType
    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    // علاقة مع السيارات التي يملكها المستخدم
    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    // علاقة مع إعلانات البيع التي أنشأها المستخدم
    public function ads()
    {
        return $this->hasMany(Ad::class);
    }

    // علاقة مع إعلانات التأجير التي أنشأها المستخدم
    public function rentalAds()
    {
        return $this->hasMany(RentalAd::class);
    }

    // علاقة مع المزادات التي أنشأها المستخدم (البائع)
    public function auctions()
    {
        return $this->hasMany(Auction::class);
    }

    // علاقة مع المزايدات التي قام بها المستخدم
    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    // علاقة مع الورشة المرتبطة بالمستخدم (إذا كان نوع الحساب ورشة)
    public function workshop()
    {
        return $this->hasOne(Workshop::class);
    }

    // علاقة مع طلبات الخدمة التي قدمها المستخدم
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    // علاقة مع المحفظة الإلكترونية للمستخدم
    public function wallet() // هذا هو التعريف المفقود الذي يسبب المشكلة
    {
        return $this->hasOne(Wallet::class);
    }

    // علاقة مع المعاملات المالية للمستخدم
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // علاقة مع التقييمات التي قام بها المستخدم
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    // علاقة مع الرسائل التي أرسلها المستخدم
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // علاقة مع الرسائل التي تلقاها المستخدم
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    // علاقة مع العروض الترويجية التي أنشأتها الشركة/المعرض
    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    // علاقة مع الاشتراكات
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // دالة مساعدة للتحقق مما إذا كان المستخدم لديه اشتراك نشط
    public function hasActiveSubscription()
    {
        // تأكد من أن Carbon متاح (use Carbon\Carbon;)
        // أو استخدم now() مباشرة إذا كان Laravel يستخدمها تلقائياً
        return $this->is_subscribed && $this->subscription_ends_at && $this->subscription_ends_at->isFuture();
    }

        public function isAdmin()
    {
        return $this->accountType && $this->accountType->name === 'admin';
    }
}
