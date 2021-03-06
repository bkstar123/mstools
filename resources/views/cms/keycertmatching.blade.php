@extends('cms.layouts.master')
@section('title', 'Key-certificate matching')

@section('content')
<div class="card card-info">
	<div class="card-header">
		<h3 class="card-title">Private key/certificate matching</h3>
	</div>
	<form id="form" role="form" action="{{ route('keycertmatching') }}" method="post">
		@csrf
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
			<button id="submitBtn" type="submit" class="btn btn-success">Verify</button>
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