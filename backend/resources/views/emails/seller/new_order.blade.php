@extends('emails.layouts.master')

@section('title', 'New Order Received: ' . $order->order_number)

@section('content')
  <div class="greeting">Congratulations, {{ $seller->name }}! 🎨</div>
  
  <p class="lead-text">
    A customer just ordered handcrafted items from your workshop (<strong>{{ $seller->sellerProfile->shop_name ?? $seller->name }}</strong>) on CraftMarket!
  </p>

  <div class="order-box">
    <div class="order-box-title">Order Information &middot; {{ $order->order_number }}</div>
    <table style="width: 100%; font-size: 13px; margin-bottom: 12px;">
      <tr>
        <td style="color: #7A6F68; width: 130px; padding: 4px 0;">Order Date:</td>
        <td style="font-weight: 600;">{{ $order->created_at->format('M d, Y h:i A') }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 4px 0;">Customer Name:</td>
        <td style="font-weight: 600;">{{ $order->user->name }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 4px 0;">Payment Method:</td>
        <td>{{ $order->payment_method }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 4px 0;">Payment Status:</td>
        <td>
          @if($order->payment_status === 'paid')
            <span class="pill-badge pill-success">Paid via Escrow</span>
          @else
            <span class="pill-badge pill-warning">Cash on Delivery</span>
          @endif
        </td>
      </tr>
    </table>

    <div style="font-weight: 700; font-size: 13px; color: #2F4533; margin: 16px 0 8px 0; text-transform: uppercase;">
      Your Workshop Items in this Order:
    </div>

    <!-- Seller-specific items only -->
    <table class="items-table">
      <thead>
        <tr>
          <th>Item Name</th>
          <th class="text-center">Qty</th>
          <th class="text-right">Unit Price</th>
          <th class="text-right">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        @php $sellerTotal = 0; @endphp
        @foreach($sellerItems as $item)
          @php $sellerTotal += $item->subtotal; @endphp
          <tr>
            <td><strong>{{ $item->product_name }}</strong></td>
            <td class="text-center">{{ $item->quantity }}</td>
            <td class="text-right">&#8369;{{ number_format($item->unit_price ?? $item->price, 2) }}</td>
            <td class="text-right">&#8369;{{ number_format($item->subtotal, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr class="summary-total">
          <td colspan="3" class="text-right">Artisan Payout Share:</td>
          <td class="text-right">&#8369;{{ number_format($sellerTotal, 2) }}</td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div class="order-box">
    <div class="order-box-title">Shipping &amp; Packaging Instructions</div>
    <p style="font-size: 13px; margin: 0 0 10px 0; color: #4A3E39;">
      <strong>Delivery Destination:</strong> {{ $order->shipping_address }}<br>
      <strong>Customer Contact:</strong> {{ $order->contact_number }}
    </p>
    <p style="font-size: 12px; color: #7A6F68; margin: 0; line-height: 1.5;">
      &bull; Please inspect your handcrafted creations and securely pack with eco-friendly padding.<br>
      &bull; Update the fulfillment status in your Artisan Dashboard to "Processing" while packing, then "Shipped" once dispatched to the courier.
    </p>
  </div>

  <div style="text-align: center; margin-top: 24px;">
    <a href="{{ config('app.url', 'http://localhost:5173') }}/dashboard/seller" class="btn btn-forest">
      Manage Order in Artisan Dashboard &rarr;
    </a>
  </div>
@endsection
