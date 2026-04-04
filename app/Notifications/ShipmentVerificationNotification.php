<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class ShipmentVerificationNotification extends Notification
{
    use Queueable;

    protected $shipment;

    /**
     * Create a new notification instance.
     */
    public function __construct($shipment)
    {
        $this->shipment = $shipment;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('Verifikasi Surat Jalan Baru! 🚨')
            ->icon('/logo.png') // Pastikan ada logo aplikasi di folder public
            ->body('Truk ' . $this->shipment->vehicle_plate . ' menunggu verifikasi fisik di lapangan untuk tujuan ' . $this->shipment->transporter_name . ' (' . $this->shipment->documented_qty_kg . ' KG).')
            ->action('Verifikasi Sekarang', url('/shipments/verification'))
            ->options(['TTL' => 1000]); // TTL in seconds
    }
}
