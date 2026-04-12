<?php

namespace App\Notifications;

// Removed Queueable - send synchronously for reliability
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class ShipmentVerifiedNotification extends Notification
{
    // No queue - sent synchronously

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
            ->title('Barang Selesai Diverifikasi! ✅')
            ->icon('/logo.png')
            ->body('Petugas gudang telah memverifikasi muatan untuk truk ' . $this->shipment->vehicle_plate . '. Dokumen SJ sudah siap dicetak.')
            ->action('Lihat Detail', url('/shipments/' . $this->shipment->id))
            ->options(['TTL' => 1000]); 
    }
}
