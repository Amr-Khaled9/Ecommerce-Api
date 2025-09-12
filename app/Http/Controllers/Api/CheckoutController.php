<?php

namespace App\Http\Controllers\Api;

use App\Enum\OrderStatus;
use App\Enum\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    // Check out to place order and save it to DB
    public function checkout(CheckoutRequest $request)
    {
        // get user
        $user = $request->user();
        // get product in Cart
        $cartItem = Cart::where('user_id', $user->id)->get();
        // check cart Item
        if ($cartItem->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Your cart is empty',
            ], 400);
        }
        //price
        $subtotal = 0;
        $items = [];

        foreach ($cartItem as $item) {
            $product = $item->product;
            //check product is active
            if (!$product->is_active() ) {
                return response()->json([
                    'status' => false,
                    'message' => "{$product->name} is no longer not available",
                ], 400);
            }
            // check product is found
            if ( $product->stock < $item->quantity) {
                return response()->json([
                    'status' => false,
                    'message' => "{$product->name} is no longer not available sto0ck",
                ], 400);
            }
            $itemSubTotal = round($product->stock * $item->quantity); // عشان القريب
            $subtotal += $itemSubTotal;
            $items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $item->quantity,
                'price' => $product->price,
                'subtotal' => $itemSubTotal,
            ];
        }

//        tax and shipping_cost
        $tax = round($subtotal * 0.08, 2);
        $shippingCost = 5.00;
        $total = round($subtotal + $tax + $shippingCost, 2);

//        create order with database transaction
        DB::beginTransaction();
        try {
            $order = new Order([
                'user_id' => $user->id,
                'status' => OrderStatus::PENDING,
                'shipping_name' => $request->shipping_name,
                'shipping_address' => $request->shipping_address,
                'shipping_city' => $request->shipping_city,
                'shipping_status' => $request->shipping_state,
                'shipping_zipcode' => $request->shipping_zipcode,
                'shipping_country' => $request->shipping_country,
                'shipping_phone' => $request->shipping_phone,
                'subtotal' => $subtotal,
                'tex' => $tax,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'payment_status' => PaymentStatus::PENDING,
                'order_number' => Order::generateOrderNumber(),
                'notes' => $request->notes,
            ]);

            $user->orders()->save($order);

            // Save order items & update stock
            foreach ($items as $item) {
                $order->items()->create($item);
                Product::where('id', $item['product_id'])
                    ->decrement('stock', $item['quantity']);
            }

            // Clear the user's cart
            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully',
                'data' => $order->load('items'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to place order',
                'error' => $e->getMessage(),
            ], 500);
        }


    }

    // simulate index method to return a list of orders called orderhistory
    public function orderHistory(Request $request)
    {
        $user = $request->user();
        $orders = $user->orders()->with('items')->get();

        return response()->json([
            'status' => true,
            'message' => 'Order history retrieved successfully',
            'data' => $orders,
        ]);
    }

    // simulate show method to return a single order by id called orderDetails
    public function orderDetails(Request $request, $id)
    {
        $user = $request->user();
        $order = $user->orders()->with('items')->find($id);

        if (!$order) {
            return response()->json(['status' => false ,'message' => 'Order not found'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Order details retrieved successfully',
            'data' => $order,

        ]);
    }
}
