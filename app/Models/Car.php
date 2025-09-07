<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'make',
        'model',
        'year',
        'condition',
        'description',
        'location',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    // علاقة مع المستخدم (المالك)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة مع إعلانات البيع
    public function ad()
    {
        return $this->hasOne(Ad::class);
    }

    // علاقة مع إعلانات التأجير
    public function rentalAd()
    {
        return $this->hasOne(RentalAd::class);
    }

    // علاقة مع المزاد
    public function auction()
    {
        return $this->hasOne(Auction::class);
    }
}
