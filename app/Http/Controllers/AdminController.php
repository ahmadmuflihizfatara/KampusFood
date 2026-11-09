<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveMenuRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Every route here sits behind the "can:admin" middleware (AS-01: POST /admin/menu/{id}).
 */
class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'menus' => Menu::orderBy('id')->get(),
            'orders' => Order::with('menu', 'user')->latest()->limit(50)->get(),
            'users' => User::orderBy('name')->get(['id', 'name', 'username', 'email', 'role']),
        ]);
    }

    /**
     * `role` is not fillable, so it is assigned explicitly after validation against an allow-list.
     */
    public function storeUser(StoreUserRequest $request): RedirectResponse
    {
        $user = new User($request->safe()->only(['name', 'username', 'email', 'password']));
        $user->role = $request->validated('role');
        $user->save();

        return back()->with('status', "Akun {$user->username} dibuat.");
    }

    public function storeMenu(SaveMenuRequest $request): RedirectResponse
    {
        Menu::create($request->validated());

        return back()->with('status', 'Menu ditambahkan.');
    }

    public function updateMenu(SaveMenuRequest $request, Menu $menu): RedirectResponse
    {
        $menu->update($request->validated());

        return back()->with('status', "Menu {$menu->nama} diperbarui.");
    }

    public function completeOrder(Order $order): RedirectResponse
    {
        abort_unless($order->status === Order::Dibayar, 422, 'Hanya pesanan yang sudah dibayar yang bisa diselesaikan.');

        $order->status = Order::Selesai;
        $order->save();

        return back()->with('status', "Pesanan #{$order->id} selesai.");
    }
}
