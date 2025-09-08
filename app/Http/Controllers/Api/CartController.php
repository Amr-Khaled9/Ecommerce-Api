<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartStoreRequest;
use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{

    public function index(Request $request)
    {
        // get user
        $user = $request->user();
        // get product by user_id
        $cartItem = Cart::where('user_id', $user->id)->with('product')->get();
        if (Count($cartItem) > 1) {
            $total = $cartItem->sum(function ($item) {
                return $item->product->price * $item->quantity;
            });
            return response()->json([
                'status' => true,
                'message' => 'Cart Items retrieved successfully',
                'data' => [$cartItem, 'total_Price' => $total]
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not Found',
                'data' => ''
            ], 403);
        }
    }

    public function store(CartStoreRequest $request)
    {
        $cartItems = [];

        foreach ($request->products as $item) {
            $cartItem = Cart::where('user_id', $request->user()->id)
                ->where('product_id', $item['id'])
                ->first();

            if ($cartItem) {
                $cartItem->quantity += $item['quantity'];
                $cartItem->save();
            } else {
                $cartItem = Cart::create([
                    'user_id' => $request->user()->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            $cartItems[] = $cartItem;
        }

        return response()->json([
            'status' => true,
            'message' => 'Products added to cart successfully',
            'data' => $cartItems,
        ], 201);
    }

    public function show(Cart $cart)
    {
        //
    }

    public function update(Request $request, $id)
    {
        // validate request
        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = Cart::where('user_id', $request->user()->id)->findOrFail($id);

        // update quantity
        $cartItem->quantity = $data['quantity'];
        $cartItem->save();

        // return response
        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'cart_item' => $cartItem,
        ], 200);
    }

    public function destroy($id)
    {
        $cart = Cart::findOrFail($id);
        $cart->delete();
        // return response
        return response()->json([
            'success' => true,
            'message' => 'Cart item deleted successfully',
        ], 200);
    }

    public function clear(Request $request)
    {
        Cart::where('user_id', $request->user()->id)->delete();
        return response()->json([
            'success' => true,
            'message' => 'Cart items deleted  successfully',
        ], 200);
    }
}
