<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use App\Notifications\PasswordResetNotification;

class SendPasswordResetEmailJob implements ShouldQueue
{
    use Queueable;

   protected $email;
    protected $token;

    /**
     * Create a new job instance.
     *
     * @param string $email The user's email address.
     * @param string $token The secure password reset token.
     */
    public function __construct(string $email, string $token)
    {
        $this->email = $email;
        $this->token = $token;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::where('email', $this->email)->first();

        if ($user) {
            $user->notify(new PasswordResetNotification($this->token));
        }
    }
}
