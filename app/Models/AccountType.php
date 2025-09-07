<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];


        // علاقة مع المستخدمين
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
