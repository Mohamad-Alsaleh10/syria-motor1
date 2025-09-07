<?php

namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Database\Eloquent\Model;

    class Rating extends Model
    {
        use HasFactory;

        /**
         * The attributes that are mass assignable.
         *
         * @var array<int, string>
         */
        protected $fillable = [
            'user_id',
            'rateable_id',
            'rateable_type',
            'stars',
            'comment',
        ];

        /**
         * Get the user who made the rating.
         */
        public function user()
        {
            return $this->belongsTo(User::class);
        }

        /**
         * Get the parent rateable model (user, workshop, etc.).
         */
        public function rateable()
        {
            return $this->morphTo();
        }
    }
    