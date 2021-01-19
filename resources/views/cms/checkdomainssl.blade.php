@extends('cms.layouts.master')
@section('title', 'Verify SSL certificate data for domains')

@section('content')
<div class="card card-info">
	<div class="card-header">
		<h3 class="card-title">Provide the list of domains which are to be checked for SSL certificate data</h3>
	</div>
	<form role="form" action="{{ route('checkdomainssl') }}" method="post">
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
			<button type="submit" class="btn btn-success">Proceed</button>
		</div>
	</form>
</div>
@endsection

@push('scriptBottom')
<script type="text/javascript">
    Echo.private('user-' + {{ auth()->user()->id }})
        .listen('.verify-domain-ssldata.completed', (data) => {
        	$.notify(`MSTool has already emailed the check result of ${data.number_of_domains} domains to ${data.requestor}`, {
        		position: "right bottom",
        		className: "info",
        		clickToHide: true,
        		autoHide: false,
        	})
    });	
</script>
@endpush