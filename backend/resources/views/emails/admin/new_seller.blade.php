@extends('emails.layouts.master')

@section('title', 'Admin Alert: New Artisan Registration')

@section('content')
  <div class="greeting">Administrator Alert: New Artisan Workshop Registered 🏪</div>
  
  <p class="lead-text">
    A new maker has joined CraftMarket. Here are the registration details for administrative review.
  </p>

  <div class="order-box">
    <div class="order-box-title">Artisan Profile</div>
    <table style="width: 100%; font-size: 13px;">
      <tr>
        <td style="color: #7A6F68; width: 140px; padding: 5px 0;">Artisan Name:</td>
        <td style="font-weight: 600;">{{ $user->name }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Email:</td>
        <td style="font-weight: 600;">{{ $user->email }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Storefront Name:</td>
        <td style="font-weight: 600; color: #2F4533;">{{ $user->sellerProfile->shop_name ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Description:</td>
        <td style="line-height: 1.4;">{{ $user->sellerProfile->shop_description ?? 'No description provided' }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Contact Number:</td>
        <td>{{ $user->sellerProfile->contact_number ?? $user->phone ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Workshop Address:</td>
        <td>{{ $user->sellerProfile->address ?? $user->address ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Registered At:</td>
        <td>{{ $user->created_at->format('M d, Y h:i A') }}</td>
      </tr>
    </table>
  </div>

  <div style="text-align: center; margin-top: 24px;">
    <a href="{{ config('app.url', 'http://localhost:5173') }}/dashboard/admin" class="btn btn-forest">
      Review in Admin Control Center &rarr;
    </a>
  </div>
@endsection
