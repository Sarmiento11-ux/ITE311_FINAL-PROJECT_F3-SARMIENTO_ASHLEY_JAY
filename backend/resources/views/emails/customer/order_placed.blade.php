@extends('emails.layouts.master')

@section('title', 'Order Confirmed: ' . $order->order_number)

@section('content')
  <div class="greeting">Salamat sa pagtangkilik, {{ $order->user->name }}! 📦</div>
  
  <p class="lead-text">
    Your order <strong style="color: #2F4533;">{{ $order->order_number }}</strong> has been successfully placed. Local artisans have been notified to handcraft and prepare your items.
  </p>

  <div class="order-box">
    <div class="order-box-title">Order Summary &middot; {{ $order->order_number }}</div>
    
    <table style="width: 100%; font-size: 13px; margin-bottom: 12px;">
      <tr>
        <td style="color: #7A6F68; width: 130px; padding: 4px 0;">Order Date:</td>
        <td style="font-weight: 600;">{{ $order->created_at->format('M d, Y h:i A') }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 4px 0;">Payment Method:</td>
        <td style="font-weight: 600;">{{ $order->payment_method }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 4px 0;">Payment Status:</td>
        <td>
          @if($order->payment_status === 'paid')
            <span class="pill-badge pill-success">Paid</span>
          @else
            <span class="pill-badge pill-warning">Pending ({{ ucfirst($order->payment_status) }})</span>
          @endif
        </td>
      </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table">
      <thead>
        <tr>
          <th>Item / Artisan</th>
          <th class="text-center">Qty</th>
          <th class="text-right">Price</th>
          <th class="text-right">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->items as $item)
          <tr>
            <td>
              <strong>{{ $item->product_name }}</strong><br>
              <span style="font-size: 12px; color: #7A6F68;">
                Artisan: {{ $item->seller->sellerProfile->shop_name ?? $item->seller->name ?? 'Local Maker' }}
              </span>
            </td>
            <td class="text-center">{{ $item->quantity }}</td>
            <td class="text-right">&#8369;{{ number_format($item->unit_price ?? $item->price, 2) }}</td>
            <td class="text-right">&#8369;{{ number_format($item->subtotal, 2) }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr class="summary-row">
          <td colspan="3" class="text-right" style="color: #7A6F68; padding-top: 12px;">Subtotal:</td>
          <td class="text-right" style="padding-top: 12px;">&#8369;{{ number_format($order->subtotal, 2) }}</td>
        </tr>
        <tr class="summary-row">
          <td colspan="3" class="text-right" style="color: #7A6F68;">Shipping Fee:</td>
          <td class="text-right">
            @if($order->shipping_fee > 0)
              &#8369;{{ number_format($order->shipping_fee, 2) }}
            @else
              <span style="color: #2E7D32; font-weight: 600;">FREE</span>
            @endif
          </td>
        </tr>
        <tr class="summary-total">
          <td colspan="3" class="text-right">Total Amount:</td>
          <td class="text-right">&#8369;{{ number_format($order->total_amount, 2) }}</td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div class="order-box" style="margin-top: 16px;">
    <div class="order-box-title">Shipping &amp; Delivery Details</div>
    <table style="width: 100%; font-size: 13px;">
      <tr>
        <td style="color: #7A6F68; width: 130px; padding: 4px 0;">Recipient:</td>
        <td style="font-weight: 600;">{{ $order->user->name }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 4px 0;">Contact Number:</td>
        <td style="font-weight: 600;">{{ $order->contact_number }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 4px 0;">Delivery Address:</td>
        <td style="line-height: 1.4;">{{ $order->shipping_address }}</td>
      </tr>
      @if($order->notes)
      <tr>
        <td style="color: #7A6F68; padding: 4px 0;">Order Notes:</td>
        <td style="font-style: italic;">{{ $order->notes }}</td>
      </tr>
      @endif
    </table>
  </div>

  <div style="text-align: center; margin-top: 24px;">
    <a href="{{ config('app.url', 'http://localhost:5173') }}/orders" class="btn btn-forest">
      Track Your Order in Dashboard &rarr;
    </a>
  </div>
@endsection
