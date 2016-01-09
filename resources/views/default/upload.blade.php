@extends('layouts.app')

@section('meta')
@endsection

@section('scripts-body')
<script src="/js/app.js"></script>
<script src="/js/deps.js"></script>
<script>
    $(function(){
        $.ajaxPrefilter(function(options, originalOptions, xhr){
            var $token = $("#fileupload").find('input[name="_token"]').val();
            if ($token) {
                return xhr.setRequestHeader('X-XSRF-TOKEN', $token);
            }
        });
    });
</script>
@endsection

@section('content')

<div class="text-content">
    <div class="span7 offset1">
        @if(Session::has('success'))
        <div class="alert-box success">
            <h2>{!! Session::get('success') !!}</h2>
        </div>
        @endif
        <p id="pleaseSelect">Choose files from your machine</p>
        {!! Form::open(array('url'=>'process', 'method'=>'POST', 'files'=>true, 'id'=>'fileupload', 'multiple'=>true)) !!}
        <div class="control-group">
            <div class="controls">
                {!! Form::file('files[]', array()) !!}
                {!! Form::file('files[]', array()) !!}
            </div>
            <div class="errors"></div>
        </div>
        {!! Form::button('Upload', array('class'=>'btn btn-primary', 'id'=>'uploadBtn', 'disabled'=>true)) !!}
        {!! Form::close() !!}
    </div>
</div>

<div id="response"></div>

@endsection

