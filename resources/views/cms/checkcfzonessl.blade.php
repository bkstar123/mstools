@extends('cms.layouts.master')
@section('title', 'Verify custom SSL configuration for CF zones')

@section('content')
<div class="card card-primary">
	<div class="card-header">
		<h3 class="card-title">Verify custom SSL configuration for CF zones</h3>
	</div>
	<form role="form" action="{{ route('checkcfzonessl') }}" method="post">
		@csrf
		<div class="card-body">
			<div class="form-group">
				<label>Zone List (comma-seperated)</label>
				@error('domains')
				    <div class="alert alert-danger">{{ $message }}</div>
				@enderror
				<textarea class="form-control"
				          name="zones"
				          required
				          row="5" 
				          placeholder="Paste the comma-seperated zones here"></textarea>
			</div>
		</div>
		<div class="card-footer">
			<button type="submit" class="btn btn-primary">Proceed</button>
		</div>
	</form>
</div>
@endsection