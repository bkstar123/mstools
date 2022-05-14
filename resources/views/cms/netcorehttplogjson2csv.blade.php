@extends('cms.layouts.master')
@section('title', '.Net Core HTTP Log Json-to-CSV Conversion')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Upload HTTP Log JSON file for conversion</div>
                <div id="card-body" class="card-body">
                    <div class="form-group">
                        <label for="file-upload">Upload HTTP Log JSON File</label>
                        <input type="file" class="form-control" name="httplog" id="file-upload">
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scriptBottom')
<script type="text/javascript">
$(document).ready(function () {
    $('#file-upload').bkstar123_ajaxuploader({
    	size: {{ config('mstools.maxFileUpload') }},
        allowedExtensions: ['json'],
        batchSize: 1,
        outerClass: 'col-md-12',
        uploadUrl: '{{ route('upload.httplog.jsonfile') }}'
    });
});
</script>
@endpush