<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'user_id',
        'starting_price',
        'current_price',
        'start_time',
        'end_time',
        'winner_id',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
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

    // علاقة مع الفائز بالمزاد
    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    // علاقة مع المزايدات
    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    // علاقة متعددة الأشكال مع المعاملات (Polymorphic relationship with Transactions)
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }
}

