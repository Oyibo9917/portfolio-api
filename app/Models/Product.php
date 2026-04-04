<?php

namespace App\Models;

use App\Enums\ProductCategories;
use App\Enums\InventoryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'image',
        'price',
        'category',
        'quantity',
        'inventory_status',
        'rating' // Note: 'rating' is kept for backward compatibility; use getProductRatingAttribute() for average rating
    ];

    protected $casts = [
        'inventory_status' => InventoryStatus::class,
        'category' => ProductCategories::class,
    ];

    protected $appends = ['user_rating', 'has_user_rating'];

    /**
     * Boot the model to listen for the 'creating' event.
     */
    protected static function booted()
    {
        static::creating(function ($product) {
            // Generate a unique code if the code is not already set
            if (!$product->code) {
                $product->code = Str::random(10); // Example: random 10 characters
            }
        });
    }

    /**
     * Generate a random code for the product (if required)
     *
     * @return string
     */
    public static function generateCode()
    {
        return Str::random(10); // Generates a random 10-character code
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: function (string $value) {
                // Check if the image exists in the 'public' disk
                if ($value && Storage::disk('public')->exists($value)) {
                    // Get the binary content of the image
                    $fileContent = Storage::disk('public')->get($value);

                    // Encode the binary content to Base64
                    $base64Encoded = base64_encode($fileContent);

                    // Return the Base64 encoded string along with the mime type
                    // Adjust mime type depending on the image format (e.g. 'image/jpeg', 'image/png', etc.)
                    return 'data:image/jpeg;base64,' . $base64Encoded;
                }

                return null;  // Return null if the image doesn't exist
            },
        );
    }

    public function activityLogs()
    {
        return $this->morphMany(\App\Models\ActivityLog::class, 'subject');
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    // public function getProductRatingAttribute(): float
    // {
    //     $averageRating = Rating::where('product_id', $this->getAttribute('id'))->avg('rating') ?? 0.0;
    //     return $averageRating;
    // }

    public function getUserRatingAttribute(): float
    {
        $user = auth()->user();
        if ($user) {
            $userRating = Rating::where('product_id', $this->getAttribute('id'))
                ->where('user_id', $user->id)
                ->first()?->rating;
            return $userRating ? $userRating : 0.0;
        }
        return 0.0;
    }

    public function getHasUserRatingAttribute(): bool
    {
        $user = auth()->user();
        if(!$user){
            return 0;
        }

        return Rating::where('product_id', $this->getAttribute('id'))
            ->where('user_id', $user->id)
            ->first()?->rating > 0;
    }
}
