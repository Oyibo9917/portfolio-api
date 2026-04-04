<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Jobs\SendCustomWelcomeEmailJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeEmailListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event)
    {
        SendCustomWelcomeEmailJob::dispatch($event->user);
    }
}
