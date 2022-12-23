@extends('cms.layouts.master')
@section('title', 'Upload certificate to Cloudflare')

@section('content')
<div class="card card-info">
	<div class="card-header">
		<h3 class="card-title">Paste certificate/private key</h3>
	</div>
	<form id="uploadCertCFForm" role="form" action="{{ route('cfzonecertupload') }}" method="post">
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
        <div class="card-body">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12">
                        <div class="icheck-warning">
                            <input class="form-control"
                                   type="checkbox"
                                   id="useDeepValidation"
                                   name="useDeepValidation"><label for="useDeepValidation"> Use deep validation (will take longer time)</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12">
                        <div class="icheck-warning">
                            <input class="form-control"
                                   type="checkbox"
                                   id="useSmartCFZoneDetection"
                                   name="useSmartCFZoneDetection"><label for="useSmartCFZoneDetection"> Use Smart Auto-Detectipn of Cloudflare Zones</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<div class="card-footer">
			<button type="button" class="btn btn-success" data-toggle="modal" data-target="#uploadCertCFModal">Proceed</button>
		</div>
	</form>
    <!-- uploadCertCFModal -->
    <div class="modal fade" id="uploadCertCFModal" tabindex="-1" role="dialog" aria-labelledby="uploadCertCFTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="uploadCertCFLongTitle">
                        <i class="fas fa-exclamation-triangle"></i> Confirm your action
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure to upload the given pair of certificate/key to Cloudflare?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button id="submitBtn" type="button" class="btn btn-primary" onclick="sendToCloudflare()">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scriptBottom')
<script type="text/javascript">
    function sendToCloudflare() {
        let cert = $('textarea[name="cert"]').val();
        let key = $('textarea[name="privateKey"]').val();
        if (!cert.trim() || !key.trim()) {
            alert('The certificate and private key are required');
            return;
        }
        $('#uploadCertCFForm').submit();
    };
    $(document).ready(function () {
        $("#uploadCertCFForm").submit(function () {
            $("#submitBtn").attr('disabled', true);
        });
    });
</script>
@endpush