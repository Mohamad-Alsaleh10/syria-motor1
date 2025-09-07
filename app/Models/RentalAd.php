<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalAd extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'user_id',
        'daily_price',
        'monthly_price',
        'rental_conditions',
        'location',
        'status',
    ];

    // علاقة مع السيارة
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    // علاقة مع المستخدم (المؤجر)
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