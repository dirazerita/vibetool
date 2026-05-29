<?php

namespace App\Providers;

use App\Models\Message;
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
        View::composer(['layouts.dashboard', 'layouts.admin'], function ($view) {
            $user = auth()->user();
            if (! $user) {
                $view->with('memberUnreadMessages', 0)->with('adminUnreadMessages', 0);

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

            $view->with('memberUnreadMessages', $memberUnread)
                ->with('adminUnreadMessages', $adminUnread);
        });
    }
}
