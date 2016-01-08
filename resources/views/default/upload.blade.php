@extends('layouts.app')

@section('content')

<div class="text-content">
    <div class="span7 offset1">
        @if(Session::has('success'))
        <div class="alert-box success">
            <h2>{!! Session::get('success') !!}</h2>
        </div>
        @endif
        <div class="secure">Choose two or more files from your machine</div>
        {!! Form::open(array('url'=>'upload','method'=>'POST', 'files'=>true)) !!}
        <div class="control-group">
            <div class="controls">
                {!! Form::file('files[]', array('multiple'=>true)) !!}
                <p class="errors">{!!$errors->first('images')!!}</p>
                @if(Session::has('error'))
                <p class="errors">{!! Session::get('error') !!}</p>
                @endif
            </div>
        </div>
        <button class="btn submit" id="uploadBtn">Upload</button>
        {!! Form::close() !!}
    </div>
</div>
    

    <!-- TODO: Current Tasks -->
    @endsection