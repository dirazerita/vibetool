<?php

namespace App\Providers;

use App\Models\License;
use App\Models\Message;
use App\Models\SoftwareRequest;
use App\Observers\LicenseObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        License::observe(LicenseObserver::class);

        View::composer(['layouts.dashboard', 'layouts.admin'], function ($view) {
            $user = auth()->user();
            if (! $user) {
                $view->with('memberUnreadMessages', 0)
                    ->with('adminUnreadMessages', 0)
                    ->with('memberUnseenSoftwareResponses', 0)
                    ->with('adminPendingSoftwareRequests', 0);

                return;
            }

            $memberUnread = (int) Message::query()
                ->where('user_id', $user->id)
                ->where('sender_role', Message::ROLE_ADMIN)
                ->whereNull('read_at')
                ->count();

            $adminUnread = $user->isAdmin()
                ? (int) Message::query()
                    ->where('sender_role', Message::ROLE_MEMBER)
                    ->whereNull('read_at')
                    ->count()
                : 0;

            $memberUnseenSoftwareResponses = (int) SoftwareRequest::query()
                ->where('user_id', $user->id)
                ->whereNotNull('admin_responded_at')
                ->where(function ($q) {
                    $q->whereNull('user_seen_response_at')
                        ->orWhereColumn('admin_responded_at', '>', 'user_seen_response_at');
                })
                ->count();

            $adminPendingSoftwareRequests = $user->isAdmin()
                ? (int) SoftwareRequest::query()
                    ->where('status', SoftwareRequest::STATUS_PENDING)
                    ->count()
                : 0;

            $view->with('memberUnreadMessages', $memberUnread)
                ->with('adminUnreadMessages', $adminUnread)
                ->with('memberUnseenSoftwareResponses', $memberUnseenSoftwareResponses)
                ->with('adminPendingSoftwareRequests', $adminPendingSoftwareRequests);
        });
    }
}
