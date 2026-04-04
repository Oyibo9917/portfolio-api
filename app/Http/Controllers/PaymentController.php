<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->service = $paymentService;
        $this->paymentService = $paymentService;
        parent::__construct();
    }

    public function handlePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'currency' => 'required|string|in:usd,ngn,eur,gbp',
            'payment_method_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), $validator->errors(), 422);
        }

        return $this->paymentService->createPaymentIntent($request->all());
    }

    public function handleCallback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_intent_id' => 'required|string',
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'total' => 'required|numeric|min:0',
            'user_uuid' => 'required|string|exists:users,uuid',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), $validator->errors(), 422);
        }

        // Get user by UUID
        $user = \App\Models\User::where('uuid', $request->user_uuid)->firstOrFail();

        $data = $request->all();
        $data['user_id'] = $user->id;

        return $this->paymentService->handleCallback($data);
    }
}
