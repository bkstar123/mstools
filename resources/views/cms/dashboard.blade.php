@extends('cms.layouts.master')
@section('title', 'Dashboard')

@section('content')
<form id="export-pingdom-check" 
    action="{{ route('exportpingdomchecks') }}"
    method="GET"></form>
<button id="btn-export" onclick="event.preventDefault(); $('#export-pingdom-check').submit(); $('#btn-export').prop('disabled', true)"
    type="button"
    class="btn btn-primary">Export Pingdom Checks</button>
<div id="link-to-download-pingdom-report"></div>
@endsection