@extends('emails.layouts.master')

@section('introduction')
Hi,<br><br>
This is the message from {{ config('app.name') }}. 
@endsection

@section('content')
You have recently requested me for checking custom TLS/SSL configuration of {{ count($zones) }} Cloudflare zones.

Kindly find the attachment for the result.
@endsection

@section('link')
<a href="{{ config('app.url') }}" style="color: #ffffff; text-decoration: none;">VISIT ME?</a>
@endsection

@section('signature')
Thanks,<br>
{{ config('app.name') }}<br>
@endsection
