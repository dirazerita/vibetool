<?php

namespace App\Observers;

use App\Models\License;
use App\Services\WebhookDispatcher;

class LicenseObserver
{
    public function __construct(private readonly WebhookDispatcher $dispatcher) {}

    /**
     * Fire license.issued only when the row is created with both an order and
     * a user — i.e. a real allocation, not an unassigned key in the pool.
     */
    public function created(License $license): void
    {
        if ($license->order_id && $license->user_id && $license->assigned_at) {
            $this->dispatcher->dispatchForLicense($license, WebhookDispatcher::EVENT_ISSUED);
        }
    }

    /**
     * Fire license.renewed when expires_at moves forward. Also fire
     * license.issued if an unassigned key was just assigned to a user/order.
     */
    public function updated(License $license): void
    {
        // Just got assigned to a user/order — treat as issuance.
        if (
            $license->wasChanged('order_id')
            && $license->order_id
            && $license->user_id
        ) {
            $this->dispatcher->dispatchForLicense($license, WebhookDispatcher::EVENT_ISSUED);

            return;
        }

        if (! $license->wasChanged('expires_at')) {
            return;
        }

        $oldExpiresAt = $license->getOriginal('expires_at');
        $newExpiresAt = $license->expires_at;

        // From any state to lifetime, or to a later date — both count as renewal.
        $isRenewal = $newExpiresAt === null
            || $oldExpiresAt === null
            || ($newExpiresAt instanceof \DateTimeInterface && (string) $newExpiresAt > (string) $oldExpiresAt);

        if (! $isRenewal) {
            return;
        }

        // Don't notify for unassigned keys
        if (! $license->order_id || ! $license->user_id) {
            return;
        }

        $this->dispatcher->dispatchForLicense($license, WebhookDispatcher::EVENT_RENEWED);
    }

    /**
     * Fire license.revoked only when an assigned license is deleted. Deleting
     * an unassigned pool key is not user-visible and shouldn't notify.
     * Runs BEFORE the DELETE so the foreign key for webhook_deliveries.license_id
     * is still satisfied; if we ran after delete the license row is already gone.
     */
    public function deleting(License $license): void
    {
        if ($license->order_id && $license->user_id) {
            $this->dispatcher->dispatchForLicense($license, WebhookDispatcher::EVENT_REVOKED);
        }
    }
}
