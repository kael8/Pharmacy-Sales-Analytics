<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use Carbon\Carbon;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    //

    public function index()
    {
        $userId = auth()->id();
        $twoMonthsFromNow = Carbon::now()->addMonths(2);

        // Subquery to get the latest inventory record for each batch_id
        $latestInventoriesSubquery = DB::table('inventories as inv')
            ->select('inv.batch_id', DB::raw('MAX(inv.created_at) as latest_created_at'))
            ->groupBy('inv.batch_id');

        // Main query to get the latest inventory records with notifications
        $notifications = Inventory::leftJoin('notifications', function ($join) use ($userId) {
            $join->on('inventories.id', '=', 'notifications.inventory_id')
                ->where('notifications.user_id', $userId);
        })
            ->joinSub($latestInventoriesSubquery, 'latest_inv', function ($join) {
                $join->on('inventories.batch_id', '=', 'latest_inv.batch_id')
                    ->on('inventories.created_at', '=', 'latest_inv.latest_created_at');
            })
            ->where(function ($query) {
                $query->where('notifications.viewed', false)
                    ->orWhereNull('notifications.id');
            })
            ->with('product')
            ->orderBy('inventories.expiration_date', 'asc')
            ->get(['inventories.*', 'notifications.id as notification_id', 'notifications.viewed']);

        // Create notifications for the user if not already created
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

        $quantityNotifications = [];
        $expirationNotifications = [];

        // Add color coding based on the percentage of quantity remaining and expiration date
        foreach ($notifications as $notification) {
            // Calculate the initial quantity for the batch
            $initialQuantity = Inventory::where('batch_id', $notification->batch_id)
                ->where('action_type', 'added')
                ->sum('quantity');

            $currentQuantity = $notification->quantity;
            $percentageRemaining = ($currentQuantity / $initialQuantity) * 100;

            if ($percentageRemaining > 50) {
                $notification->quantity_color = 'green';
            } elseif ($percentageRemaining > 10) {
                $notification->quantity_color = 'yellow';
            } else {
                $notification->quantity_color = 'red';
            }

            // Attach the initial quantity to the notification
            $notification->initial_quantity = $initialQuantity;

            // Add color coding based on expiration date
            $expirationDate = Carbon::parse($notification->expiration_date);
            $daysUntilExpiration = $expirationDate->diffInDays(Carbon::now());

            if ($expirationDate <= $twoMonthsFromNow) {
                if ($daysUntilExpiration <= 7) {
                    $notification->expiration_color = 'red';
                } elseif ($daysUntilExpiration <= 30) {
                    $notification->expiration_color = 'yellow';
                } else {
                    $notification->expiration_color = 'green';
                }

                // Add to expiration notifications if within 2 months
                $notification->type = 'expiration';
                $expirationNotifications[] = $notification;
            }

            // Add to quantity notifications
            $notification->type = 'quantity';
            $quantityNotifications[] = $notification;
        }

        return response()->json([
            'quantity_notifications' => $quantityNotifications,
            'expiration_notifications' => $expirationNotifications,
        ]);
    }

    public function markAsViewed()
    {
        $userId = auth()->id();
        Notification::where('user_id', $userId)->update(['viewed' => true]);
    }
}
