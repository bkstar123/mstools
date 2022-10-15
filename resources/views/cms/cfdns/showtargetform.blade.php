@extends('cms.layouts.master')
@section('title', 'Fetch Cloudflare DNS targets for hostnames')

@section('content')
<div class="card card-info">
	<div class="card-header">
		<h3 class="card-title">Provide the list of Cloudflare zones to fetch DNS hostname entries for</h3>
	</div>
	<form id="form" role="form" action="{{ route('cfdnstargets.get') }}" method="post">
		@csrf
		<div class="card-body">
			<div class="form-group">
				<label>List of hostnames <span style="color:red">&midast;</span></label>
				@error('hostnames')
				    <div class="alert alert-danger">{{ $message }}</div>
				@enderror
				<textarea class="form-control"
				          name="hostnames"
				          required
				          rows="5" 
				          placeholder="Paste the comma-seperated hostnames here">{{ old('hostnames') }}</textarea>
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