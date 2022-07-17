@extends('cms.layouts.master')
@section('title', 'Create/Edit About Page')

@section('content')
<div class="card">
	<div class="card-header">
		<h3>About me page</h3>
	</div>
	<div class="card-body">
		<form action="{{ route('about.store') }}" method="POST">
			@csrf
			<div class="form-group">
				<label for="content">Content</label>
				<textarea name="content" 
                          id="content" 
                          rows="8" 
                          class="form-control @error('content') is-invalid @enderror">{{ $about->content ?? '' }}</textarea>
                @error('content') 
                    <span class="invalid-feedback" role="alert">
                    	<strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <button class="btn btn-success" 
                    type="submit">
                <i class="fa fa-fw fa-lg fa-check-circle"></i>
                Save
            </button>
        </form>
    </div>
</div>            
@endsection

@push('scriptBottom')
@include('ckfinder::setup')
<script>
    ClassicEditor
        .create(document.querySelector('#content'), {
            ckfinder: {
                // Use named route for CKFinder connector entry point
                uploadUrl: '{{ route('ckfinder_connector') }}?command=QuickUpload&type=Files'
            },
            toolbar: {
                items: [
                    'heading',
                    '|',
                    'bold',
                    'italic',
                    'link',
                    'bulletedList',
                    'numberedList',
                    '|',
                    'indent',
                    'outdent',
                    '|',
                    'imageUpload',
                    'blockQuote',
                    'insertTable',
                    'mediaEmbed',
                    'undo',
                    'redo',
                    'alignment',
                    'fontBackgroundColor',
                    'CKFinder',
                    'fontColor',
                    'fontSize',
                    'highlight',
                    'fontFamily',
                    'horizontalLine',
                    'subscript',
                    'superscript',
                    'strikethrough'
                ]
            },
            language: 'en',
            image: {
                toolbar: [
                    'imageTextAlternative',
                    'imageStyle:full',
                    'imageStyle:side'
                ]
            },
            table: {
                contentToolbar: [
                    'tableColumn',
                    'tableRow',
                    'mergeTableCells',
                    'tableCellProperties',
                    'tableProperties'
                ]
            },
            licenseKey: '',       
        })
        .then( editor => {
            window.editor = editor;       
        })
        .catch(e => console.error(e));
</script>
@endpush