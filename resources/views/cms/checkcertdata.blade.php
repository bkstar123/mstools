@extends('cms.layouts.master')
@section('title', 'Decode certificate')

@section('content')
@if(!isset($ssl))
<div class="card card-info">
	<div class="card-header">
		<h3 class="card-title">Paste the certificate's content</h3>
	</div>
	<form role="form" action="{{ route('checkcertdata') }}" method="post">
		@csrf
		<div class="card-body">
			<div class="form-group">
				<label>Certificate content</label>
				@error('cert')
				    <div class="alert alert-danger">{{ $message }}</div>
				@enderror
				<textarea class="form-control"
				          name="cert"
				          required
				          rows="15" 
				          placeholder="Paste the content of the certificate here"></textarea>
			</div>
		</div>
		<div class="card-footer">
			<button type="submit" class="btn btn-success">Proceed</button>
		</div>
	</form>
</div>
@endif
@isset($ssl)
<div class="card card-info">
  <div class="card-header">Output</div>
  <div class="card-body">
    <h5 class="card-title">Certificate Data</h5>
    <p class="card-text">
    	<ul class="list-group">
    		<li class="list-group-item">
    			<strong>Common Name:</strong> 
    			{{ $ssl->getDomain() }}
    		</li>
    		<li class="list-group-item">
    			<strong>Organization:</strong> 
    			{{ $ssl->getOrganization() }}
    		</li>
    		<li class="list-group-item">
    			<strong>Organization Unit:</strong> 
    			{{ array_key_exists('OU', $ssl->getRawCertificateFields()['subject']) ? $ssl->getRawCertificateFields()['subject']['OU'] : '' }}
    		</li>
    		<li class="list-group-item">
    			<strong>Locality:</strong> 
    			{{ array_key_exists('L', $ssl->getRawCertificateFields()['subject']) ? $ssl->getRawCertificateFields()['subject']['L'] : '' }}
    		</li>
    		<li class="list-group-item">
    			<strong>State:</strong> 
    			{{ array_key_exists('ST', $ssl->getRawCertificateFields()['subject']) ? $ssl->getRawCertificateFields()['subject']['ST'] : '' }}
    		</li>
    		<li class="list-group-item">
    			<strong>Country:</strong> 
    			{{ array_key_exists('C', $ssl->getRawCertificateFields()['subject']) ? $ssl->getRawCertificateFields()['subject']['C'] : '' }}
    		</li>
    		<li class="list-group-item">
    			<strong>Valid from:</strong> 
    			{{ $ssl->validFromDate() }}
    		</li>
    		<li class="list-group-item">
    			<strong>Valid until:</strong> 
    			{{ $ssl->expirationDate() }}
    		</li>
    		<li class="list-group-item">
    			<strong>Issuer:</strong> 
    			{{ $ssl->getIssuer() }}
    		</li>
    		<li class="list-group-item">
    			<strong>Issuer:</strong> 
    			{{ $ssl->getIssuer() }}
    		</li>
    		<li class="list-group-item">
    			<strong>Fingerprint:</strong> 
    			{{ $ssl->getFingerprint() }}
    		</li>
    		<li class="list-group-item">
    			<strong>SAN:</strong> 
    			{{ implode(',', $ssl->getAdditionalDomains()) }}
    		</li>
    	</ul>	
    </p>
  </div>
  <div class="card-footer">
      <a href="{{ route('checkcertdata') }}"" class="btn btn-success">Back</a>
  </div>
</div>
@endisset
@endsection