@extends('emails.layouts.master')

@section('title', 'Welcome to CraftMarket Philippines!')

@section('content')
  <div class="greeting">Mabuhay, {{ $user->name }}! 🎉</div>
  
  <p class="lead-text">
    Welcome to <strong>CraftMarket Philippines</strong> — the home of genuine handmade treasures and passionate Filipino artisans.
  </p>

  <div class="order-box">
    <div class="order-box-title">Account Details</div>
    <table style="width: 100%; font-size: 14px;">
      <tr>
        <td style="padding: 6px 0; color: #7A6F68; width: 140px;">Registered Name:</td>
        <td style="padding: 6px 0; font-weight: 600;">{{ $user->name }}</td>
      </tr>
      <tr>
        <td style="padding: 6px 0; color: #7A6F68;">Email Address:</td>
        <td style="padding: 6px 0; font-weight: 600;">{{ $user->email }}</td>
      </tr>
      <tr>
        <td style="padding: 6px 0; color: #7A6F68;">Account Role:</td>
        <td style="padding: 6px 0;">
          <span class="pill-badge pill-success">{{ ucfirst($user->role) }}</span>
        </td>
      </tr>
      @if($user->role === 'seller' && $user->sellerProfile)
      <tr>
        <td style="padding: 6px 0; color: #7A6F68;">Storefront Name:</td>
        <td style="padding: 6px 0; font-weight: 600; color: #2F4533;">{{ $user->sellerProfile->shop_name }}</td>
      </tr>
      @endif
    </table>
  </div>

  @if($user->role === 'seller')
    <p>
      Your artisan workshop is now ready! You can list handcrafted goods, monitor buyer orders, and manage inventory right from your artisan dashboard.
    </p>
    <div style="text-align: center;">
      <a href="{{ config('app.url', 'http://localhost:5173') }}/dashboard/seller" class="btn btn-forest">
        Go to Artisan Dashboard &rarr;
      </a>
    </div>
  @else
    <p>
      Discover unique woven textiles, handcrafted ceramics, eco-friendly wooden home decor, and authentic local jewelry crafted with soul and heritage.
    </p>
    <div style="text-align: center;">
      <a href="{{ config('app.url', 'http://localhost:5173') }}/products" class="btn">
        Explore Handcrafted Collections &rarr;
      </a>
    </div>
  @endif

  <p style="font-size: 13px; color: #7A6F68; margin-top: 24px;">
    Tip: For your security, CraftMarket will never ask for your password via email or message.
  </p>
@endsection
