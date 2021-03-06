@extends('layout')

@section('title')
    Загрузки - Список файлов {{ $user->login }} (Стр. {{ $page->current }})
@stop

@section('header')
    <h1>Файлы {{ $user->login }}</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">Загрузки</a></li>
            <li class="breadcrumb-item active">Файлы {{ $user->login }}</li>
        </ol>
    </nav>
@stop

@section('content')
    @if ($user->id === getUser('id'))
        <?php $type = ($active === 1) ? 'success' : 'light'; ?>
        <a href="/downs/active/files?active=1" class="badge badge-{{ $type }}">Проверенные</a>

        <?php $type = ($active === 0) ? 'success' : 'light'; ?>
        <a href="/downs/active/files?active=0" class="badge badge-{{ $type }}">Ожидающие</a>
    @endif

    @if ($downs->isNotEmpty())
        @foreach ($downs as $down)
            <?php $rating = $down->rated ? round($down->rating / $down->rated, 1) : 0; ?>

            <div class="b">
                <i class="fa fa-file"></i>
                <b><a href="/downs/{{ $down->id }}">{{ $down->title }}</a></b> ({{ $down->count_comments }})
            </div>
            <div>
                Категория: <a href="/loads/{{ $down->category->id }}">{{ $down->category->name }}</a><br>
                Рейтинг: {{ $rating }}<br>
                Скачиваний: {{ $down->loads }}<br>
                Автор: {!! $down->user->getProfile() !!} ({{ dateFixed($down->created_at) }})
            </div>

        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Опубликованных файлов еще нет!') !!}
    @endif
@stop
