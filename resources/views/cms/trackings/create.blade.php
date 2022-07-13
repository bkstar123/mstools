@extends('cms.layouts.master')
@section('title', 'Create DXP go-live tracking')

@section('content')
<div class="card card-info">
	<div class="card-header">
		<h3 class="card-title">Provide list of DXP sites to be tracked for go-live</h3>
	</div>
	<form id="form" role="form" action="{{ route('trackings.store') }}" method="post">
		@csrf
		<div class="card-body">
			<div class="form-group">
				<label>List of sites <span style="color:red">&midast;</span></label>
				@error('sites')
				    <div class="alert alert-danger">{{ $message }}</div>
				@enderror
				<textarea class="form-control"
				          name="sites"
				          required
				          rows="5" 
				          placeholder="Paste the comma-seperated sites here">{{ old('sites') }}</textarea>
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