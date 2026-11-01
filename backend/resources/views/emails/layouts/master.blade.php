<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'CraftMarket Philippines')</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #F8F5EE;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      color: #2C221E;
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
    }
    table {
      border-collapse: collapse;
      mso-table-lspace: 0pt;
      mso-table-rspace: 0pt;
    }
    .wrapper {
      width: 100%;
      background-color: #F8F5EE;
      padding: 30px 15px;
    }
    .container {
      max-width: 600px;
      margin: 0 auto;
      background-color: #FFFFFF;
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
      border: 1px solid #EFE8DE;
    }
    .header {
      background-color: #2F4533;
      padding: 28px 30px;
      text-align: center;
    }
    .header h1 {
      margin: 0;
      font-size: 24px;
      font-weight: 700;
      letter-spacing: -0.5px;
      color: #FFFFFF;
    }
    .header h1 span {
      color: #E2A76F;
    }
    .header p {
      margin: 6px 0 0 0;
      font-size: 13px;
      color: #E8F0EA;
      letter-spacing: 0.3px;
    }
    .badge-bar {
      background-color: #243628;
      padding: 8px 20px;
      text-align: center;
      font-size: 11px;
      color: #D3E0D6;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .content {
      padding: 32px 30px;
    }
    .greeting {
      font-size: 18px;
      font-weight: 700;
      color: #2F4533;
      margin-bottom: 12px;
    }
    .lead-text {
      font-size: 15px;
      color: #4A3E39;
      margin-bottom: 24px;
    }
    .pill-badge {
      display: inline-block;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .pill-success {
      background-color: #E8F5E9;
      color: #2E7D32;
      border: 1px solid #C8E6C9;
    }
    .pill-info {
      background-color: #E3F2FD;
      color: #1565C0;
      border: 1px solid #BBDEFB;
    }
    .pill-warning {
      background-color: #FFF8E1;
      color: #F57F17;
      border: 1px solid #FFE082;
    }
    .pill-danger {
      background-color: #FFEBEE;
      color: #C62828;
      border: 1px solid #FFCDD2;
    }
    .order-box {
      background-color: #FCFBF9;
      border: 1px solid #EFE8DE;
      border-radius: 10px;
      padding: 20px;
      margin: 22px 0;
    }
    .order-box-title {
      font-size: 14px;
      font-weight: 700;
      color: #2F4533;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin: 0 0 14px 0;
      padding-bottom: 8px;
      border-bottom: 1px solid #EFE8DE;
    }
    .items-table {
      width: 100%;
      margin: 12px 0;
    }
    .items-table th {
      text-align: left;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #7A6F68;
      padding: 8px 4px;
      border-bottom: 1px solid #EFE8DE;
    }
    .items-table td {
      padding: 10px 4px;
      font-size: 14px;
      border-bottom: 1px solid #F5EFEB;
      color: #2C221E;
    }
    .items-table .text-right {
      text-align: right;
    }
    .items-table .text-center {
      text-align: center;
    }
    .summary-row td {
      border-bottom: none;
      padding: 5px 4px;
      font-size: 14px;
    }
    .summary-total td {
      border-top: 2px solid #2F4533;
      padding: 10px 4px 4px 4px;
      font-size: 16px;
      font-weight: 700;
      color: #2F4533;
    }
    .btn {
      display: inline-block;
      padding: 12px 28px;
      background-color: #C25E3E;
      color: #FFFFFF !important;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
      border-radius: 8px;
      text-align: center;
      margin: 20px 0;
    }
    .btn-forest {
      background-color: #2F4533;
    }
    .footer {
      background-color: #F4EFEB;
      padding: 24px 30px;
      text-align: center;
      border-top: 1px solid #EAE3D9;
      font-size: 12px;
      color: #7A6F68;
    }
    .footer p {
      margin: 4px 0;
    }
    .footer-links {
      margin-top: 10px;
    }
    .footer-links a {
      color: #2F4533;
      text-decoration: underline;
      margin: 0 8px;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="container">
      
      <!-- Brand Header -->
      <div class="header">
        <h1>Craft<span>Market</span>.</h1>
        <p>The Trusted Philippine Handmade Marketplace</p>
      </div>

      <div class="badge-bar">
        🇵🇭 Proudly Local &middot; Verified Artisans &middot; Escrow Protected
      </div>

      <!-- Main Body Content -->
      <div class="content">
        @yield('content')
      </div>

      <!-- Footer -->
      <div class="footer">
        <p><strong>CraftMarket Philippines</strong> &middot; Supporting Local Artisans Nationwide</p>
        <p>Need help? Reach out at <a href="mailto:support@craftmarket.ph" style="color: #2F4533; font-weight: 600;">support@craftmarket.ph</a></p>
        <div class="footer-links">
          <a href="{{ config('app.url', 'http://localhost:5173') }}">Home</a> &middot;
          <a href="{{ config('app.url', 'http://localhost:5173') }}/orders">My Orders</a> &middot;
          <a href="{{ config('app.url', 'http://localhost:5173') }}/products">Explore Crafts</a>
        </div>
        <p style="margin-top: 12px; font-size: 11px; color: #9C8A80;">
          &copy; {{ date('Y') }} CraftMarket Philippines. All rights reserved.
        </p>
      </div>

    </div>
  </div>
</body>
</html>
