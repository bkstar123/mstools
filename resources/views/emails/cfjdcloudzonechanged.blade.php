@extends('emails.layouts.master')

@section('introduction')
Hi,<br><br>
This is the message from {{ config('app.name') }}.
@endsection

@section('content')
MSTools detects the recent changes in the list of China network zones on the Cloudflare as follows:<br>
- <strong>Added zones</strong>: {{ $addedZones }}<br>
- <strong>Removed zones</strong>: {{ $removedZones }}<br>  
Kindly take the appropriate update in case of necessity.  
@endsection

@section('signature')
Thanks,<br>
{{ config('app.name') }}
@endsection