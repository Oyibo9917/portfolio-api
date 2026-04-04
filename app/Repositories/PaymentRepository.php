<?php

namespace App\Repositories;

use App\Helpers\ApiResponse;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PaymentRepository extends BaseRepository
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getModel(): Model
    {
        return new Payment();
    }

    public function getCollection(array $options): JsonResponse
    {
        $query = $this->getModel()
            ->with(['user:id,uuid,name,email', 'product:id,name,code,price,image'])
            ->orderBy('created_at', 'desc');

        // Apply filters if provided
        if (isset($options['user_id'])) {
            $query->where('user_id', $options['user_id']);
        }

        if (isset($options['status'])) {
            $query->where('status', $options['status']);
        }

        $payments = $query->get();

        return ApiResponse::success($payments, "Payment records fetched successfully.", 200);
    }

    public function getUserPayments(int $userId, array $options = []): JsonResponse
    {
        $options['user_id'] = $userId;
        return $this->getCollection($options);
    }

    public function getPaymentByIntent(string $paymentIntentId): ?Payment
    {
        return Payment::where('payment_intent_id', $paymentIntentId)->first();
    }

    public function createPayment(array $data): JsonResponse
    {
        $payment = Payment::create($data);
        return ApiResponse::success($payment, "Payment record created successfully.", 201);
    }

    public function updatePaymentStatus(string $paymentIntentId, string $status): JsonResponse
    {
        $payment = Payment::where('payment_intent_id', $paymentIntentId)->firstOrFail();
        $payment->update(['status' => $status]);

        return ApiResponse::success($payment, "Payment status updated successfully.", 200);
    }
}
