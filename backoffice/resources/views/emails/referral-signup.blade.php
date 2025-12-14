@extends('emails.layout')

@section('title', 'New Referral Signup - TAEAB')

@php
    $to_email = $notification->to_email ?? $referrer->email ?? '';
    $logoUrl = config('app.frontend_url', config('app.url')) . '/images/logo.png';
@endphp

@section('content')
    <div style="text-align: center; margin-bottom: 30px;">
        <img src="{{ $logoUrl }}" alt="TAEAB Logo" style="max-width: 200px; height: auto; margin-bottom: 20px;" onerror="this.style.display='none';">
    </div>

    <h2 style="color: #14b8a6; margin-top: 0;">🎉 Congratulations, {{ $referrer->name }}!</h2>
    
    <p>Great news! Someone just signed up using your affiliate link!</p>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #14b8a6;">
        <p style="margin: 0; font-weight: 500;">New Referral Details:</p>
        @if($newUser)
            <p style="margin: 5px 0 0 0;">Name: <strong>{{ $newUser->name ?? 'N/A' }}</strong></p>
            <p style="margin: 5px 0 0 0;">Email: <strong>{{ $newUser->email ?? 'N/A' }}</strong></p>
            <p style="margin: 5px 0 0 0;">Signed up: <strong>{{ isset($newUser->created_at) ? \Carbon\Carbon::parse($newUser->created_at)->format('F j, Y') : 'Today' }}</strong></p>
        @else
            <p style="margin: 5px 0 0 0;">A new user has signed up using your affiliate link!</p>
        @endif
    </div>
    
    <p>This is a great opportunity to grow your affiliate earnings! Keep sharing your affiliate link to earn more commissions.</p>
    
    <div style="background-color: #e8f5e9; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p style="margin: 0; color: #2e7d32; font-weight: 500;">💡 Tip: Share your affiliate link on social media, with friends, or in your network to maximize your earnings!</p>
    </div>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.frontend_url', config('app.url')) }}/dashboard" class="button">View Your Affiliate Dashboard</a>
    </div>
    
    <div class="divider"></div>
    
    <p style="margin-top: 30px;">Thank you for being a valuable member of the TAEAB community!</p>
    
    <p style="margin-top: 30px;">Best regards,<br>
    <strong>The TAEAB Team</strong></p>
@endsection

