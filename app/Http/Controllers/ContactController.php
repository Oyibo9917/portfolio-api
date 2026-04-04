<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function __construct(private SmsService $smsService) {}

    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'phone'   => 'nullable|string|max:20',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:2000',
        ]);

        // Send email
        Mail::raw(
            "From: {$request->name} <{$request->email}>" .
            ($request->phone ? " | Tel: {$request->phone}" : '') .
            "\n\nSubject: {$request->subject}\n\n{$request->message}",
            function ($mail) use ($request) {
                $mail->to(config('mail.from.address'))
                     ->subject("[Portfolio Contact] {$request->subject}");
            }
        );

        // SMS alert to your number
        $myPhone = config('services.kudisms.my_phone');
        if ($myPhone) {
            try {
                $phone = $request->phone ? " | {$request->phone}" : '';
                $sms   = "Portfolio contact: {$request->name}{$phone} - {$request->subject}. Email: {$request->email}";
                $this->smsService->send($myPhone, substr($sms, 0, 160), $request->name);
            } catch (\Throwable $e) {
                Log::warning('Contact SMS alert failed: ' . $e->getMessage());
            }
        }

        return ApiResponse::success(null, 'Message sent successfully.');
    }
}
