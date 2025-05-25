<?php
// app/Http/Controllers/Api/v1/CheckoutController.php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Application\Checkout\InitiateCheckoutUseCase;
use App\Application\Checkout\CompleteCheckoutUseCase;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private InitiateCheckoutUseCase $initiateCheckoutUseCase,
        private CompleteCheckoutUseCase $completeCheckoutUseCase
    ) {}

    /**
     * Initiate Checkout
     * POST /api/v1/checkout
     */
    public function initiate(InitiateCheckoutRequest $request)
    {
        try {
            $result = $this->initiateCheckoutUseCase->execute(
                $request->user()->id,
                $request->validated()
            );

            return $this->successResponse([
                'order_id' => $result['order']->id,
                'order_number' => $result['order']->order_number,
                'total_amount' => $result['order']->total_amount,
                'payment_url' => $result['payment_url'],
                'transaction_reference' => $result['transaction_reference']
            ], 'Checkout initiated successfully. Please complete payment.', 201);

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Payment Callback (called by ClickPay)
     * POST /api/v1/payments/callback
     */
    public function callback(Request $request)
    {
        try {
            $paymentReference = $request->input('tran_ref');

            if (!$paymentReference) {
                return $this->errorResponse('Payment reference is required', 400);
            }

            $result = $this->completeCheckoutUseCase->execute($paymentReference);

            if ($result['success']) {
                return $this->successResponse([
                    'order' => [
                        'id' => $result['order']->id,
                        'order_number' => $result['order']->order_number,
                        'status' => $result['order']->status,
                        'total_amount' => $result['order']->total_amount,
                        'items' => $result['order']->items->map(function ($item) {
                            return [
                                'product_name' => $item->product_name,
                                'quantity' => $item->quantity,
                                'price' => $item->price,
                                'total' => $item->quantity * $item->price
                            ];
                        })
                    ]
                ], $result['message']);
            }

            return $this->errorResponse($result['message'], 400);

        } catch (\Exception $e) {
            return $this->errorResponse('Payment processing failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Check Payment Status
     * GET /api/v1/checkout/status/{transactionReference}
     */
    public function status($transactionReference)
    {
        try {
            $result = $this->completeCheckoutUseCase->execute($transactionReference);

            return $this->successResponse([
                'order' => $result['order'],
                'payment_status' => $result['order']->status,
                'success' => $result['success']
            ], 'Payment status retrieved');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get payment status: ' . $e->getMessage(), 400);
        }
    }
}
