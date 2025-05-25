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
                return $this->errorResponse('Order not found', 404);
            }

            if ($order->status === 'paid') {
                return $this->successResponse(
                    new OrderResource($order),
                    'Payment already completed'
                );
            }

            $result = $this->completeCheckoutUseCase->execute($paymentReference);

            return $result['success']
                ? $this->successResponse(new OrderResource($result['order']), $result['message'])
                : $this->errorResponse($result['message'], 400);

        } catch (\Exception $e) {
            Log::error('Payment callback error', [
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
                Log::warning('ClickPay verification failed', [
                    'tran_ref' => $transactionReference,
                    'error' => $e->getMessage()
                ]);

                return $this->successResponse(
                    new OrderStatusResource($order),
                    'Payment status retrieved (verification pending)'
                );
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get payment status', 400);
        }
    }

    public function mockCallback(Request $request)
    {
        try {
            $paymentReference = $request->input('tran_ref');
            $order = $this->orderService->findByPaymentReference($paymentReference);

            if (!$order) {
                return $this->errorResponse('Order not found', 404);
            }

            $this->orderService->updateStatus($order->id, 'paid');
            $this->orderService->processSuccessfulPayment($order);

            return $this->successResponse(
                new OrderResource($order->fresh(['items'])),
                'Payment completed successfully (MOCK)'
            );

        } catch (\Exception $e) {
            return $this->errorResponse('Mock payment failed', 500);
        }
    }

}
