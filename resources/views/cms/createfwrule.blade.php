@extends('cms.layouts.master')
@section('title', 'Create firewall rule for Cloudflare zones')

@section('content')
<div class="card card-info">
	<div class="card-header bg-success">
		<h3 class="card-title">CREATE FIREWALL RULE</h3>
	</div>
	<form id="createCFFWRuleForm" role="form" action="{{ route('createfwrule') }}" method="post">
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
                <label>Description <span style="color:red">&midast;</span></label>
                @error('description')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                <input class="form-control"
                          type="text"
                          name="description"
                          required
                          value="{{ old('description') }}" 
                          placeholder="Describe the rule here, template: YourZendeskTicketNumber-yourAlias-RulePurpose" />
            </div>
        </div>
        <div class="card-body" id="action">
            <div class="form-group">
                <label>Action <span style="color:red">&midast;</span></label>
                @error('action')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                <select class="form-control"
                        name="action"
                        required>
                    <option value="" disabled selected>Select one of the following actions</option>
                    <option value="block" {{ old('action') == 'block' ? 'selected' : '' }}>Block</option>
                    <option value="allow" {{ old('action') == 'allow' ? 'selected' : '' }}>Allow</option> 
                    <option value="bypass" {{ old('action') == 'bypass' ? 'selected' : '' }}>Bypass</option>   
                    <option value="js_challenge" {{ old('action') == 'js_challenge' ? 'selected' : '' }}>JS Challenge</option>
                    <option value="challenge" {{ old('action') == 'challenge' ? 'selected' : '' }}>Legacy CAPTCHA</option>
                    <option value="managed_challenge" {{ old('action') == 'managed_challenge' ? 'selected' : '' }}>Managed Challenge</option>
                    <option value="log" {{ old('action') == 'log' ? 'selected' : '' }}>Log</option>    
                </select>
            </div>
        </div>
        <div class="card-body" id="expression">
            <div class="form-group">
                <label>Rule Expression <span style="color:red">&midast;</span></label>
                @error('expression')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                <textarea class="form-control"
                          name="expression"
                          required
                          rows="15" 
                          placeholder="Paste the rule expression here">{{ old('expression') }}</textarea>
            </div>
        </div>
		<div class="card-footer">
			<button type="button" class="btn btn-success" data-toggle="modal" data-target="#createCFFWRuleModal">Proceed</button>
		</div>
	</form>
    <!-- createCFFWRuleModal -->
    <div class="modal fade" id="createCFFWRuleModal" tabindex="-1" role="dialog" aria-labelledby="createCFFWRuleTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="createCFFWRuleLongTitle">
                        <i class="fas fa-exclamation-triangle"></i> Confirm your action
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure to send the given firewall rule settings to Cloudflare?
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
        let action = $('[name="action"]').val();
        let zones = $('[name="zones"]').val();
        let description = $('[name="description"]').val();
        let expression = $('[name="expression"]').val();
        if (!zones || !description || !action.length || !expression) {
            alert('You must fill all required inputs');
            return;
        } else if (action == 'bypass' && $('[name="products[]"]').val().length == 0) {
            alert('You must specify at least one feature for Bypass action');
            return;
        } else {
            $('#createCFFWRuleForm').submit();
        }
    };
    function toggleFeatureToBypassElement() {
        if ($("[name='action'").val() == 'bypass') {
            let element = '<div class="card-body" id="products">' + 
                '<div class="form-group">' +
                    '<label>Features to Bypass (required for Bypass action) <span style="color:red">&midast;</span></label>' +
                    '@error('products')' +
                        '<div class="alert alert-danger">{{ $message }}</div>' +
                    '@enderror' +
                    '<select class="form-control" name="products[]" multiple required>' +
                        '<option value="" disabled>Please select one or more features to bypass</option>' +
                        '<option value="uaBlock">User Agent Block</option>' +
                        '<option value="bic">Browser Integrity Check</option>' +
                        '<option value="hot">Hotlink Protection</option>' +
                        '<option value="securityLevel">Security Level</option>' +
                        '<option value="rateLimit">Rate Limit</option>' +
                        '<option value="waf">WAF Managed Rules</option>' +
                        '<option value="zoneLockdown">Zone Lockdown</option>' +
                    '</select>' +
                '</div>' +
            '</div>';
            $('#action').after(element);   
        } else {
            $('#products').remove();
        }
    }
    $(document).ready(function () {
        $("#createCFFWRuleForm").submit(function () {
            $("#submitBtn").attr('disabled', true);
        });
        $("[name='action']").on('change', function () {
            toggleFeatureToBypassElement();
        });
        if ($("[name='action']").val() == 'bypass') {
            toggleFeatureToBypassElement();
        }
    });
</script>
@endpush