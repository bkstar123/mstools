@extends('emails.layouts.master')

@section('introduction')
Hi,<br><br>
This is the message from {{ config('app.name') }}.
@endsection

@section('content')
MSTools detects recent IP change of Cloudflare on JDCloud network, the new IPs are as follows:
{{ $changedIPs }}. Kindly take the appropriate update in case of necessity.
@endsection

@section('signature')
Thanks,<br>
{{ config('app.name') }}
@endsection
