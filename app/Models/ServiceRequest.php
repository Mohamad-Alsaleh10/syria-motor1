<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workshop_id',
        'title',
        'description',
        'status',
        'estimated_cost',
    ];

    // علاقة مع المستخدم الطالب للخدمة
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // علاقة مع الورشة المختارة
    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    // علاقة متعددة الأشكال مع المعاملات (Polymorphic relationship with Transactions)
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }
}