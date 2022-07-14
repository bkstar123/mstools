@extends('cms.layouts.master')
@section('title', 'Sites in the tracking')

@section('content')
<div class="card card-info">
	<div class="card-header">
		<h3 class="card-title">Sites in the tracking</h3>
	</div>
	<form id="form" role="form" action="{{ route('trackings.update', ['tracking' => $tracking->id]) }}" method="post">
		@csrf
		@method('PATCH')
		<div class="card-body">
			<div class="form-group">
				<label>Edit the list of sites in the tracking <span style="color:red">&midast;</span></label>
				<textarea class="form-control"
				          name="sites"
				          rows="5" 
				          placeholder="Paste the comma-seperated sites here">{{ $tracking->sites }}</textarea>
			</div>
		</div>
		<div class="card-footer">
			<button id="submitBtn" type="submit" class="btn btn-success" @cannot('trackings.update', $tracking) disabled @endcan>Update</button>
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