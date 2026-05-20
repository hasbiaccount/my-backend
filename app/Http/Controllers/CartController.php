<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $carts = $request->user()
            ->carts()
            ->with('event:id,title,start_date,end_date,location,registration_fee')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Cart retrieved successfully',
            'data' => $carts,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $cart = DB::transaction(function () use ($request) {
            $cart = $request->user()->carts()->lockForUpdate()
                ->firstOrNew(['event_id' => $request->event_id]);
            $cart->quantity = ($cart->exists ? $cart->quantity : 0) + (int) $request->quantity;
            $cart->save();
            return $cart;
        });

        return response()->json([
            'success' => true,
            'message' => 'Event added to cart',
            'data' => $cart->load('event:id,title,start_date,end_date,location,registration_fee'),
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $cart = $request->user()->carts()->find($id);

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
            ], 404);
        }

        $cart->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully',
            'data' => $cart,
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $cart = $request->user()->carts()->find($id);

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found',
            ], 404);
        }

        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart item removed successfully',
        ]);
    }
}
