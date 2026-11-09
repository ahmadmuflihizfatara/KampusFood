<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PaymentCallbackTest extends TestCase
{
    use RefreshDatabase;

    private const string Secret = 'test-callback-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.payment.callback_secret' => self::Secret, 'services.payment.simulator' => true]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sendCallback(array $payload, ?string $signature = null): TestResponse
    {
        $body = json_encode($payload);

        return $this->call('POST', '/payments/callback', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_SIGNATURE' => $signature ?? hash_hmac('sha256', $body, self::Secret),
        ], $body);
    }

    /**
     * @return array{reference: string, amount: int, status: string}
     */
    private function payloadFor(Order $order): array
    {
        return ['reference' => $order->payment_reference, 'amount' => $order->total_harga, 'status' => 'paid'];
    }

    public function test_valid_callback_marks_order_paid(): void
    {
        $order = Order::factory()->create();

        $this->sendCallback($this->payloadFor($order))->assertOk();

        $this->assertSame(Order::Dibayar, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $order = Order::factory()->create();

        $this->sendCallback($this->payloadFor($order), 'forged-signature')->assertUnauthorized();

        $this->assertSame(Order::MenungguPembayaran, $order->fresh()->status);
    }

    public function test_tampered_amount_breaks_the_signature(): void
    {
        $order = Order::factory()->create();
        $original = json_encode($this->payloadFor($order));

        $this->sendCallback([...$this->payloadFor($order), 'amount' => 1], hash_hmac('sha256', $original, self::Secret))
            ->assertUnauthorized();

        $this->assertSame(Order::MenungguPembayaran, $order->fresh()->status);
    }

    public function test_signed_callback_with_wrong_amount_is_rejected(): void
    {
        $order = Order::factory()->create();

        $this->sendCallback([...$this->payloadFor($order), 'amount' => 1])->assertUnprocessable();

        $this->assertSame(Order::MenungguPembayaran, $order->fresh()->status);
    }

    public function test_unknown_transaction_is_rejected(): void
    {
        $this->sendCallback(['reference' => 'KF-TIDAK-ADA', 'amount' => 1000, 'status' => 'paid'])->assertNotFound();
    }

    public function test_replayed_callback_does_not_change_a_paid_order(): void
    {
        $order = Order::factory()->dibayar()->create(['paid_at' => now()->subHour()]);
        $paidAt = $order->paid_at;

        $this->sendCallback($this->payloadFor($order))->assertOk();

        $this->assertEquals($paidAt, $order->fresh()->paid_at);
    }

    public function test_callback_is_refused_when_secret_is_not_configured(): void
    {
        config(['services.payment.callback_secret' => '']);
        $order = Order::factory()->create();

        $this->sendCallback($this->payloadFor($order), hash_hmac('sha256', json_encode($this->payloadFor($order)), ''))
            ->assertServiceUnavailable();
    }

    public function test_simulator_is_hidden_when_disabled(): void
    {
        config(['services.payment.simulator' => false]);
        $order = Order::factory()->create();

        $this->actingAs($order->user)->get(route('payments.simulator', $order->payment_reference))->assertNotFound();
    }
}
