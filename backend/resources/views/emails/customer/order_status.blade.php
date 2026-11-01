@extends('emails.layouts.master')

@section('title', 'Order Update: ' . $order->order_number . ' is now ' . ucfirst($newStatus))

@section('content')
  <div class="greeting">Hello, {{ $order->user->name }}! 🚚</div>
  
  <p class="lead-text">
    Your order <strong style="color: #2F4533;">{{ $order->order_number }}</strong> has a new status update.
  </p>

  <div style="text-align: center; margin: 24px 0;">
    <div style="font-size: 13px; color: #7A6F68; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Current Status</div>
    <div>
      @if($newStatus === 'confirmed')
        <span class="pill-badge pill-info" style="font-size: 15px; padding: 8px 18px;">Confirmed &middot; In Queue</span>
      @elseif($newStatus === 'processing')
        <span class="pill-badge pill-warning" style="font-size: 15px; padding: 8px 18px;">Processing &middot; Being Crafted</span>
      @elseif($newStatus === 'shipped')
        <span class="pill-badge pill-info" style="font-size: 15px; padding: 8px 18px;">Shipped &middot; On the Way</span>
      @elseif($newStatus === 'delivered')
        <span class="pill-badge pill-success" style="font-size: 15px; padding: 8px 18px;">Delivered &middot; Completed</span>
      @elseif($newStatus === 'cancelled')
        <span class="pill-badge pill-danger" style="font-size: 15px; padding: 8px 18px;">Cancelled</span>
      @else
        <span class="pill-badge pill-info" style="font-size: 15px; padding: 8px 18px;">{{ ucfirst($newStatus) }}</span>
      @endif
    </div>
  </div>

  <div class="order-box">
    <div class="order-box-title">Order Overview</div>
    <table style="width: 100%; font-size: 13px;">
      <tr>
        <td style="color: #7A6F68; width: 140px; padding: 5px 0;">Order Reference:</td>
        <td style="font-weight: 600;">{{ $order->order_number }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Total Amount:</td>
        <td style="font-weight: 600;">&#8369;{{ number_format($order->total_amount, 2) }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Delivery Address:</td>
        <td>{{ $order->shipping_address }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Status Updated At:</td>
        <td>{{ now()->format('M d, Y h:i A') }}</td>
      </tr>
    </table>
  </div>

  @if($newStatus === 'shipped')
    <p style="background: #F0F4FF; border-left: 4px solid #4A6FA5; padding: 12px 16px; border-radius: 4px; font-size: 13px; color: #1E3A8A;">
      📦 <strong>Your package is on its way!</strong> Our courier partners are transporting your handcrafted items safely. Please prepare the exact cash amount if paying via Cash on Delivery.
    </p>
  @elseif($newStatus === 'delivered')
    <p style="background: #F0FAF3; border-left: 4px solid #3A7A56; padding: 12px 16px; border-radius: 4px; font-size: 13px; color: #1E4620;">
      ✨ <strong>Package received!</strong> Thank you for supporting authentic Philippine craftsmanship and empowering local makers.
    </p>
  @elseif($newStatus === 'cancelled')
    <p style="background: #FFF5F5; border-left: 4px solid #E53E3E; padding: 12px 16px; border-radius: 4px; font-size: 13px; color: #9B2C2C;">
      This order has been cancelled. If payment was already made, our escrow support team will process your refund according to CraftMarket policy.
    </p>
  @endif

  <div style="text-align: center; margin-top: 24px;">
    <a href="{{ config('app.url', 'http://localhost:5173') }}/orders" class="btn btn-forest">
      View Order Details &rarr;
    </a>
  </div>
@endsection
