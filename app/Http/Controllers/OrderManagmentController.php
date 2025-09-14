<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Enum\OrderStatus;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class OrderManagmentController extends Controller
{
    //index 
    public function index(Request $request)
    {
        // Validate request parameters
        $request->validate([
            'status' => 'in:' . implode(',', OrderStatus::values()),
            'from_date' => 'date',
            'to_date' => 'date',
        ]);
        $query = Order::with(['user', 'items.product']);
        // Build query with optional filters
        $query = Order::with(['user', 'items.product']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Get orders with pagination
        $orders = $query->latest()->paginate(15); // 15 items per page

        return response()->json([
            'orders' => $orders,
            'available_statuses' => OrderStatus::values(),
        ]);
    }

        public function show(Order $order)
    {
        // Load all related data for admin view
        $order->load([
            'user',
            'items.product',
            'statusHistory.changedBy'
        ]);

        return response()->json([
            'order' => $order,
            'available_transitions' => $order->getAllowedTransitions(),
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        // Validate the new status
        $request->validate([
            'status' => 'required|string|in:' . implode(',', OrderStatus::values()),
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            // Convert string to enum
            $newStatus = OrderStatus::from($request->status);

            // Attempt the transition
            $order->transitionTo($newStatus, Auth::user(), $request->notes);

            // Reload order with fresh data
            $order->load(['statusHistory.changedBy']);

            return response()->json([
                'success' => true,
                'message' => "Order status updated to {$newStatus->getLabel()}",
                'order' => $order,
                'available_transitions' => $order->getAllowedTransitions(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function cancel(Request $request, Order $order)
    {
        $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        try {
            // Check if order can be cancelled
            if (!$order->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This order cannot be cancelled in its current status.',
                ], 400);
            }

            // Cancel the order
            $order->transitionTo(OrderStatus::CANCELLED, Auth::user(), "Cancelled: " . $request->notes);

            return response()->json([
                'success' => true,
                'message' => 'Order has been cancelled',
                'order' => $order->fresh(['statusHistory.changedBy']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
