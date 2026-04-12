<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Return in-app notifications for the currently authenticated user.
     * Operator sees: pending draft shipments (need their verification)
     * Admin sees: recently verified shipments (need post-processing)
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = [];

        if ($user->role === 'operator') {
            // Pending shipments waiting for physical verification
            $pending = Shipment::with(['purchaseOrder'])
                ->where('status', 'draft')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            foreach ($pending as $s) {
                $notifications[] = [
                    'id'        => 'ship-draft-' . $s->id,
                    'type'      => 'warning',
                    'title'     => 'Verifikasi Diperlukan 🚨',
                    'body'      => 'Truk ' . ($s->vehicle_plate ?: '-') . ' menunggu verifikasi fisik.',
                    'time'      => $s->created_at->diffForHumans(),
                    'url'       => route('shipments.verification'),
                ];
            }
        } elseif ($user->role === 'admin') {
            // Recently verified shipments (last 24h)
            $verified = Shipment::where('status', 'verified')
                ->orderBy('verified_at', 'desc')
                ->take(10)
                ->get();

            foreach ($verified as $s) {
                $notifications[] = [
                    'id'        => 'ship-verified-' . $s->id,
                    'type'      => 'success',
                    'title'     => 'Barang Terverifikasi ✅',
                    'body'      => 'Truk ' . ($s->vehicle_plate ?: '-') . ' selesai diverifikasi. Dokumen siap dicetak.',
                    'time'      => $s->verified_at ? $s->verified_at->diffForHumans() : '-',
                    'url'       => route('shipments.show', $s->id),
                ];
            }

            // Pending drafts (needing operator verification yet, so admin is informed)
            $pending = Shipment::where('status', 'draft')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            foreach ($pending as $s) {
                $notifications[] = [
                    'id'        => 'ship-draft-admin-' . $s->id,
                    'type'      => 'info',
                    'title'     => 'Pengiriman Menunggu ⏳',
                    'body'      => 'Truk ' . ($s->vehicle_plate ?: '-') . ' menanti verifikasi petugas.',
                    'time'      => $s->created_at->diffForHumans(),
                    'url'       => route('shipments.show', $s->id),
                ];
            }
        }

        return response()->json([
            'count'         => count($notifications),
            'notifications' => $notifications,
        ]);
    }
}
