@extends('layout')

@section('title')
    Онлайн пользователей
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Онлайн пользователей</li>
        </ol>
    </nav>
@stop

@section('content')
    <div class="b"><b>Кто на сайте:</b></div>

    @if ($online->isNotEmpty())

        @foreach($online as $key => $value)
            {{ $comma = (empty($key)) ? '' : ', ' }}
            {!! $value->user->getGender() !!} <b>{!! $value->user->getProfile() !!}</b>
        @endforeach

        <br>Всего пользователей: {{ $online->count() }} чел.<br><br>
    @else
        {!! showError('Зарегистированных пользователей нет!') !!}
    @endif

    <div class="b"><b>Поздравляем именинников:</b></div>

    @if ($birthdays->isNotEmpty())

        @foreach($birthdays as $key => $value)
            {{ $comma = (empty($key)) ? '' : ', ' }}
            {!! $value->getGender() !!} <b>{!! $value->getProfile() !!}</b>
        @endforeach

        <br>Всего именниников: {{ $birthdays->count() }} чел.<br><br>
    @else
        {!! showError('Сегодня именинников нет!') !!}
    @endif

    <div class="b"><b>Приветствуем новичков:</b></div>

    @if ($novices->isNotEmpty())
        @foreach($novices as $key => $value)
            {{ $comma = (empty($key)) ? '' : ', ' }}
            {!! $value->getGender() !!} <b>{!! $value->getProfile() !!}</b>
        @endforeach

        <br>Всего новичков: {{ $novices->count() }} чел.<br><br>
    @else
        {!! showError('Новичков пока нет!') !!}
    @endif

@stop
