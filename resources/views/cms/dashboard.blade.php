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
						          id="avgsmChecks"
						          name="avgsmChecks"
				                  required
				                  rows="5" 
				                  placeholder="Paste the comma-seperated Pingdom check ids here">{{ old('avgsmChecks') }}</textarea>
				    </div>
				    <div class="row">
				        <div class="form-group col-md-6">
						    <label for="avgsmFrom">From (UTC) </label>
						    <div class="input-group">
							    <input type="datetime" 
							           required 
							           id="avgsmFrom" 
							           name="avgsmFrom" 
							           value="{{ Carbon\Carbon::now()->subWeek()->setTimezone('UTC')->format("Y-m-d H:i") }}" 
							           class="form-control" />
                            </div>
				        </div>
				        <div class="form-group col-md-6">
						    <label for="avgsmTo">To (UTC) </label>
						    @error('avgsmTo')
						        <div class="alert alert-danger">{{ $message }}</div>
						    @enderror
						    <div class="input-group">
							    <input type="datetime" 
							           required
							           id="avgsmTo" 
							           name="avgsmTo" 
							           value="{{ Carbon\Carbon::now()->setTimezone('UTC')->format("Y-m-d H:i") }}" 
							           class="form-control" />
                            </div>
				        </div>
				    </div>
				</div>
				<div class="card-footer">
					<button id="submitBtnForGetPingdomCheckAverageSummary" type="submit" class="btn btn-success">Generate Report</button>
					<button id="submitBtnForGetPingdomCheckAverageSummaryUI" class="btn btn-success">See in UI</button>
				</div>
			</form>
		</div>
	</div>
</div>
<hr>
<div id="avgSummaryContainer"></div>
<hr>
@if(file_exists(storage_path('app/last_cloudflare_jdcloud_zones.txt')))
<div class="row">
	<div class="col col-md-12">
		<div class="card card-success">
			<div class="card-header">
				<h3 class="card-title">
				    China Network enabled zones on Cloudflare 
				    captured by {{ Carbon\Carbon::createFromTimestamp(filemtime(storage_path('app/last_cloudflare_jdcloud_zones.txt')))->timezone('UTC')->toDateTimeString() }} UTC  
				</h3>
			</div>
			<div class="card-body">
				{{ implode(",", json_decode(file_get_contents(storage_path('app/last_cloudflare_jdcloud_zones.txt')), true)) }}
			</div>
		</div>
	</div>
</div>
@endif
<div class="row">
	<div class="col col-md-12">
		<div class="card card-success">
			<div class="card-header">
				<h3 class="card-title">
				    Search for Custom Origin Server of the given CF-for-SaaS hostname 
				</h3>
			</div>
			<div class="card-body">
				<input type="text"
				    required
				    id="saasHostnames"
				    name="saasHostnames"
				    value="{{ old('saasHostnames') }}"
				    placeholder="Enter your inquired CF-for-SaaS hostnames (seperated by comma)"
				    class="form-control"></input>
			</div>
			<div class="card-footer">
				<button id="submitBtnForExportSaaSHostnames" class="btn btn-success">Export to CSV</button>
				<button id="submitBtnForSearchSaaSHostnames" class="btn btn-success">View on browser</button>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scriptBottom')
