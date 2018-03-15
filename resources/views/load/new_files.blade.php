@extends('layout')

@section('title')
    Загрузки - Новые файлы (Стр. {{ $page['current'] }})
@stop

@section('content')
    <h1>Новые файлы</h1>

    @if ($downs->isNotEmpty())
        @foreach ($downs as $down)

            <?php $folder = $down->category->folder ? $down->category->folder.'/' : '' ?>
            <?php $filesize = $down->link ? formatFileSize(UPLOADS.'/files/'.$folder.$down->link) : 0; ?>

            <div class="b">
                <i class="fa fa-file"></i>
                <b><a href="/down/{{ $down->id }}">{{ $down->title }}</a></b> ({{ $filesize }})
            </div>
            <div>
                Категория: <a href="/load/{{ $down->category->id }}">{{ $down->category->name }}</a><br>
                Скачиваний: {{ $down->loads }}<br>

                <?php $rating = $down->rated ? round($down->rating / $down->rated, 1) : 0; ?>

                Рейтинг: <b>{{ $rating }}</b> (Голосов: {{ $down->rated }})<br>
                Автор: {!! profile($down->user) !!} ({{ dateFixed($down->created_at) }})
            </div>

        @endforeach

        {!! pagination($page) !!}
    @else
        {!! showError('Опубликованных файлов еще нет!') !!}
    @endif
@stop
