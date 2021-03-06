@extends('layout')

@section('title')
    Шаблоны писем
@stop

@section('header')
    <div class="float-right">
        <a class="btn btn-success" href="/admin/notices/create">Добавить</a>
    </div><br>

    <h1>Шаблоны писем</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item active">Шаблоны писем</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($notices->isNotEmpty())
        @foreach ($notices as $notice)

            <div class="b">
                <i class="fa fa-envelope"></i> <b><a href="/admin/notices/edit/{{ $notice->id }}">{{ $notice->name }}</a></b>

                <div class="float-right">
                    @if ($notice->protect)
                        <i class="fa fa-lock"></i>
                    @else
                        <a href="/admin/notices/delete/{{ $notice->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить данный шаблон?')"><i class="fa fa-trash-alt"></i></a>
                    @endif
                </div>
            </div>

            <div>
                <span class="badge badge-info">Тип шаблона: {{ $notice->type }}</span><br>
                Изменено: {!! $notice->user->getProfile() !!}
                ({{ dateFixed($notice->updated_at) }})
            </div>
        @endforeach

        <br>Всего шаблонов: {{ $notices->count() }}<br><br>

    @else
        {!! showError('Шаблонов еще нет!') !!}
    @endif
@stop
