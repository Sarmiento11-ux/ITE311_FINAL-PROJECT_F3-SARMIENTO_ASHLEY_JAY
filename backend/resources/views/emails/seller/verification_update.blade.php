@extends('emails.layouts.master')

@php
  $shopName = $seller->sellerProfile->shop_name ?? $seller->name;
  $badge = match ($status) {
      'approved' => ['pill-success', 'Approved'],
      'rejected' => ['pill-danger', 'Needs Attention'],
      default    => ['pill-warning', 'Under Review'],
  };
@endphp

@section('title', 'Storefront Verification Update')

@section('content')
  <div class="greeting">Hello, {{ $seller->name }} 👋</div>

  <p class="lead-text">
    The CraftMarket moderation team has reviewed your artisan storefront
    <strong>{{ $shopName }}</strong>.
  </p>

  <p>
    <span class="pill-badge {{ $badge[0] }}">{{ $badge[1] }}</span>
  </p>

  @if($status === 'approved')
    <p class="lead-text">
      Congratulations! Your workshop is now verified and visible to customers across the
      CraftMarket marketplace. You can start publishing listings and receiving orders.
    </p>
  @elseif($status === 'rejected')
    <p class="lead-text">
      We could not approve your storefront application in its current form. Please review the
      moderator notes below, update your workshop details, and submit a new verification request.
    </p>
  @else
    <p class="lead-text">
      Your storefront has been returned to the review queue. No action is required right now;
      our team will notify you once the review is complete.
    </p>
  @endif

  @if(!empty($notes))
    <div class="order-box">
      <div class="order-box-title">Moderator Notes</div>
      <p style="margin: 0; color: #4A3E39; line-height: 1.6;">{{ $notes }}</p>
    </div>
  @endif

  <div class="order-box">
    <div class="order-box-title">Storefront Summary</div>
    <table style="width: 100%; font-size: 13px;">
      <tr>
        <td style="color: #7A6F68; width: 160px; padding: 5px 0;">Workshop Name:</td>
        <td style="font-weight: 600; color: #2F4533;">{{ $shopName }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Verification Status:</td>
        <td style="font-weight: 600;">{{ ucfirst($status) }}</td>
      </tr>
      <tr>
        <td style="color: #7A6F68; padding: 5px 0;">Reviewed At:</td>
        <td>{{ now()->format('M d, Y h:i A') }}</td>
      </tr>
    </table>
  </div>

  <div style="text-align: center; margin-top: 24px;">
    <a href="{{ config('app.url', 'http://localhost:5173') }}/dashboard/seller" class="btn btn-forest">
      Open Artisan Dashboard &rarr;
    </a>
  </div>
@endsection
