@extends('cms.layouts.master')
@section('title', 'Upload certificate to Cloudflare')

@section('content')
<div class="card card-info">
	<div class="card-header">
		<h3 class="card-title">Paste certificate/private key</h3>
	</div>
	<form role="form" action="{{ route('cfzonecertupload') }}" method="post">
		@csrf
        <div class="card-body">
            <div class="form-group">
                <label>Zone List (Optional)</label>
                @error('zones')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                <textarea class="form-control"
                          name="zones"
                          rows="5" 
                          placeholder="Paste the comma-seperated zones here">{{ old('zones') }}</textarea>
            </div>
        </div>
		<div class="card-body">
			<div class="form-group">
				<label>Certificate <span style="color:red">&midast;</span></label>
				@error('cert')
				    <div class="alert alert-danger">{{ $message }}</div>
				@enderror
				<textarea class="form-control"
				          name="cert"
				          required
				          rows="15" 
				          placeholder="Paste the content of the certificate here">{{ old('cert') }}</textarea>
			</div>
		</div>
        <div class="card-body">
            <div class="form-group">
                <label>Private Key <span style="color:red">&midast;</span></label>
                @error('privateKey')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                <textarea class="form-control"
                          name="privateKey"
                          required
                          rows="15" 
                          placeholder="Paste the content of the private key here">{{ old('privateKey') }}</textarea>
            </div>
        </div>
		<div class="card-footer">
			<button type="submit" class="btn btn-success">Proceed</button>
		</div>
	</form>
</div>
@endsection