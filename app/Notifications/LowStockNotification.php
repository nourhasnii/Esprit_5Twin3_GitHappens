<?php

namespace App\Notifications;

use App\Support\Fmt;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $siteId,
        public string $siteName,
        public int $productId,
        public string $productName,
        public float $available,
        public float $threshold,
    ) {}

    public function via(object $notifiable): array
    {
        return config('stock.alerts.mail') ? ['database', 'mail'] : ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'site_id' => $this->siteId,
            'product_id' => $this->productId,
            'available' => $this->available,
            'threshold' => $this->threshold,
            'message' => $this->message(),
            'url' => route('stocks.index', ['site_id' => $this->siteId, 'state' => 'low']),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Stock bas : '.$this->productName.' à '.$this->siteName)
            ->line($this->message())
            ->action('Voir les stocks du site', route('stocks.index', ['site_id' => $this->siteId]));
    }

    private function message(): string
    {
        return sprintf(
            '%s passe sous son seuil à %s : %s disponible(s) pour un seuil de %s.',
            $this->productName, $this->siteName, Fmt::q($this->available), Fmt::q($this->threshold)
        );
    }
}
