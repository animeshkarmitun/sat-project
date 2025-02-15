<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Subscribe a user to a plan.
     *
     * @param array $data
     * @return Subscription
     * @throws CustomException
     */
    public function subscribeUser(array $data): Subscription
    {
        $this->validateSubscriptionData($data);

        return DB::transaction(function () use ($data) {
            $subscription = Subscription::create([
                'user_id' => $data['user_id'],
                'plan_type' => $data['plan_type'],
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays($this->getPlanDuration($data['plan_type'])),
                'payment_status' => $data['payment_status'] ?? 'pending',
                'payment_gateway' => $data['payment_gateway'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'auto_renew' => $data['auto_renew'] ?? false,
                'discount_applied' => $data['discount_applied'] ?? 0,
            ]);

            Log::info('User subscribed', ['user_id' => $data['user_id'], 'plan_type' => $data['plan_type']]);
            return $subscription;
        });
    }

    /**
     * Validate subscription data.
     *
     * @param array $data
     * @throws CustomException
     */
    private function validateSubscriptionData(array $data): void
    {
        if (empty($data['user_id']) || empty($data['plan_type'])) {
            throw new CustomException('subscription.missing_required_fields', [], 400);
        }
    }

    /**
     * Get the duration of the subscription plan in days.
     *
     * @param string $planType
     * @return int
     */
    private function getPlanDuration(string $planType): int
    {
        return match ($planType) {
            'basic' => 30,
            'premium' => 90,
            'annual' => 365,
            default => 7,
        };
    }

    /**
     * Retrieve active subscriptions.
     *
     * @return array
     */
    public function getActiveSubscriptions(): array
    {
        return Subscription::where('end_date', '>=', Carbon::now())
            ->orderBy('end_date', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Retrieve a user's active subscription.
     *
     * @param int $userId
     * @return Subscription|null
     */
    public function getUserActiveSubscription(int $userId): ?Subscription
    {
        return Subscription::where('user_id', $userId)
            ->where('end_date', '>=', Carbon::now())
            ->first();
    }

    /**
     * Cancel a user subscription.
     *
     * @param int $subscriptionId
     * @throws CustomException
     */
    public function cancelSubscription(int $subscriptionId): void
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        $subscription->update(['end_date' => Carbon::now()]);
        Cache::forget("subscription_{$subscriptionId}");
        Log::info('Subscription canceled', ['subscription_id' => $subscriptionId]);
    }

    /**
     * Renew a subscription.
     *
     * @param int $subscriptionId
     * @throws CustomException
     */
    public function renewSubscription(int $subscriptionId): void
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        if ($subscription->auto_renew) {
            $newEndDate = Carbon::parse($subscription->end_date)->addDays($this->getPlanDuration($subscription->plan_type));
            $subscription->update(['end_date' => $newEndDate]);
            Cache::forget("subscription_{$subscriptionId}");
            Log::info('Subscription renewed', ['subscription_id' => $subscriptionId, 'new_end_date' => $newEndDate]);
        } else {
            throw new CustomException('subscription.auto_renew_disabled', [], 400);
        }
    }

    /**
     * Apply a discount to a subscription.
     *
     * @param int $subscriptionId
     * @param float $discountAmount
     * @throws CustomException
     */
    public function applyDiscount(int $subscriptionId, float $discountAmount): void
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        $subscription->update(['discount_applied' => $discountAmount]);
        Cache::forget("subscription_{$subscriptionId}");
        Log::info('Discount applied to subscription', ['subscription_id' => $subscriptionId, 'discount' => $discountAmount]);
    }
}
