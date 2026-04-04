<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'uuid',
        'payment_intent_id',
        'currency',
        'total',
        'quantity',
        'status',
        'payment_method',
        'metadata',
        'product_id',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

     // Automatically generate a UUID for new users
    protected static function booted()
    {
        static::creating(function ($user) {
            // Generate a UUID and assign it to the 'uuid' attribute
            $user->uuid = (string) \Ramsey\Uuid\Guid\Guid::uuid4();
        });
    }
}
