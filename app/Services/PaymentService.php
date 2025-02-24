<?php
/**
 * Class PaymentService
 *
 * Handles payment processing, retrieval, refunds, and financial reporting.
 *
 * Function Descriptions:
 * - processPayment(array $data) - Processes a payment for a subscription and updates the payment status.
 * - validatePaymentData(array $data) - Validates payment request data before processing.
 * - getPaymentDetails(int $paymentId) - Retrieves details of a specific payment transaction.
 * - getUserPayments(int $userId) - Retrieves all payments made by a specific user.
 * - refundPayment(int $paymentId) - Processes a refund for a completed payment.
 * - getPaymentsByStatus(string $status) - Retrieves payments filtered by their status (e.g., pending, completed, refunded).
 * - getTotalRevenue() - Computes the total revenue generated from completed payments.
 * - getTotalCompletedPayments() - Counts the number of successfully completed payments.
 */


namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PaymentService
{
    /**
     * Process a payment for a subscription.
     *
     * @param array $data
     * @return Payment
     * @throws CustomException
     */
    public function processPayment(array $data): Payment
    {
        $this->validatePaymentData($data);

        return DB::transaction(function () use ($data) {
            $payment = Payment::create([
                'user_id' => $data['user_id'],
                'subscription_id' => $data['subscription_id'],
                'amount' => $data['amount'],
                'payment_gateway' => $data['payment_gateway'],
                'transaction_id' => $data['transaction_id'] ?? null,
                'payment_status' => $data['payment_status'] ?? 'pending',
                'paid_at' => $data['payment_status'] === 'completed' ? Carbon::now() : null,
                'currency' => $data['currency'] ?? 'USD',
                'payment_method' => $data['payment_method'] ?? 'credit_card',
            ]);

            if ($data['payment_status'] === 'completed') {
                Subscription::where('id', $data['subscription_id'])->update(['payment_status' => 'completed']);
            }

            Log::info('Payment processed', ['payment_id' => $payment->id, 'user_id' => $data['user_id']]);
            return $payment;
        });
    }

    /**
     * Validate payment data.
     *
     * @param array $data
     * @throws CustomException
     */
    private function validatePaymentData(array $data): void
    {
        if (empty($data['user_id']) || empty($data['subscription_id']) || empty($data['amount']) || empty($data['payment_gateway'])) {
            throw new CustomException('payment.missing_required_fields', [], 400);
        }
    }

    /**
     * Retrieve payment details.
     *
     * @param int $paymentId
     * @return Payment|null
     */
    public function getPaymentDetails(int $paymentId): ?Payment
    {
        return Cache::remember("payment_{$paymentId}", 3600, function () use ($paymentId) {
            return Payment::find($paymentId);
        });
    }

    /**
     * Retrieve all payments for a user.
     *
     * @param int $userId
     * @return array
     */
    public function getUserPayments(int $userId): array
    {
        return Payment::where('user_id', $userId)
            ->orderBy('paid_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Refund a payment.
     *
     * @param int $paymentId
     * @throws CustomException
     */
    public function refundPayment(int $paymentId): void
    {
        $payment = Payment::findOrFail($paymentId);
        if ($payment->payment_status !== 'completed') {
            throw new CustomException('payment.cannot_refund_uncompleted', [], 400);
        }

        $payment->update(['payment_status' => 'refunded']);
        Log::info('Payment refunded', ['payment_id' => $paymentId]);
    }

    /**
     * Retrieve payments filtered by status.
     *
     * @param string $status
     * @return array
     */
    public function getPaymentsByStatus(string $status): array
    {
        return Payment::where('payment_status', $status)
            ->orderBy('paid_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Retrieve total revenue from completed payments.
     *
     * @return float
     */
    public function getTotalRevenue(): float
    {
        return Payment::where('payment_status', 'completed')->sum('amount');
    }

    /**
     * Retrieve the number of completed payments.
     *
     * @return int
     */
    public function getTotalCompletedPayments(): int
    {
        return Payment::where('payment_status', 'completed')->count();
    }
}
