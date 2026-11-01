@extends('emails.layouts.master')

@section('title', 'Payment Receipt: ' . $order->order_number)

@section('content')
  <div class="greeting">Bayad Na, {{ $order->user->name }}! 💳</div>
  
  <p class="lead-text">
    We have successfully verified your payment for order <strong style="color: #2F4533;">{{ $order->order_number }}</strong>. Your transaction is protected under CraftMarket Escrow.
  </p>

  <div class="order-box" style="border-left: 4px solid #2E7D32;">
    <div class="order-box-title">Official Payment Receipt</div>
    <table style="width: 100%; font-size: 13px;">
      <tr>
        <td style="color: #7A6F68; width: 150px; padding: 6px 0;">Order Number:</td>
        <td style="font-weight: 600;">{{ $order->order_number }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 6px 0;">Amount Paid:</td>
        <td style="font-size: 16px; font-weight: 700; color: #2F4533;">&#8369;{{ number_format($amount, 2) }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 6px 0;">Payment Method:</td>
        <td style="font-weight: 600;">{{ ucfirst($paymentMethod) }}</td>
      </tr>
      @if($reference)
      <tr>
        <td style="color: #7A6F68; padding: 6px 0;">Transaction Reference:</td>
        <td style="font-family: monospace; font-size: 12px; color: #4A3E39;">{{ $reference }}</td>
      </tr>
      @endif
      <tr>
        <td style="color: #7A6F68; padding: 6px 0;">Payment Date &amp; Time:</td>
        <td>{{ now()->format('M d, Y h:i:s A') }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 6px 0;">Escrow Protection:</td>
        <td><span class="pill-badge pill-success">Protected &middot; Verified</span></td>
      </tr>
    </table>
  </div>

  <p style="font-size: 13px; color: #4A3E39;">
    Your payment is securely held until you confirm delivery of your handcrafted items. The artisan workshops have been instructed to fulfill your order immediately.
  </p>

  <div style="text-align: center; margin-top: 24px;">
    <a href="{{ config('app.url', 'http://localhost:5173') }}/orders" class="btn btn-forest">
      Track Order in Dashboard &rarr;
    </a>
  </div>
@endsection
