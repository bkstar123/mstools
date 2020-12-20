@extends('cms.layouts.master')
@section('title', 'Verify domain certificate')

@section('content')
<div class="card card-primary">
	<div class="card-header">
		<h3 class="card-title">Verify certificate for domains</h3>
	</div>
	<form role="form" action="{{ route('checkdomainssl') }}" method="post">
		@csrf
		<div class="card-body">
			<div class="form-group">
				<label>Domain List (comma-seperated)</label>
				@error('domains')
				    <div class="alert alert-danger">{{ $message }}</div>
				@enderror
				<textarea class="form-control"
				          name="domains"
				          required
				          row="5" 
				          placeholder="Paste the comma-seperated domains here"></textarea>
			</div>
		</div>
		<div class="card-footer">
			<button type="submit" class="btn btn-primary">Proceed</button>
		</div>
	</form>
</div>
@endsection