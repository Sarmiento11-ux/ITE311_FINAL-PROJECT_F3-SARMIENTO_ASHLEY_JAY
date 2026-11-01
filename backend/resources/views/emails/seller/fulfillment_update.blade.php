@extends('emails.layouts.master')

@section('title', 'Fulfillment Update: ' . $item->product_name)

@section('content')
  <div class="greeting">Fulfillment Update 📋</div>
  
  <p class="lead-text">
    The fulfillment status for item <strong>{{ $item->product_name }}</strong> under order <strong>{{ $order->order_number }}</strong> has been updated.
  </p>

  <div class="order-box">
    <div class="order-box-title">Item Fulfillment Details</div>
    <table style="width: 100%; font-size: 13px;">
      <tr>
        <td style="color: #7A6F68; width: 140px; padding: 5px 0;">Product:</td>
        <td style="font-weight: 600;">{{ $item->product_name }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Order Number:</td>
        <td>{{ $order->order_number }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Quantity:</td>
        <td>{{ $item->quantity }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Current Status:</td>
        <td>
          <span class="pill-badge pill-info">{{ ucfirst($item->status) }}</span>
        </td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Updated At:</td>
        <td>{{ now()->format('M d, Y h:i A') }}</td>
      </tr>
    </table>
  </div>

  <div style="text-align: center; margin-top: 24px;">
    <a href="{{ config('app.url', 'http://localhost:5173') }}/dashboard/seller" class="btn btn-forest">
      Open Artisan Orders Table &rarr;
    </a>
  </div>
@endsection
