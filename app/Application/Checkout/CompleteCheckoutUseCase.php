<?php
namespace App\Application\Checkout;

use App\Domains\Order\Models\Order;
use App\Domains\Order\Repositories\OrderRepositoryInterface;
use App\Domains\Cart\Services\CartService;
use App\Application\Product\ReduceStockUseCase;
use App\Domains\Payment\Factory\PaymentGatewayFactory;
use Illuminate\Support\Facades\DB;

class CompleteCheckoutUseCase
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private CartService $cartService,
        private ReduceStockUseCase $reduceStockUseCase
    ) {}

    public function execute(string $paymentReference)
    {
        return DB::transaction(function () use ($paymentReference) {
            $order = Order::where('payment_reference', $paymentReference)->first();

            if (!$order) {
                throw new \Exception('Order not found');
            }

            $paymentGateway = PaymentGatewayFactory::create('clickpay');
            $paymentResult = $paymentGateway->verifyPayment($paymentReference);

            if (!$paymentResult['success']) {
                throw new \Exception('Payment verification failed');
            }

            if ($paymentResult['status'] === 'paid') {
                $this->orderRepository->update($order->id, [
                    'status' => Order::STATUS_PAID
                ]);

                foreach ($order->items as $item) {
                    $this->reduceStockUseCase->execute($item->product_id, $item->quantity);
                }

                $this->cartService->clearCart($order->user_id);

                return [
                    'success' => true,
                    'order' => $order->fresh(['items']),
                    'message' => 'Payment completed successfully'
                ];
            }

            if ($paymentResult['status'] === 'failed') {
                $this->orderRepository->update($order->id, [
                    'status' => Order::STATUS_CANCELLED
                ]);
            }

            return [
                'success' => false,
                'order' => $order->fresh(['items']),
                'status' => $paymentResult['status'],
                'message' => 'Payment not completed'
            ];
        });
    }
}
