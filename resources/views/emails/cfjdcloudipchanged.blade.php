@extends('emails.layouts.master')

@section('introduction')
Hi,<br><br>
This is the message from {{ config('app.name') }}.
@endsection

@section('content')
MSTools detects the recent changes of Cloudflare IPs, as follows:<br>
- <strong>Added IPs</strong>: {{ $addedIPs }}<br>
- <strong>Removed IPs</strong>: {{ $removedIPs }}<br>  
Kindly take the appropriate update in case of necessity.  
@endsection

@section('signature')
Thanks,<br>
{{ config('app.name') }}
@endsection
