<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Application\Checkout\InitiateCheckoutUseCase;
use App\Application\Checkout\CompleteCheckoutUseCase;
use App\Http\Requests\Checkout\InitiateCheckoutRequest;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\Order\OrderStatusResource;
use App\Domains\Order\Services\OrderService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private InitiateCheckoutUseCase $initiateCheckoutUseCase,
        private CompleteCheckoutUseCase $completeCheckoutUseCase,
        private OrderService $orderService
    ) {}

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
            ], 'Checkout initiated successfully', 201);

        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function callback(Request $request)
    {
        try {
            $paymentReference = $request->input('tran_ref');

            if (!$paymentReference) {
                return $this->errorResponse('Payment reference is required', 400);
            }

            Log::info('Payment callback received', [
                'tran_ref' => $paymentReference,
                'request_data' => $request->all()
            ]);

            $order = $this->orderService->findByPaymentReference($paymentReference);
            if (!$order) {
                Log::error('Order not found for payment reference', [
                    'tran_ref' => $paymentReference
                ]);
                return $this->errorResponse('Order not found', 404);
            }

            if ($order->status === 'paid') {
                Log::info('Order already paid', [
                    'order_id' => $order->id,
                    'tran_ref' => $paymentReference
                ]);
                return $this->successResponse(
                    new OrderResource($order),
                    'Payment already completed'
                );
            }

            try {
                $result = $this->completeCheckoutUseCase->execute($paymentReference);

                if ($result['success']) {
                    Log::info('Payment completed successfully', [
                        'order_id' => $result['order']->id,
                        'tran_ref' => $paymentReference
                    ]);

                    return $this->successResponse(
                        new OrderResource($result['order']),
                        $result['message']
                    );
                } else {
                    Log::warning('Payment not completed', [
                        'order_id' => $order->id,
                        'status' => $result['status'] ?? 'unknown',
                        'message' => $result['message']
                    ]);

                    return $this->errorResponse($result['message'], 400);
                }

            } catch (\Exception $verificationError) {
                Log::error('ClickPay verification failed in callback', [
                    'tran_ref' => $paymentReference,
                    'order_id' => $order->id,
                    'error' => $verificationError->getMessage(),
                    'trace' => $verificationError->getTraceAsString()
                ]);

                return $this->errorResponse(
                    'Payment verification failed: ' . $verificationError->getMessage(),
                    400
                );
            }

        } catch (\Exception $e) {
            Log::error('Payment callback error', [
                'tran_ref' => $paymentReference ?? 'unknown',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Payment processing failed', 500);
        }
    }

    public function checkoutStatus($transactionReference)
    {
        try {
            $order = $this->orderService->findByPaymentReference($transactionReference);

            if (!$order) {
                return $this->errorResponse('Order not found', 404);
            }

            Log::info('Checking payment status', [
                'tran_ref' => $transactionReference,
                'order_id' => $order->id,
                'current_status' => $order->status
            ]);

            if (in_array($order->status, ['paid', 'cancelled'])) {
                return $this->successResponse(
                    new OrderStatusResource($order),
                    'Payment status retrieved'
                );
            }

            try {
                $result = $this->completeCheckoutUseCase->execute($transactionReference);
                return $this->successResponse(
                    new OrderStatusResource($result['order']),
                    'Payment status retrieved'
                );
            } catch (\Exception $e) {
                Log::warning('ClickPay verification failed in status check', [
                    'tran_ref' => $transactionReference,
                    'error' => $e->getMessage()
                ]);

                return $this->successResponse(
                    new OrderStatusResource($order),
                    'Payment status retrieved (verification temporarily unavailable)'
                );
            }

        } catch (\Exception $e) {
            Log::error('Status check error', [
                'tran_ref' => $transactionReference,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Failed to get payment status', 400);
        }
    }

    public function mockCallback(Request $request)
    {
        try {
            $paymentReference = $request->input('tran_ref');

            if (!$paymentReference) {
                return $this->errorResponse('Payment reference is required', 400);
            }

            $order = $this->orderService->findByPaymentReference($paymentReference);

            if (!$order) {
                return $this->errorResponse('Order not found', 404);
            }

            if ($order->status === 'paid') {
                return $this->successResponse(
                    new OrderResource($order),
                    'Payment already completed (MOCK)'
                );
            }

            Log::info('Processing mock payment', [
                'order_id' => $order->id,
                'tran_ref' => $paymentReference
            ]);

            $this->orderService->updateStatus($order->id, 'paid');

            $this->orderService->processSuccessfulPayment($order);

            return $this->successResponse(
                new OrderResource($order->fresh(['items'])),
                'Payment completed successfully (MOCK)'
            );

        } catch (\Exception $e) {
            Log::error('Mock payment failed', [
                'tran_ref' => $paymentReference ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Mock payment failed', 500);
        }
    }

    public function resetOrder(Request $request)
    {
        try {
            $paymentReference = $request->input('tran_ref');

            if (!$paymentReference) {
                return $this->errorResponse('Payment reference is required', 400);
            }

            $order = $this->orderService->findByPaymentReference($paymentReference);

            if (!$order) {
                return $this->errorResponse('Order not found', 404);
            }

            $this->orderService->updateStatus($order->id, 'pending');

            Log::info('Order reset to pending', [
                'order_id' => $order->id,
                'tran_ref' => $paymentReference
            ]);

            return $this->successResponse(
                new OrderResource($order->fresh()),
                'Order reset to pending status'
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to reset order', 500);
        }
    }
}
