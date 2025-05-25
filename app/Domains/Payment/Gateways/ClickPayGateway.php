<?php

namespace App\Domains\Payment\Gateways;

use App\Domains\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClickPayGateway implements PaymentGatewayInterface
{
    private string $profileId;
    private string $serverKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->profileId = config('payment.clickpay.profile_id');
        $this->serverKey = config('payment.clickpay.server_key');
        $this->baseUrl = config('payment.clickpay.base_url');
    }

    public function createPayment(array $paymentData): array
    {
        try {
            $payload = [
                'profile_id' => $this->profileId,
                'tran_type' => 'sale',
                'tran_class' => 'ecom',
                'cart_id' => $paymentData['order_number'],
                'cart_description' => $paymentData['description'] ?? 'Order Payment',
                'cart_currency' => $paymentData['currency'] ?? 'SAR',
                'cart_amount' => $paymentData['amount'],
                'callback' => config('payment.callback_url'),
                'return' => config('payment.return_url'),
                'customer_details' => [
                    'name' => $paymentData['customer']['name'],
                    'email' => $paymentData['customer']['email'],
                    'phone' => $paymentData['customer']['phone'] ?? '',
                    'street1' => $paymentData['customer']['address'] ?? '',
                    'city' => $paymentData['customer']['city'] ?? 'Riyadh',
                    'state' => $paymentData['customer']['state'] ?? 'Riyadh',
                    'country' => $paymentData['customer']['country'] ?? 'SA',
                    'zip' => $paymentData['customer']['zip'] ?? '12345'
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => $this->serverKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/payment/request', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'payment_url' => $data['redirect_url'] ?? null,
                    'transaction_reference' => $data['tran_ref'] ?? null,
                    'raw_response' => $data
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Payment creation failed',
                'raw_response' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('ClickPay Payment Creation Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Payment gateway error occurred'
            ];
        }
    }

    public function verifyPayment(string $paymentReference): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->serverKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/payment/query', [
                'profile_id' => $this->profileId,
                'tran_ref' => $paymentReference
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $status = $this->mapPaymentStatus($data['payment_result']['response_status'] ?? 'F');

                return [
                    'success' => true,
                    'status' => $status,
                    'amount' => $data['cart_amount'] ?? 0,
                    'currency' => $data['cart_currency'] ?? 'SAR',
                    'transaction_id' => $data['tran_ref'] ?? null,
                    'raw_response' => $data
                ];
            }

            return [
                'success' => false,
                'error' => 'Payment verification failed'
            ];

        } catch (\Exception $e) {
            Log::error('ClickPay Payment Verification Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Payment verification error occurred'
            ];
        }
    }

    public function getPaymentStatus(string $paymentReference): string
    {
        $verification = $this->verifyPayment($paymentReference);
        return $verification['status'] ?? 'failed';
    }

    private function mapPaymentStatus(string $gatewayStatus): string
    {
        return match ($gatewayStatus) {
            'A' => 'paid',
            'H' => 'pending',
            default => 'failed'
        };
    }
}
