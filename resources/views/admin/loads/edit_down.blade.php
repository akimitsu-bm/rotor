@extends('layout')

@section('title')
    Редактирование загрузки {{ $down->title }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/admin">{{ trans('main.panel') }}</a></li>
            <li class="breadcrumb-item"><a href="/admin/loads">Загрузки</a></li>

            @if ($down->category->parent->id)
                <li class="breadcrumb-item"><a href="/admin/loads/{{ $down->category->parent->id }}">{{ $down->category->parent->name }}</a></li>
            @endif

            <li class="breadcrumb-item"><a href="/admin/loads/{{ $down->category->id }}">{{ $down->category->name }}</a></li>
            <li class="breadcrumb-item active">Редактирование</li>
            <li class="breadcrumb-item"><a href="/downs/{{ $down->id }}">Обзор загрузки</a></li>
        </ol>
    </nav>
@stop

@section('content')
    @if (! $down->active)
        <div class="p-1 bg-warning text-dark">
            <b>Внимание!</b> Данная загрузка ожидает проверки модератором!<br>
        </div><br>
    @endif

    @if (isAdmin('boss'))
        <i class="fa fa-pencil-alt"></i>

        @if ($down->active)
            <a href="/admin/downs/publish/{{ $down->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите снять с публикации данную загрузку?')">Снять с публикации</a> /
        @else
            <a href="/admin/downs/publish/{{ $down->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите опубликовать данную загрузку?')">Опубликовать</a> /
        @endif

        <a href="/admin/downs/delete/{{ $down->id }}?token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить данную загрузку?')">Удалить</a><br>
    @endif

    <div class="form mb-3">
        <form action="/admin/downs/edit/{{ $down->id }}" method="post" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('category') }}">
                <label for="inputCategory">Категория</label>

                <?php $inputCategory = getInput('category', $down->category_id); ?>
                <select class="form-control" id="inputCategory" name="category">
                    @foreach ($categories as $category)

                        <option value="{{ $category->id }}"{{ ($inputCategory === $category->id && ! $category->closed) ? ' selected' : '' }}{{ $category->closed ? ' disabled' : '' }}>{{ $category->name }}</option>

                        @if ($category->children->isNotEmpty())
                            @foreach($category->children as $datasub)
                                <option value="{{ $datasub->id }}"{{ $inputCategory === $datasub->id && ! $datasub->closed ? ' selected' : '' }}{{ $datasub->closed ? ' disabled' : '' }}>– {{ $datasub->name }}</option>
                            @endforeach
                        @endif
                    @endforeach

                </select>
                {!! textError('category') !!}
            </div>

            <div class="form-group{{ hasError('title') }}">
                <label for="title">Название:</label>
                <input class="form-control" name="title" id="title" maxlength="50" value="{{ getInput('title', $down->title) }}" required>
                {!! textError('title') !!}
            </div>

            <div class="form-group{{ hasError('text') }}">
                <label for="text">Текст:</label>
                <textarea class="form-control markItUp" id="text" name="text" rows="5">{{ getInput('text', $down->text) }}</textarea>
                {!! textError('text') !!}
            </div>

            @if ($down->getFiles()->isNotEmpty())
                @foreach ($down->getFiles() as $file)
                <i class="fa fa-download"></i>
                <b><a href="{{ $file->hash }}">{{ $file->name }}</a></b> ({{ formatSize($file->size) }}) (<a href="/admin/downs/delete/{{ $down->id }}/{{ $file->id }}" onclick="return confirm('Вы действительно хотите удалить данный файл?')">Удалить</a>)<br>
                @endforeach
            @endif

            @if ($down->getImages()->isNotEmpty())
                @foreach ($down->getImages() as $image)
                    {!! resizeImage($image->hash) !!}<br>
                    <i class="fa fa-image"></i> <b><a href="{{ $image->hash }}">{{ $image->name }}</a></b> ({{ formatSize($image->size ) }}) (<a href="/admin/downs/delete/{{ $down->id }}/{{ $image->id }}" onclick="return confirm('Вы действительно хотите удалить данный скриншот?')">Удалить</a>)<br><br>
                @endforeach
            @endif

            @if ($down->files->count() < setting('maxfiles'))
                <label class="btn btn-sm btn-secondary" for="files">
                    <input type="file" id="files" name="files[]" onchange="$('#upload-file-info').html((this.files.length > 1) ? this.files.length + ' файлов' : this.files[0].name);" hidden multiple>
                    Прикрепить файлы&hellip;
                </label>
                <span class="badge badge-info" id="upload-file-info"></span>
                {!! textError('files') !!}
                <br>
            @endif

            <p class="text-muted font-italic">
                Можно загрузить до {{ setting('maxfiles') }} файлов<br>
                Максимальный вес файла: {{ formatSize(setting('fileupload')) }}<br>
                Допустимые расширения файлов: {{ str_replace(',', ', ', setting('allowextload')) }}<br>
                Допустимые размеры картинок: от 100px
            </p>

            <button class="btn btn-primary">Сохранить</button>
        </form>
    </div>
@stop
