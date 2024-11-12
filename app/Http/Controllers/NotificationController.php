<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use Carbon\Carbon;
use App\Models\Notification;

class NotificationController extends Controller
{
    //

    public function index()
    {
        $twoMonthsFromNow = Carbon::now()->addMonths(2);

        $notifications = Inventory::leftJoin('notifications', function ($join) {
            $join->on('inventories.id', '=', 'notifications.inventory_id')
                ->where('notifications.user_id', auth()->id());
        })
            ->where(function ($query) {
                $query->where('notifications.viewed', false)
                    ->orWhereNull('notifications.id');
            })
            ->where('inventories.expiration_date', '<=', $twoMonthsFromNow)
            ->with('product')
            ->orderBy('inventories.expiration_date', 'asc')
            ->get(['inventories.*', 'notifications.id as notification_id', 'notifications.viewed']);

        // Create notifications for the user if not already created
        $userId = auth()->id();
        foreach ($notifications as $inventory) {
            Notification::firstOrCreate(
                ['inventory_id' => $inventory->id, 'user_id' => $userId],
                ['viewed' => false]
            );
        }

        // Fetch notifications with viewed status
        $userNotifications = Notification::where('user_id', $userId)
            ->with('inventory.product')
            ->orderBy('created_at', 'desc')
            ->get();



        return response()->json($notifications);
    }

    public function markAsViewed()
    {
        $userId = auth()->id();
        Notification::where('user_id', $userId)->update(['viewed' => true]);
    }
}
