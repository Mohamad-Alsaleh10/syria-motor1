<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'user_id',
        'price',
        'status',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // علاقة مع السيارة
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    // علاقة مع المستخدم (البائع)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة متعددة الأشكال مع المعاملات (Polymorphic relationship with Transactions)
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }
}