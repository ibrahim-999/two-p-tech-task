<?php

namespace App\Domains\Payment\Gateways;

use App\Domains\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Http\Client\ConnectionException;
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
            if (! $this->isConfigurationValid()) {
                return $this->errorResponse('Payment gateway configuration is missing');
            }

            $payload = $this->buildPaymentPayload($paymentData);
            $response = $this->sendPaymentRequest($payload);

            return $this->processPaymentResponse($response);

        } catch (ConnectionException $e) {
            Log::error('ClickPay Connection Error', ['message' => $e->getMessage()]);

            return $this->errorResponse('Unable to connect to payment gateway');
        } catch (\Exception $e) {
            Log::error('ClickPay Payment Creation Error', ['message' => $e->getMessage()]);

            return $this->errorResponse('Payment gateway error occurred');
        }
    }

    public function verifyPayment(string $paymentReference): array
    {
        try {
            $payload = [
                'profile_id' => (int) $this->profileId,
                'tran_ref' => $paymentReference,
            ];

            $response = $this->sendVerificationRequest($payload);

            return $this->processVerificationResponse($response, $paymentReference);

        } catch (\Exception $e) {
            Log::error('ClickPay Payment Verification Error', [
                'message' => $e->getMessage(),
                'tran_ref' => $paymentReference,
            ]);

            return $this->errorResponse('Payment verification error');
        }
    }

    public function getPaymentStatus(string $paymentReference): string
    {
        $verification = $this->verifyPayment($paymentReference);

        return $verification['status'] ?? 'failed';
    }

    private function isConfigurationValid(): bool
    {
        return ! empty($this->profileId) && ! empty($this->serverKey);
    }

    private function buildPaymentPayload(array $paymentData): array
    {
        return [
            'profile_id' => (int) $this->profileId,
            'tran_type' => 'sale',
            'tran_class' => 'ecom',
            'cart_id' => $paymentData['order_number'],
            'cart_amount' => (float) $paymentData['amount'],
            'cart_currency' => $paymentData['currency'] ?? 'EGP',
            'cart_description' => $paymentData['description'] ?? 'Order Payment',
            'paypage_lang' => 'en',
            'customer_details' => $paymentData['customer'],
            'shipping_details' => $paymentData['shipping'],
            'callback' => config('payment.callback_url'),
            'return' => config('payment.return_url'),
            'user_defined' => [
                'udf3' => $paymentData['order_number'],
                'udf9' => 'Laravel Ecommerce',
            ],
        ];
    }

    private function sendPaymentRequest(array $payload)
    {
        Log::info('ClickPay Payment Request', [
            'cart_amount' => $payload['cart_amount'],
            'cart_currency' => $payload['cart_currency'],
        ]);

        return Http::timeout(30)
            ->withHeaders([
                'authorization' => $this->serverKey,
                'Content-Type' => 'application/json',
            ])
            ->post($this->baseUrl.'/payment/request', $payload);
    }

    private function sendVerificationRequest(array $payload)
    {
        return Http::timeout(30)
            ->withHeaders([
                'authorization' => $this->serverKey,
                'Content-Type' => 'application/json',
            ])
            ->post($this->baseUrl.'/payment/query', $payload);
    }

    private function processPaymentResponse($response): array
    {
        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['redirect_url']) || isset($data['payment_url'])) {
                return [
                    'success' => true,
                    'payment_url' => $data['redirect_url'] ?? $data['payment_url'],
                    'transaction_reference' => $data['tran_ref'] ?? null,
                    'raw_response' => $data,
                ];
            }

            return $this->errorResponse($data['message'] ?? 'No payment URL received');
        }

        $errorResponse = $response->json();

        return $this->errorResponse(
            $errorResponse['message'] ?? 'Payment creation failed',
            $errorResponse
        );
    }

    private function processVerificationResponse($response, string $paymentReference): array
    {
        if ($response->successful()) {
            $data = $response->json();
            $responseStatus = $data['payment_result']['response_status'] ??
                $data['response_status'] ??
                $data['status'] ?? 'F';

            return [
                'success' => true,
                'status' => $this->mapPaymentStatus($responseStatus),
                'amount' => $data['cart_amount'] ?? 0,
                'currency' => $data['cart_currency'] ?? 'EGP',
                'transaction_id' => $data['tran_ref'] ?? $paymentReference,
                'raw_response' => $data,
            ];
        }

        return $this->errorResponse('Payment verification failed', $response->json());
    }

    private function mapPaymentStatus(string $gatewayStatus): string
    {
        return match (strtoupper($gatewayStatus)) {
            'A', 'APPROVED', 'SUCCESS' => 'paid',
            'H', 'HELD', 'PENDING' => 'pending',
            'P', 'PROCESSING' => 'pending',
            'V', 'VOID' => 'cancelled',
            default => 'failed'
        };
    }

    private function errorResponse(string $message, array $rawResponse = []): array
    {
        return [
            'success' => false,
            'error' => $message,
            'raw_response' => $rawResponse,
        ];
    }
}
