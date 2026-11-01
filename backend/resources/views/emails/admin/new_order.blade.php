@extends('emails.layouts.master')

@section('title', 'Admin Alert: New Marketplace Order ' . $order->order_number)

@section('content')
  <div class="greeting">Marketplace Order Notification: {{ $order->order_number }} 📈</div>
  
  <p class="lead-text">
    A new order has been created on the CraftMarket platform.
  </p>

  <div class="order-box">
    <div class="order-box-title">Order Overview</div>
    <table style="width: 100%; font-size: 13px;">
      <tr>
        <td style="color: #7A6F68; width: 140px; padding: 5px 0;">Order Reference:</td>
        <td style="font-weight: 600;">{{ $order->order_number }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Customer Name:</td>
        <td style="font-weight: 600;">{{ $order->user->name }} ({{ $order->user->email }})</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Total Amount:</td>
        <td style="font-size: 15px; font-weight: 700; color: #2F4533;">&#8369;{{ number_format($order->total_amount, 2) }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Payment Method:</td>
        <td>{{ $order->payment_method }} ({{ ucfirst($order->payment_status) }})</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Total Line Items:</td>
        <td>{{ $order->items->count() }} item(s) from {{ $order->items->pluck('seller_id')->unique()->count() }} artisan(s)</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Placed At:</td>
        <td>{{ $order->created_at->format('M d, Y h:i A') }}</td>
      </tr>
    </table>
  </div>

  <div style="text-align: center; margin-top: 24px;">
    <a href="{{ config('app.url', 'http://localhost:5173') }}/dashboard/admin" class="btn btn-forest">
      Manage in Admin Dashboard &rarr;
    </a>
  </div>
@endsection
