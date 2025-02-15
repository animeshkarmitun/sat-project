<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SubscriptionService
{
    /**
     * Create a new subscription for a user.
     *
     * @param int $userId
     * @param string $planType
     * @param Carbon $endDate
     * @throws CustomException
     */
    public function createSubscription(int $userId, string $planType, Carbon $endDate): void
    {
        $user = User::findOrFail($userId);

        if (Subscription::where('user_id', $userId)->where('status', 'active')->exists()) {
            throw new CustomException('subscription.already_active', [], 400);
        }

        DB::transaction(function () use ($userId, $planType, $endDate) {
            Subscription::create([
                'user_id' => $userId,
                'plan_type' => $planType,
                'status' => 'active',
                'start_date' => now(),
                'end_date' => $endDate,
                'auto_renew' => true,
            ]);
        });

        Cache::forget("user_subscription_{$userId}");
        Log::info('Subscription created', ['user_id' => $userId, 'plan_type' => $planType]);
    }

    /**
     * Cancel a user's subscription.
     *
     * @param int $userId
     * @throws CustomException
     */
    public function cancelSubscription(int $userId): void
    {
        $subscription = Subscription::where('user_id', $userId)->where('status', 'active')->firstOrFail();
        
        $subscription->update(['status' => 'canceled', 'auto_renew' => false]);
        
        Cache::forget("user_subscription_{$userId}");
        Log::info('Subscription canceled', ['user_id' => $userId]);
    }

    /**
     * Check if a user has an active subscription.
     *
     * @param int $userId
     * @return bool
     */
    public function hasActiveSubscription(int $userId): bool
    {
        return Cache::remember("user_subscription_{$userId}", 3600, function () use ($userId) {
            return Subscription::where('user_id', $userId)
                ->where('status', 'active')
                ->where('end_date', '>', now())
                ->exists();
        });
    }

    /**
     * Renew a user's subscription.
     *
     * @param int $userId
     * @param Carbon $newEndDate
     * @throws CustomException
     */
    public function renewSubscription(int $userId, Carbon $newEndDate): void
    {
        $subscription = Subscription::where('user_id', $userId)->where('status', 'active')->firstOrFail();
        
        $subscription->update(['end_date' => $newEndDate]);
        
        Cache::forget("user_subscription_{$userId}");
        Log::info('Subscription renewed', ['user_id' => $userId, 'new_end_date' => $newEndDate]);
    }

    /**
     * Get a user's subscription details.
     *
     * @param int $userId
     * @return Subscription|null
     */
    public function getUserSubscription(int $userId): ?Subscription
    {
        return Cache::remember("user_subscription_{$userId}", 3600, function () use ($userId) {
            return Subscription::where('user_id', $userId)->orderBy('start_date', 'desc')->first();
        });
    }

    /**
     * List all active subscriptions.
     *
     * @return array
     */
    public function listActiveSubscriptions(): array
    {
        return Subscription::where('status', 'active')
            ->orderBy('end_date', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Auto-expire subscriptions that have reached their end date.
     */
    public function expireSubscriptions(): void
    {
        $expiredSubscriptions = Subscription::where('status', 'active')
            ->where('end_date', '<=', now())
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            $subscription->update(['status' => 'expired', 'auto_renew' => false]);
            Cache::forget("user_subscription_{$subscription->user_id}");
            Log::info('Subscription expired', ['user_id' => $subscription->user_id]);
        }
    }
}
