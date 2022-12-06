@extends('emails.layouts.master')

@section('introduction')
Hi,<br><br>
This is the message from {{ config('app.name') }}.
@endsection

@section('content')
MSTools detects the recent changes of Cloudflare IPs, the new IPs are as follows:
{{ $changedIPs }}. Kindly take the appropriate update in case of necessity.
@endsection

@section('signature')
Thanks,<br>
{{ config('app.name') }}
@endsection
