<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'value',
        'start_date',
        'end_date',
        'quantity',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // علاقة مع المستخدم (الشركة/المعرض الذي أنشأ العرض)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
