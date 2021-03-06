@extends('layout')

@section('title')
    Статусы пользователей
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item active">Статусы пользователей</li>
        </ol>
    </nav>
@stop

@section('content')
    В зависимости от вашей активности на сайте вы получаете определенный статус<br>
    При наборе определенного количества актива ваш статус меняется на вышестоящий<br>
    Актив - это сумма постов на форуме, гостевой, в комментариях и пр.<br><br>

    @if ($statuses->isNotEmpty())
        @foreach ($statuses as $status)

            <i class="fa fa-user-circle"></i>

            @if ($status->color)
                <b><span style="color:{{ $status->color }}">{{ $status->name }}</span></b> — {{ plural($status->topoint, setting('scorename')) }}<br>
            @else
                <b>{{ $status->name }}</b> — {{ plural($status->topoint, setting('scorename')) }}<br>
            @endif
        @endforeach

        <br>
    @else
        {!! showError('Статусы еще не назначены!') !!}
    @endif

    Некоторые статусы могут быть выделены определенными цветами<br>
    Самым активным юзерам администрация сайта может назначать особые статусы<br><br>
@stop
