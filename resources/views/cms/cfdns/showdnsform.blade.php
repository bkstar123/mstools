@extends('cms.layouts.master')
@section('title', 'Fetch DNS hostname entries for zones')

@section('content')
<div class="card card-info">
	<div class="card-header">
		<h3 class="card-title">Provide the list of Cloudflare zones to fetch DNS hostname entries for</h3>
	</div>
	<form id="form" role="form" action="{{ route('cfdnsrecords.get') }}" method="post">
		@csrf
		<div class="card-body">
			<div class="form-group">
				<label>Zone List <span style="color:red">&midast;</span></label>
				@error('zones')
				    <div class="alert alert-danger">{{ $message }}</div>
				@enderror
				<textarea class="form-control"
				          name="zones"
				          required
				          rows="5" 
				          placeholder="Paste the comma-seperated zones here">{{ old('zones') }}</textarea>
			</div>
			<div class="form-group">
				<div class="row">
					<div class="col-md-12">
						<div class="icheck-warning">
							<input class="form-control"
							       type="checkbox"
							       id="onlyProd"
							       name="onlyProd"
							       checked><label for="onlyProd"> Include only production environments' hostnames</label>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="row">
					<div class="col-md-12">
						<div class="icheck-warning">
							<input class="form-control"
							       type="checkbox"
							       id="onlyProxied"
							       name="onlyProxied"
							       checked><label for="onlyProxied"> Include only CF-proxied hostnames</label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="card-footer">
			<button id="submitBtn" type="submit" class="btn btn-success">Proceed</button>
		</div>
	</form>
</div>
@endsection

@push('scriptBottom')
<script type="text/javascript">
	$(document).ready(function () {
		$("#form").submit(function () {
			$("#submitBtn").attr('disabled', true);
		});
	});
</script>
@endpush