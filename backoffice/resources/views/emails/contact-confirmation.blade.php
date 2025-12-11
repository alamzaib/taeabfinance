@extends('emails.layout')

@section('title', 'Thank You for Contacting Us')

@php
    $to_email = $notification->to_email ?? '';
@endphp

@section('content')
    <h2 style="color: #14b8a6; margin-top: 0;">Thank You for Contacting TAEAB Support!</h2>
    
    <p>Dear {{ $name }},</p>
    
    <p>We have successfully received your support request regarding:</p>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #14b8a6;">
        <p style="margin: 0; font-weight: 500;">{{ $subject }}</p>
    </div>
    
    <p>Our support team will review your request and get back to you within 24-48 hours. We appreciate your patience.</p>
    
    <p>If you have any urgent concerns, please feel free to contact us directly at <a href="mailto:support@taeab.com" style="color: #14b8a6;">support@taeab.com</a>.</p>
    
    <div class="divider"></div>
    
    <p style="margin-top: 30px;">Best regards,<br>
    <strong>The TAEAB Support Team</strong></p>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ config('app.url') }}" class="button">Visit Our Website</a>
    </div>
@endsection

