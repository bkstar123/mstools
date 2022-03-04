@extends('cms.layouts.master')
@section('title', 'Update a firewall rule for Cloudflare zones')

@section('content')
<div class="card card-info">
	<div class="card-header bg-primary">
		<h3 class="card-title">UPDATE FIREWALL RULE</h3>
	</div>
	<form id="updateCFFWRuleForm" role="form" action="{{ route('updatefwrule') }}" method="post">
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
        <div class="card-body" id="new_action">
            <div class="form-group">
                <label>New Action </label>
                <select class="form-control"
                        name="new_action">
                    <option value="" disabled selected>Select one of the following actions</option>
                    <option value="block" {{ old('new_action') == 'block' ? 'selected' : '' }}>Block</option>
                    <option value="allow" {{ old('new_action') == 'allow' ? 'selected' : '' }}>Allow</option> 
                    <option value="bypass" {{ old('new_action') == 'bypass' ? 'selected' : '' }}>Bypass</option>   
                    <option value="js_challenge" {{ old('new_action') == 'js_challenge' ? 'selected' : '' }}>JS Challenge</option>
                    <option value="challenge" {{ old('new_action') == 'challenge' ? 'selected' : '' }}>Legacy CAPTCHA</option>
                    <option value="managed_challenge" {{ old('new_action') == 'managed_challenge' ? 'selected' : '' }}>Managed Challenge</option>
                    <option value="log" {{ old('new_action') == 'log' ? 'selected' : '' }}>Log</option>    
                </select>
            </div>
        </div>
        <div class="card-body" id="new_expression">
            <div class="form-group">
                <label>New Rule Expression </label>
                <textarea class="form-control"
                          name="new_expression"
                          rows="15" 
                          placeholder="Paste the new rule expression here"></textarea>
            </div>
        </div>
        <div class="card-body" id="new_description">
            <div class="form-group">
                <label>New description for the rule </label>
                <input class="form-control"
                          type="text"
                          name="new_description"
                          placeholder="Enter the new description for the rule" />
            </div>
        </div>
        <div class="card-body" id="paused">
            <div class="form-group">
                <label>Paused </label>
                <select class="form-control"
                        name="paused">
                    <option value="" disabled selected>Specify the new status for the rule </option>
                    <option value="true">TRUE</option>
                    <option value="false">FALSE</option>   
                </select>
            </div>
        </div>
		<div class="card-footer">
			<button type="button" class="btn btn-success" data-toggle="modal" data-target="#updateCFFWRuleModal">Proceed</button>
		</div>
	</form>
    <!-- updateCFFWRuleModal -->
    <div class="modal fade" id="updateCFFWRuleModal" tabindex="-1" role="dialog" aria-labelledby="updateCFFWRuleTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="updateCFFWRuleLongTitle">
                        <i class="fas fa-exclamation-triangle"></i> Confirm your action
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure to send the given updated settings to Cloudflare?
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
        let new_action =  $('[name="new_action"]').val();
        if (!zones || !description) {
            alert('You must fill the zones and description fields');
            return;
        } else if (new_action == 'bypass' && $('[name="products[]"]').val().length == 0) {
            alert('You must specify at least one feature for Bypass action');
            return;
        } else {
            $('#updateCFFWRuleForm').submit();
        }
    };
    function toggleFeatureToBypassElement() {
        if ($("[name='new_action'").val() == 'bypass') {
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
            $('#new_action').after(element);   
        } else {
            $('#products').remove();
        }
    }
    $(document).ready(function () {
        $("#updateCFFWRuleForm").submit(function () {
            $("#submitBtn").attr('disabled', true);
        });
        $("[name='new_action']").on('change', function () {
            toggleFeatureToBypassElement();
        });
        if ($("[name='new_action']").val() == 'bypass') {
            toggleFeatureToBypassElement();
        }
    });
</script>
@endpush