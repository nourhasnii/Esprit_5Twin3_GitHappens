<?php

namespace App\Listeners;

use App\Events\StockLevelLow;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

/** Découvert automatiquement par Laravel 12 (dossier app/Listeners). */
class NotifyLowStock
{
    public function handle(StockLevelLow $event): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        // À restreindre selon la gestion des rôles du projet (ex: ->where('role', 'admin'))
        $recipients = User::query()->get();

        Notification::send($recipients, new LowStockNotification(
            siteId: $event->site->getKey(),
            siteName: $event->site->name,
            productId: $event->product->getKey(),
            productName: (string) $event->product->name,
            available: $event->available,
            threshold: $event->threshold,
        ));
    }
}
