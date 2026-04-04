<?php

namespace App\Services;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SmsService
{
    private string $token;
    private string $senderId;
    private string $gateway;
    private string $url;

    public function __construct()
    {
        $this->token    = config('services.kudisms.token');
        $this->senderId = config('services.kudisms.sender_id');
        $this->gateway  = config('services.kudisms.gateway');
        $this->url      = config('services.kudisms.url');
    }

    /**
     * Send SMS and log the attempt.
     *
     * @param  string|array  $recipients  e.g. "2348012345678" or ["2348012345678","2348087654321"]
     * @param  string        $message
     * @param  string|null   $senderName  Override sender display name (for contact form)
     */
    public function send(string|array $recipients, string $message, ?string $senderName = null): array
    {
        $recipientString = is_array($recipients)
            ? implode(',', $recipients)
            : $recipients;

        $log = SmsLog::create([
            'user_id'     => Auth::id(),
            'sender_name' => $senderName ?? (Auth::check() ? Auth::user()->name : 'Anonymous'),
            'recipients'  => $recipientString,
            'message'     => $message,
            'status'      => 'pending',
        ]);

        $response = Http::asJson()->post($this->url, [
            'token'      => $this->token,
            'senderID'   => $this->senderId,
            'recipients' => $recipientString,
            'message'    => $message,
            'gateway'    => $this->gateway,
        ]);

        $status = $response->successful() ? 'sent' : 'failed';

        $log->update([
            'status'   => $status,
            'response' => $response->json(),
        ]);

        return [
            'success' => $response->successful(),
            'message' => $response->successful() ? 'SMS sent successfully.' : ($response->json('message') ?? 'Failed to send SMS.'),
            'data'    => $response->json(),
        ];
    }
}
