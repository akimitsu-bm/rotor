@extends('layout_simple')

@section('title')
    Вас забанили по IP!
@stop

@section('content')

    <h1>Вас забанили по IP!</h1>

    <h2>Вход на сайт запрещен!</h2>

    <b>Возможные причины:</b><br>
    1. Вы нарушили какие-либо правила сайта<br>
    2. Превышена допустимая частота запросов с одного IP<br>
    3. Вы всунулись туда, куда не положено<br>
    4. Возможно у вас просто одинаковые IP с нарушителем<br><br>
    <b>Что теперь делать?</b><br>
    Сменить браузер, войти с другого IP или с прокси-сервера и<br>
    Попросить администрацию разбанить ваш IP<br><br>
    Если нет такой возможности остается только ждать, список забаненых IP очищают раз в 3-4 дня<br><br>

    @if ($ban)
        <form method="post">
            {!! view('app/_captcha') !!}
            <button class="btn btn-primary">Подтвердить</button>
        </form>
    @endif
@stop
