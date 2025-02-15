<?php

namespace App\Services;

use App\Models\User;
use App\Models\Payment;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class PaymentService
{
    /**
     * Process a new payment for a user.
     *
     * @param int $userId
     * @param float $amount
     * @param string $paymentMethod
     * @param string|null $transactionId
     * @throws CustomException
     */
    public function processPayment(int $userId, float $amount, string $paymentMethod, ?string $transactionId = null): void
    {
        $user = User::findOrFail($userId);

        if ($amount <= 0) {
            throw new CustomException('payment.invalid_amount', [], 400);
        }

        DB::transaction(function () use ($userId, $amount, $paymentMethod, $transactionId) {
            Payment::create([
                'user_id' => $userId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'transaction_id' => $transactionId,
                'status' => 'completed',
                'payment_date' => now(),
            ]);
        });

        Cache::forget("user_payments_{$userId}");
        Log::info('Payment processed', ['user_id' => $userId, 'amount' => $amount, 'payment_method' => $paymentMethod]);
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

        if ($payment->status !== 'completed') {
            throw new CustomException('payment.not_eligible_for_refund', [], 400);
        }

        $payment->update(['status' => 'refunded']);
        Cache::forget("user_payments_{$payment->user_id}");
        Log::info('Payment refunded', ['payment_id' => $paymentId]);
    }

    /**
     * Get all payments for a user.
     *
     * @param int $userId
     * @return array
     */
    public function getUserPayments(int $userId): array
    {
        return Cache::remember("user_payments_{$userId}", 3600, function () use ($userId) {
            return Payment::where('user_id', $userId)
                ->orderBy('payment_date', 'desc')
                ->get()
                ->toArray();
        });
    }

    /**
     * Generate an invoice for a payment.
     *
     * @param int $paymentId
     * @return array
     * @throws CustomException
     */
    public function generateInvoice(int $paymentId): array
    {
        $payment = Payment::findOrFail($paymentId);

        return [
            'invoice_number' => 'INV-' . strtoupper(uniqid()),
            'user_id' => $payment->user_id,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'status' => $payment->status,
            'payment_date' => $payment->payment_date,
        ];
    }

    /**
     * Get payment details by transaction ID.
     *
     * @param string $transactionId
     * @return Payment|null
     */
    public function getPaymentByTransaction(string $transactionId): ?Payment
    {
        return Payment::where('transaction_id', $transactionId)->first();
    }

    /**
     * List all transactions within a date range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function listPaymentsByDateRange(Carbon $startDate, Carbon $endDate): array
    {
        return Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->orderBy('payment_date', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get total revenue for a given period.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    public function getTotalRevenue(Carbon $startDate, Carbon $endDate): float
    {
        return Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Get a list of all failed transactions.
     *
     * @return array
     */
    public function getFailedPayments(): array
    {
        return Payment::where('status', 'failed')
            ->orderBy('payment_date', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get payment statistics for reporting.
     *
     * @return array
     */
    public function getPaymentStats(): array
    {
        return [
            'total_payments' => Payment::count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'total_failed_payments' => Payment::where('status', 'failed')->count(),
            'total_refunded' => Payment::where('status', 'refunded')->sum('amount'),
        ];
    }
}