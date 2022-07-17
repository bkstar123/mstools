@extends('cms.layouts.master')
@section('title', 'About Me')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <span class="badge bg-maroon"> MSTool guideline</span>
                </h3>
            </div>
            <div class="box-body">
                <div class="about-contents">
                    <p class="text-muted">
                        {!! $about->content ?? '' !!}
                    </p>
                </div>  
            </div>
        </div>
    </div>
</div>   
@endsection

@push('scriptBottom')
<script type="text/javascript">
    $(function(){
        $('div.about-contents img').attr('width','100%');
        $('div.about-contents img').attr('height','auto');
    });
</script>
@endpush