<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Menu;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('orders.index', [
            'menus' => Menu::where('tersedia', true)->orderBy('nama')->get(),
            'orders' => $request->user()->orders()->with('menu')->latest()->limit(20)->get(),
        ]);
    }

    /**
     * POST /api/orders (AS-01). CTRL-01: the official price is read from the database
     * and the total is recalculated here; the client-sent total_harga is ignored.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $menu = Menu::findOrFail($request->integer('menu_id'));

        $order = new Order;
        $order->user()->associate($request->user());
        $order->menu()->associate($menu);
        $order->jumlah = $request->integer('jumlah');
        $order->harga_satuan = $menu->harga;
        $order->total_harga = $menu->harga * $order->jumlah;
        $order->status = Order::MenungguPembayaran;
        $order->payment_reference = 'KF-'.Str::ulid();
        $order->save();

        return response()->json([
            'id' => $order->id,
            'menu' => $menu->nama,
            'jumlah' => $order->jumlah,
            'harga_satuan' => $order->harga_satuan,
            'total_harga' => $order->total_harga,
            'status' => $order->status,
            'payment_reference' => $order->payment_reference,
            'url' => route('orders.show', $order),
        ], 201);
    }

    public function show(Order $order): View
    {
        Gate::authorize('view-order', $order);

        return view('orders.show', ['order' => $order->load('menu', 'user')]);
    }
}
