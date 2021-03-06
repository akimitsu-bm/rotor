@extends('layout')

@section('title')
    {{ trans('news.edit_title') }}
@stop

@section('breadcrumb')
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="/news">{{ trans('news.site_news') }}</a></li>
            <li class="breadcrumb-item"><a href="/news/{{ $news->id }}">{{ $news->title }}</a></li>
            <li class="breadcrumb-item"><a href="/news/comments/{{ $news->id }}">{{ trans('main.comments') }}</a></li>
            <li class="breadcrumb-item active">{{ trans('news.edit_title') }}</li>
        </ol>
    </nav>
@stop

@section('content')
    <i class="fa fa-pencil-alt"></i> <b>{{ $comment->user->login }}</b> <small>({{ dateFixed($comment->created_at) }})</small><br><br>

    <div class="form">
        <form method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

            <div class="form-group{{ hasError('msg') }}">
                <label for="msg">{{ trans('main.message') }}:</label>
                <textarea class="form-control markItUp" id="msg" rows="5" maxlength="{{ setting('comment_length') }}" name="msg" placeholder="{{ trans('main.message') }}" required>{{ getInput('msg', $comment->text) }}</textarea>
                <span class="js-textarea-counter"></span>
                {!! textError('msg') !!}
            </div>

            <button class="btn btn-success">{{ trans('main.edit') }}</button>
        </form>
    </div>
@stop
