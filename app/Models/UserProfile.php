<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserProfile extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'city', 'bio', 'profile_picture', 'phone_number', 'address'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// $user = User::find(1); // Find the user
// // Create or update the user's profile
// $user->profile()->create([
//     'city' => 'New York',
//     'bio' => 'Developer and Tech Enthusiast',
//     'profile_picture' => 'path/to/profile-pic.jpg',
//     'phone_number' => '123-456-7890',
//     'address' => '123 Tech St, New York, NY',
// ]);

// $user = User::find(1);
// // Update the user's profile
// $user->profile()->update([
//     'city' => 'San Francisco',
//     'bio' => 'Software Engineer at XYZ Corp',
// ]);