@extends('cms.layouts.master')
@section('title', 'Verify firewall rule existence for zones')

@section('content')
<div class="card card-info">
	<div class="card-header bg-primary">
		<h3 class="card-title">VERIFY FIREWALL RULE EXISTENCE</h3>
	</div>
	<form id="verifyCFFWRuleForm" role="form" action="{{ route('verifyruleexistence') }}" method="post">
		@csrf
        <div class="card-body" id="zones">
            <div class="form-group">
                <label>Zone List <span style="color:red">&midast;</span></label>
                @error('zones')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                <textarea class="form-control"
                          name="zones"
                          rows="5" 
                          placeholder="Paste the comma-seperated zones here">{{ old('zones') }}</textarea>
            </div>
        </div>
        <div class="card-body" id="description">
            <div class="form-group">
                <label>The description to find the rule <span style="color:red">&midast;</span></label>
                @error('description')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                <input class="form-control"
                          type="text"
                          name="description"
                          required
                          value="{{ old('description') }}" 
                          placeholder="Enter the description to identidy the rule that is to be verified for existence" />
            </div>
        </div>
		<div class="card-footer">
			<button type="button" class="btn btn-success" data-toggle="modal" data-target="#verifyCFFWRuleModal">Proceed</button>
		</div>
	</form>
    <!-- verifyCFFWRuleModal -->
    <div class="modal fade" id="verifyCFFWRuleModal" tabindex="-1" role="dialog" aria-labelledby="verifyCFFWRuleTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="verifyCFFWRuleTitle">
                        <i class="fas fa-exclamation-triangle"></i> Confirm your action
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure that you want to check the existence of the given rule description for the given zones?
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
        let zones = $('[name="zones"]').val();
        let description = $('[name="description"]').val();
        if (!zones || !description) {
            alert('You must fill the zones and description fields');
            return;
        } else {
            $('#verifyCFFWRuleForm').submit();
        }
    };
    $(document).ready(function () {
        $("#verifyCFFWRuleForm").submit(function () {
            $("#submitBtn").attr('disabled', true);
        });
    });
</script>
@endpush