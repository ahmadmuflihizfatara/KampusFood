<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Stand-in for the external payment gateway. Disabled unless PAYMENT_SIMULATOR=true,
     * because it lets anyone "pay" without paying.
     */
    public function simulator(Order $order): View
    {
        abort_unless(config('services.payment.simulator'), 404);
        Gate::authorize('view-order', $order);

        $payload = json_encode([
            'reference' => $order->payment_reference,
            'amount' => $order->total_harga,
            'status' => 'paid',
        ]);

        return view('payments.simulator', [
            'order' => $order->load('menu'),
            'payload' => $payload,
            'signature' => hash_hmac('sha256', $payload, $this->secret()),
        ]);
    }

    /**
     * POST /payments/callback (TB-2). Verifies, in order: HMAC signature over the raw body,
     * that the transaction exists, and that the amount matches the stored bill.
     * Replays are harmless because only an unpaid order can change to paid.
     */
    public function callback(Request $request): JsonResponse
    {
        $expected = hash_hmac('sha256', $request->getContent(), $this->secret());

        if (! hash_equals($expected, (string) $request->header('X-Signature'))) {
            return response()->json(['message' => 'Signature tidak valid.'], 401);
        }

        $data = $request->validate([
            'reference' => ['required', 'string', 'max:64'],
            'amount' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:paid'],
        ]);

        $order = Order::where('payment_reference', $data['reference'])->first();

        if (! $order) {
            return response()->json(['message' => 'Transaksi tidak dikenal.'], 404);
        }

        if ($order->total_harga !== (int) $data['amount']) {
            return response()->json(['message' => 'Nominal tidak sesuai tagihan.'], 422);
        }

        $updated = Order::whereKey($order->id)
            ->where('status', Order::MenungguPembayaran)
            ->update(['status' => Order::Dibayar, 'paid_at' => now()]);

        return response()->json([
            'message' => $updated ? 'Pembayaran diterima.' : 'Transaksi sudah diproses sebelumnya.',
        ]);
    }

    private function secret(): string
    {
        $secret = (string) config('services.payment.callback_secret');

        // An empty HMAC key would make every signature forgeable.
        abort_if($secret === '', 503, 'PAYMENT_CALLBACK_SECRET belum diatur.');

        return $secret;
    }
}
