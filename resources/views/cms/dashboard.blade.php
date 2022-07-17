@extends('cms.layouts.master')
@section('title', 'Dashboard')

@section('content')
<form id="export-pingdom-check" 
      action="{{ route('pingdom.checks.export') }}"
      method="GET"></form>
<button id="btn-export" 
        onclick="event.preventDefault(); $('#export-pingdom-check').submit(); $('#btn-export').prop('disabled', true)"
        type="button"
        class="btn btn-primary">
    Export Pingdom Checks
</button>
<hr>
<div class="row">
	<div class="col col-md-6">
		<div class="card card-success">
			<div class="card-header">
				<h3 class="card-title">Get A & CNAME DNS records for domains</h3>
			</div>
			<form id="check-dns-form" role="form" action="{{ route('dns.query') }}" method="post">
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
	</div>
	<div class="col col-md-6">
		<div class="card card-success">
			<div class="card-header">
				<h3 class="card-title">Get details of Pingdom checks</h3>
			</div>
			<form id="get-details-pingdom-checks-form" role="form" action="{{ route('pingdom.checks') }}" method="post">
				@csrf
				<div class="card-body">
					<div class="form-group">
						<label>List of Pingdom check IDs <span style="color:red">&midast;</span></label>
						@error('checks')
				            <div class="alert alert-danger">{{ $message }}</div>
				        @enderror
				        <textarea class="form-control"
				                  name="checks"
				                  required
				                  rows="5" 
				                  placeholder="Paste the comma-seperated Pingdom check id here">{{ old('checks') }}</textarea>
				    </div>
				</div>
				<div class="card-footer">
					<button id="submitBtnForGetDetailsPingdomChecks" type="submit" class="btn btn-success">Proceed</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection

@push('scriptBottom')
<script type="text/javascript">
	$(document).ready(function () {
		$("#check-dns-form").submit(function () {
			$("#submitBtnForCheckDNS").attr('disabled', true);
		});
		$("#get-details-pingdom-checks-form").submit(function () {
			$("#submitBtnForGetDetailsPingdomChecks").attr('disabled', true);
		});
	});
</script>
@endpush