<script src="{{ url('/js/vendor/highcharts/highcharts.js') }}"></script>
<script src="https://code.highcharts.com/modules/variwide.js"></script>
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
		$("#submitBtnForGetPingdomCheckAverageSummaryUI").click(function (e) {
			e.preventDefault();
			$("#avgSummaryContainer").empty();
			$("#submitBtnForGetPingdomCheckAverageSummaryUI").prop('disabled', true);
			setTimeout(function() {
				$("#submitBtnForGetPingdomCheckAverageSummaryUI").prop('disabled', false);
			}, 10000);
			let avgsmChecks = $("#avgsmChecks").val();
			let avgsmFrom = $("#avgsmFrom").val();
			let avgsmTo = $("#avgsmTo").val();
			let settings = {
				'url': @json(route('pingdom.avg.summary.ui')),
				'method': 'GET',
				'data': {
					'avgsmFrom': avgsmFrom,
					'avgsmTo': avgsmTo,
					'avgsmChecks': avgsmChecks
				}
			};
			$.ajax(settings)
			 .done(function(res) {
			 	const data = JSON.parse(res);
			 	let seriesData = [];
				data.summary.states.forEach(function (state) {
				    let color;
				    switch(state.status) {
				        case 'up':
				            color = '#0ac210';
				            break;
				        case 'down':
				            color = '#f70000';
				            break;
				        case 'unknown':
				            color = '#b0aeae';
				            break;
				    };	5
				    seriesData.push({
				        name: moment.unix(state.timefrom).utc().format("YYYY-MM-DD HH:mm"),
				        y: 1,
				        z: state.timeto - state.timefrom,
				        color: color
				    });
				});
				let chart = Highcharts.chart('avgSummaryContainer', {
				    chart: {
				        type: 'variwide'
				    },
				    title: {
				        text: 'Outage Summary'
				    },				    
				    xAxis: {
				        type: 'category',
				        visible: false
				    },
				    yAxis: {
				        visible: false
				    },				    
				    series: [{
				        name: 'Outage Summary',
				        data: seriesData,
				        borderRadius: 3,
				        borderWidth: 0,
				        minPointLength: 10
				    }]
				});

			});
		});
		$.datetimepicker.setLocale('en');
        let datetimeSettings = {
            inline:true,
            weeks: true,
            format: 'Y-m-d H:i',
            timepicker : true,
        }
        $("#avgsmFrom").datetimepicker(datetimeSettings);
        $("#avgsmTo").datetimepicker(datetimeSettings);
        $("#submitBtnForSearchSaaSHostnames").click(function (e) {
        	e.preventDefault();
        	$("#submitBtnForSearchSaaSHostnames").prop('disabled', true);
			setTimeout(function() {
				$("#submitBtnForSearchSaaSHostnames").prop('disabled', false);
			}, 10000);
        	let saasHostnames = $("#saasHostnames").val();
        	if (saasHostnames.length <= 0) {
        		alert("You must provide the list of hostnames");
        	}
        	let settings = {
				'url': @json(route('cf4saas.getcustomoriginserver')),
				'method': 'POST',
				'headers': {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				'data': {
					'saasHostnames': saasHostnames
				}
			};
			$.ajax(settings).done(function (res) {
				if ($("#cf4SaasSearchDisplayTable").length <= 0) {
					let html = `<hr/><table class="table table-striped" id="cf4SaasSearchDisplayTable"
					<thead><tr><th>Hostname</th><th>Custom Origin Server</th><th>Status</th><th>Created At</th></tr></thead>
					<tbody id="cf4SaasSearchDisplayBody"></tbody></table>`;
					$(html).insertAfter('#submitBtnForSearchSaaSHostnames');
				}
				res.forEach(function (item) {
					$("#cf4SaasSearchDisplayBody").append(`<tr><td>${item.hostname}</td><td>${item.custom_origin_server}</td><td>${item.status}</td><td>${item.created_at}</td></tr>`);
				});
			});
        });
        $("#submitBtnForExportSaaSHostnames").click(function (e) {
        	e.preventDefault();
        	$("#submitBtnForExportSaaSHostnames").prop('disabled', true);
			setTimeout(function() {
				$("#submitBtnForExportSaaSHostnames").prop('disabled', false);
			}, 10000);
        	let saasHostnames = $("#saasHostnames").val();
        	if (saasHostnames.length <= 0) {
        		alert("You must provide the list of hostnames, or enter '*' to export all");
        	}
        	let settings = {
				'url': @json(route('cf4saas.exporthostnames')),
				'method': 'POST',
				'headers': {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				'data': {
					'saasHostnames': saasHostnames
				}
			};
			$.ajax(settings).done(function (res) {
				alert("MSTools is processing your request, close this dialog and wait for notification of completion")
			});
        });
	});
</script>
@endpush