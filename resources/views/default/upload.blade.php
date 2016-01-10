@extends('layouts.app')

@section('meta')
@endsection

@section('scripts-head')
<script src="/js/deps.js"></script>
@endsection

@section('scripts-body')
<script src="/js/app.js"></script>
<script>
    $(function(){
        var token = $("#fileupload").find('input[name="_token"]').val();
        if (token) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': token
                }
            });
        }
    });
</script>
@endsection

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <h2 id="pleaseSelect">Choose files from your machine</h2>
            {!! Form::open(array(
            'url'=>'process',
            'method'=>'POST',
            'files'=>true,
            'id'=>'fileupload',
            'multiple'=>true,
            'class'=>'form form-horizontal')) !!}
            <div class="form-group">
                <label for="file1" class="col-sm-2 control-label">File 1</label>
                <div class="col-sm-10">
                    {!! Form::file('files[]', array('class'=>'form-control', 'id'=>'file1')) !!}
                </div>
            </div>
            <div class="form-group">
                <label for="file2" class="col-sm-2 control-label">File 2</label>
                <div class="col-sm-10">
                    {!! Form::file('files[]', array('class'=>'form-control', 'id'=>'file2')) !!}
                </div>
            </div>
            <div class="form-group buttonsContainer">
                <div class="col-sm-offset-2 col-sm-10 buttons">
                    {!! Form::button('Upload', array(
                    'class'=>'btn btn-primary',
                    'id'=>'uploadBtn',
                    'disabled'=>true)) !!}
                </div>
            </div>
            {!! Form::close() !!}
            
            <div class="col-md-12">
                <div id="response"></div>
            </div>
            
        </div>
    </div>
</div>

@endsection

