@extends('cms.layouts.master')
@section('title', 'Delete a firewall rule from Cloudflare zones')

@section('content')
<div class="card card-info">
	<div class="card-header bg-danger">
		<h3 class="card-title">DELETE FIREWALL RULE</h3>
	</div>
	<form id="deleteCFFWRuleForm" role="form" action="{{ route('deletefwrule') }}" method="post">
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
                          placeholder="Enter the description to identidy the rule that is to be updated" />
            </div>
        </div>
		<div class="card-footer">
			<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteCFFWRuleModal">Proceed</button>
		</div>
	</form>
    <!-- deleteCFFWRuleModal -->
    <div class="modal fade" id="deleteCFFWRuleModal" tabindex="-1" role="dialog" aria-labelledby="deleteCFFWRuleTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title" id="deleteCFFWRuleLongTitle">
                        <i class="fas fa-exclamation-triangle"></i> Confirm your action
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure to delete the firewall rule with the given description from the given Cloudflare zones?
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
            $('#deleteCFFWRuleForm').submit();
        }
    };
    $(document).ready(function () {
        $("#deleteCFFWRuleForm").submit(function () {
            $("#submitBtn").attr('disabled', true);
        });
    });
</script>
@endpush