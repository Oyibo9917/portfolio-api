<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\SmsLog;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmsController extends Controller
{
    public function __construct(private SmsService $smsService) {}

    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'recipients' => 'required',
            'message'    => 'required|string|max:160',
        ]);

        $result = $this->smsService->send($request->recipients, $request->message);

        return $result['success']
            ? ApiResponse::success($result['data'], $result['message'])
            : ApiResponse::error($result['message'], $result['data'], 502);
    }

    public function history(Request $request): JsonResponse
    {
        $query = SmsLog::with('user:id,name,email')
            ->orderByDesc('created_at');

        if (Auth::check()) {
            // Logged in: own messages + anonymous, filterable
            $filter = $request->query('filter', 'all'); // all | mine | anonymous
            $query->where(function ($q) use ($filter) {
                if ($filter === 'mine') {
                    $q->where('user_id', Auth::id());
                } elseif ($filter === 'anonymous') {
                    $q->whereNull('user_id');
                } else {
                    // all = own + anonymous
                    $q->where('user_id', Auth::id())
                      ->orWhereNull('user_id');
                }
            });
        } else {
            // Guest: anonymous only
            $query->whereNull('user_id');
        }

        // Search by message content
        if ($search = $request->query('search')) {
            $query->where('message', 'like', "%{$search}%");
        }

        $logs = $query->get()->map(fn($log) => [
            'id'          => $log->id,
            'sender_name' => $log->sender_name,
            'recipients'  => $log->recipients,
            'message'     => $log->message,
            'status'      => $log->status,
            'sent_at'     => $log->created_at->diffForHumans(),
            'sent_at_raw' => $log->created_at->toDateTimeString(),
            'is_mine'     => Auth::check() && $log->user_id === Auth::id(),
        ]);

        return ApiResponse::success($logs, 'SMS history fetched.');
    }
}
