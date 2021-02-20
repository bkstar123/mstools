@extends('cms.layouts.master')
@section('title', 'Verify custom SSL configuration for Cloudflare zones')

@section('content')
<div class="card card-info">
	<div class="card-header">
		<h3 class="card-title">Provide the list of Cloudflare zones which are to be verified</h3>
	</div>
	<form role="form" action="{{ route('checkcfzonessl') }}" method="post">
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
		</div>
		<div class="card-footer">
			<button type="submit" class="btn btn-success">Proceed</button>
		</div>
	</form>
</div>
@endsection