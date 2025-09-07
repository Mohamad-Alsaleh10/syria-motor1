<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'location',
        'services',
        'images',
    ];

    protected $casts = [
        'services' => 'array',
        'images' => 'array',
    ];

    // علاقة مع المستخدم (حساب الورشة)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة مع طلبات الخدمة
    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    // علاقة متعددة الأشكال مع التقييمات (Polymorphic relationship with Ratings)
    public function ratings()
    {
        return $this->morphMany(Rating::class, 'rateable');
    }
}
