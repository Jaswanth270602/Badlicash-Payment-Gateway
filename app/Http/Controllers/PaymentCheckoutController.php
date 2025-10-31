<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentLink;
use App\Models\Order;
use Illuminate\Support\Str;

class PaymentCheckoutController extends Controller
{
    public function show(string $token)
    {
        $paymentLink = PaymentLink::where('link_token', $token)->firstOrFail();
        return view('checkout.payment', compact('paymentLink'));
    }

    public function process(Request $request, string $token)
    {
        $paymentLink = PaymentLink::where('link_token', $token)->firstOrFail();
        $request->validate([
            'payment_method' => 'required|in:card,upi,netbanking',
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
        ]);
        // Minimal simulation: mark link complete and redirect success
        $order = Order::create([
            'merchant_id' => $paymentLink->merchant_id,
            'order_id' => 'ORD_' . strtoupper(Str::random(12)),
            'amount' => $paymentLink->amount,
            'currency' => $paymentLink->currency,
            'customer_details' => json_encode([
                'name' => $request->customer_name,
                'email' => $request->customer_email,
                'phone' => $request->customer_phone,
            ]),
            'status' => 'paid',
        ]);
        $paymentLink->status = 'completed';
        $paymentLink->save();
        return redirect()->route('payment.success', $token);
    }

    public function success(string $token)
    {
        $paymentLink = PaymentLink::where('link_token', $token)->firstOrFail();
        return view('checkout.success', compact('paymentLink'));
    }

    public function failed(string $token)
    {
        $paymentLink = PaymentLink::where('link_token', $token)->firstOrFail();
        return view('checkout.failed', compact('paymentLink'));
    }
}

 