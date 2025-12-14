@extends('emails.layout')

@section('title', 'Welcome to TAEAB')

@php
    $to_email = $notification->to_email ?? $user->email ?? '';
    $logoUrl = config('app.frontend_url', config('app.url')) . '/images/logo.png';
@endphp

@section('content')
    <div style="text-align: center; margin-bottom: 30px;">
        <img src="{{ $logoUrl }}" alt="TAEAB Logo" style="max-width: 200px; height: auto; margin-bottom: 20px;" onerror="this.style.display='none';">
    </div>

    <h2 style="color: #14b8a6; margin-top: 0;">Welcome to TAEAB, {{ $user->name }}!</h2>
    
    <p>Thank you for joining TAEAB. We're excited to have you on board!</p>
    
    <p>Your account has been successfully created. You can now:</p>
    
    <ul style="line-height: 2;">
        <li>Explore our investment packages and start growing your wealth</li>
        <li>Track your earnings and portfolio performance</li>
        <li>Manage your billing and payment methods</li>
        <li>Access your affiliate program and earn commissions</li>
    </ul>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #14b8a6;">
        <p style="margin: 0; font-weight: 500;">Account Details:</p>
        <p style="margin: 5px 0 0 0;">Email: <strong>{{ $user->email }}</strong></p>
        <p style="margin: 5px 0 0 0;">Registered: <strong>{{ $user->created_at->format('F j, Y') }}</strong></p>
    </div>
    
    <p>To get started, simply log in to your dashboard and explore the opportunities available to you.</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.frontend_url', config('app.url')) }}/dashboard" class="button">Go to Dashboard</a>
    </div>
    
    <div class="divider"></div>
    
    <p style="margin-top: 30px;">If you have any questions or need assistance, our support team is here to help. Feel free to contact us at <a href="mailto:support@taeab.com" style="color: #14b8a6;">support@taeab.com</a>.</p>
    
    <p style="margin-top: 30px;">Best regards,<br>
    <strong>The TAEAB Team</strong></p>
@endsection

