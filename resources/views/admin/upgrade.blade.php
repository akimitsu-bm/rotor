@extends('layout')

@section('title')
    Апгрейд системы
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item active">Апгрейд системы</li>
        </ol>
    </nav>
@stop

@section('content')
    {!! nl2br($wrap->getMigrate()) !!}

    <br>
    <div class="alert alert-success">
        <i class="fa fa-check"></i> <b>База данных в актуальном состоянии</b>
    </div>
@stop
