@extends('emails.layout')

@section('title', 'New Contact Support Request')

@php
    $to_email = 'support@taeab.com';
@endphp

@section('content')
    <h2 style="color: #14b8a6; margin-top: 0;">New Support Request</h2>
    
    <p>You have received a new support request from the contact form:</p>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
        <p style="margin: 5px 0;"><strong>Name:</strong> {{ $name }}</p>
        <p style="margin: 5px 0;"><strong>Email:</strong> {{ $email }}</p>
        @if($phone)
        <p style="margin: 5px 0;"><strong>Phone:</strong> {{ $phone }}</p>
        @endif
        <p style="margin: 5px 0;"><strong>Subject:</strong> {{ $subject }}</p>
    </div>
    
    <div style="margin: 20px 0;">
        <h3 style="color: #333; font-size: 16px; margin-bottom: 10px;">Message:</h3>
        <div style="background-color: #ffffff; padding: 15px; border-left: 4px solid #14b8a6; border-radius: 3px;">
            {!! nl2br(e($message)) !!}
        </div>
    </div>
    
    <div class="divider"></div>
    
    <p style="color: #6c757d; font-size: 14px;">
        <strong>Please respond to this request as soon as possible.</strong>
    </p>
@endsection

