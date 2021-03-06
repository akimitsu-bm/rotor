@extends('layout')

@section('title')
    Топ популярных файлов (Стр. {{ $page->current }})
@stop

@section('header')
    <h1>Топ популярных файлов</h1>
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/loads">Загрузки</a></li>
            <li class="breadcrumb-item active">Топ файлов</li>
        </ol>
    </nav>
@stop

@section('content')
    Сортировать:
    <?php $active = ($order === 'loads') ? 'success' : 'light'; ?>
    <a href="/loads/top?sort=loads" class="badge badge-{{ $active }}">Скачивания</a>

    <?php $active = ($order === 'rated') ? 'success' : 'light'; ?>
    <a href="/loads/top?sort=rated" class="badge badge-{{ $active }}">Оценки</a>

    <?php $active = ($order === 'count_comments') ? 'success' : 'light'; ?>
    <a href="/loads/top?sort=comments" class="badge badge-{{ $active }}">Комментарии</a>
    <hr>

    @if ($downs->isNotEmpty())

        @foreach ($downs as $data)
            <?php $rating = $data->rated ? round($data->rating / $data->rated, 1) : 0; ?>

            <div class="b">
                <i class="fa fa-file"></i>
                <b><a href="/downs/{{ $data->id }}">{{ $data->title }}</a></b> ({{ $data->count_comments }})
            </div>

            <div>
                Категория: <a href="/loads/{{ $data->category->id }}">{{ $data->category->name }}</a><br>
                Рейтинг: {{ $rating }}<br>
                Скачиваний: {{ $data->loads }}<br>
                <a href="/downs/comments/{{ $data->id }}">Комментарии</a> ({{ $data->count_comments }})
                <a href="/downs/end/{{ $data->id }}">&raquo;</a>
            </div>
        @endforeach

        {!! pagination($page) !!}
    @else
        @if (! $category->closed)
            {!! showError('Файлы не найдены!') !!}
        @endif
    @endif
@stop
