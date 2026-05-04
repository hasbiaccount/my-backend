<?php

namespace App\Http\Controllers;

use App\Models\CartAcara;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartAcaraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $carts = $request->user()->cartAcaras()->with('event')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar cart acara',
            'data' => $carts
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        $cart = $user->cartAcaras()->where('event_id', $request->event_id)->first();

        if ($cart) {
            $cart->quantity += $request->quantity;
            $cart->save();
        } else {
            $cart = $user->cartAcaras()->create([
                'event_id' => $request->event_id,
                'quantity' => $request->quantity
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menambahkan acara ke cart',
            'data' => $cart
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $cart = $request->user()->cartAcaras()->find($id);

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart not found'
            ], 404);
        }

        $cart->update([
            'quantity' => $request->quantity
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quantity berhasil diupdate',
            'data' => $cart
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $cart = $request->user()->cartAcaras()->find($id);

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart not found'
            ], 404);
        }

        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menghapus acara dari cart'
        ]);
    }
}
