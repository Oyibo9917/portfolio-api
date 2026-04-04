<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\Payment;
use App\Models\Product;
use App\Repositories\PaymentRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class PaymentService extends BaseService
{
    public function __construct(PaymentRepository $repository)
    {
        parent::__construct($repository);
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(array $data): JsonResponse
    {
        try {
            $paymentMethodData = [
                'type' => 'card',
                'card' => [
                    'token' => $data['payment_method_id'],
                ],
            ];

            $paymentIntent = PaymentIntent::create([
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'payment_method_data' => $paymentMethodData,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'return_url' => route('payment.callback'),
            ]);

            return ApiResponse::success([
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
            ], 'Payment initiated successfully');
        } catch (ApiErrorException $e) {
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }

    public function handleCallback(array $data): JsonResponse
    {
        DB::beginTransaction();
        try {
            $paymentIntent = PaymentIntent::retrieve($data['payment_intent_id']);

            if ($paymentIntent->status !== 'succeeded') {
                return ApiResponse::error('Payment failed', null, 400);
            }

            // Create payment record
            $payment = Payment::updateOrCreate(
                ['payment_intent_id' => $data['payment_intent_id']],
                [
                    'user_id' => $data['user_id'],
                    'currency' => $paymentIntent->currency,
                    'quantity' => $data['quantity'],
                    'total' => $data['total'],
                    'status' => $paymentIntent->status,
                    'product_id' => $data['product_id'],
                    'payment_method' => $paymentIntent->payment_method,
                    'metadata' => $paymentIntent->metadata ?? [],
                ]
            );

            // Deduct product quantity
            $this->deductProductQuantity($data['product_id'], $data['quantity']);

            DB::commit();

            return ApiResponse::success($payment, 'Payment successful');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), null, 500);
        }
    }

    protected function deductProductQuantity(int $productId, int $quantity): void
    {
        $product = Product::lockForUpdate()->findOrFail($productId);

        if ($product->quantity < $quantity) {
            throw new \Exception('Insufficient product quantity');
        }

        $product->decrement('quantity', $quantity);
    }

    public function getUserPaymentHistory(int $userId, array $options = []): JsonResponse
    {
        return $this->repository->getUserPayments($userId, $options);
    }
}
