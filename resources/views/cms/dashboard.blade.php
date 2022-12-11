@extends('cms.layouts.master')
@section('title', 'Dashboard')

@section('content')
<div class="row">
	<div class="col col-md-12">
		<div class="card card-success">
			<div class="card-header">
				<h3 class="card-title">Export Pingdom checks</h3>
			</div>
			<form id="pingdom-checks-export-form" role="form" action="{{ route('pingdom.checks.export') }}" method="post">
				@csrf
				<div class="card-body">
					<div class="form-group">
						<label>List of tags (optional)</label>
						<textarea class="form-control"
						          name="tags"
				                  rows="5" 
				                  placeholder="Paste the comma-seperated tags here (no spaces)"></textarea>
				    </div>
				</div>
				<div class="card-footer">
					<button id="submitBtnForPingdomCheckExport" type="submit" class="btn btn-success">Export</button>
				</div>
			</form>
		</div>
	</div>
</div>
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
				                  placeholder="Paste the comma-seperated Pingdom check ids here">{{ old('checks') }}</textarea>
				    </div>
				</div>
				<div class="card-footer">
					<button id="submitBtnForGetDetailsPingdomChecks" type="submit" class="btn btn-success">Proceed</button>
				</div>
			</form>
		</div>
	</div>
</div>
<hr>
<div class="row">
	<div class="col col-md-12">
		<div class="card card-success">
			<div class="card-header">
				<h3 class="card-title">Get average uptime summary for Pingdom checks</h3>
			</div>
			<form id="get-pingdom-check-average-summary-form" 
			      role="form" 
			      action="{{ route('pingdom.checks.avg.summary') }}" 
			      method="post">
				@csrf
				<div class="card-body">
					<div class="form-group">
						<label>List of Pingdom check IDs for average uptime summary<span style="color:red">&midast;</span></label>
						@error('avgsmChecks')
						    <div class="alert alert-danger">{{ $message }}</div>
						@enderror
						<textarea class="form-control"
						          name="avgsmChecks"
				                  required
				                  rows="5" 
				                  placeholder="Paste the comma-seperated Pingdom check ids here">{{ old('avgsmChecks') }}</textarea>
				    </div>
				    <div class="row">
				        <div class="form-group col-md-3">
						    <label for="avgsmFrom">From (UTC) </label>
						    <div class="input-group">
							    <input type="date" 
							           required 
							           id="avgsmFrom" 
							           name="avgsmFrom" 
							           value="{{ Carbon\Carbon::now()->subWeek()->setTimezone('UTC')->format("Y-m-d") }}" 
							           class="form-control" />
                            </div>
				        </div>
				        <div class="form-group col-md-3">
						    <label for="avgsmTo">To (UTC) </label>
						    @error('avgsmTo')
						        <div class="alert alert-danger">{{ $message }}</div>
						    @enderror
						    <div class="input-group">
							    <input type="date" 
							           required
							           id="avgsmTo" 
							           name="avgsmTo" 
							           value="{{ Carbon\Carbon::now()->setTimezone('UTC')->format("Y-m-d") }}" 
							           class="form-control" />
                            </div>
				        </div>
				    </div>
				</div>
				<div class="card-footer">
					<button id="submitBtnForGetPingdomCheckAverageSummary" type="submit" class="btn btn-success">Proceed</button>
				</div>
			</form>
		</div>
	</div>
</div>
<hr>
@if(file_exists(storage_path('app/last_cloudflare_jdcloud_zones.txt')))
<div class="row">
	<div class="col col-md-12">
		<div class="card card-success">
			<div class="card-header">
				<h3 class="card-title">
				    China Network enabled zones on Cloudflare 
				    captured by {{ Carbon\Carbon::createFromTimestamp(filemtime(storage_path('app/last_cloudflare_jdcloud_zones.txt')))->timezone('UTC')->format('Y-m-d H:m:s') }} UTC  
				</h3>
			</div>
			<div class="card-body">
				{{ implode(",", json_decode(file_get_contents(storage_path('app/last_cloudflare_jdcloud_zones.txt')), true)) }}
			</div>
		</div>
	</div>
</div>
@endif
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
		$("#get-pingdom-check-average-summary-form").submit(function () {
			$("#submitBtnForGetPingdomCheckAverageSummary").attr('disabled', true);
		});
		$("#pingdom-checks-export-form").submit(function () {
			$("#submitBtnForPingdomCheckExport").attr('disabled', true);
		});
		$.datetimepicker.setLocale('en');
        let settings = {
            inline:true,
            weeks: true,
            format: 'Y-m-d',
            timepicker : false,
        }
        $("#avgsmFrom").datetimepicker(settings);
        $("#avgsmTo").datetimepicker(settings);
	});
</script>
@endpush