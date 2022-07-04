@extends('cms.layouts.master')
@section('title', 'Dashboard')

@section('content')
<form id="export-pingdom-check" 
    action="{{ route('exportpingdomchecks') }}"
    method="GET"></form>
<button id="btn-export" onclick="event.preventDefault(); $('#export-pingdom-check').submit(); $('#btn-export').prop('disabled', true)"
    type="button"
    class="btn btn-primary">Export Pingdom Checks</button>
<hr>
<div class="card card-info">
	<div class="card-header">
		<h3 class="card-title">Check A & CNAME DNS records for domains</h3>
	</div>
	<form id="check-dns-form" role="form" action="{{ route('checkdns') }}" method="post">
		@csrf
		<div class="card-body">
			<div class="form-group">
				<label>Domain List <span style="color:red">&midast;</span></label>
				@error('domains')
				    <div class="alert alert-danger">{{ $message }}</div>
				@enderror
				<textarea class="form-control"
				          name="domains"
				          required
				          rows="5" 
				          placeholder="Paste the comma-seperated domains here">{{ old('domains') }}</textarea>
			</div>
		</div>
		<div class="card-footer">
			<button id="submitBtnForCheckDNS" type="submit" class="btn btn-success">Proceed</button>
		</div>
	</form>
</div>
@endsection

@push('scriptBottom')
<script type="text/javascript">
	$(document).ready(function () {
		$("#check-dns-form").submit(function () {
			$("#submitBtnForCheckDNS").attr('disabled', true);
		});
	});
</script>
@endpush