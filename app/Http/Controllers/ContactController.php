<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:2000',
        ]);

        Mail::raw(
            "From: {$request->name} <{$request->email}>\n\n{$request->message}",
            function ($mail) use ($request) {
                $mail->to(config('mail.from.address'))
                     ->subject("[Portfolio Contact] {$request->subject}");
            }
        );

        return ApiResponse::success(null, 'Message sent successfully.');
    }
}
