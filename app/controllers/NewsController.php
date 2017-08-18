<?php

class NewsController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        $total = News::count();
        $page = App::paginate(Setting::get('postnews'), $total);

        $news = News::orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('user')
            ->get();

        App::view('news/index', compact('news', 'page'));
    }

    /**
     * Вывод новости
     */
    public function view($id)
    {
        $news = News::find($id);

        if (! $news) {
            App::abort(404, 'Новость не существует, возможно она была удалена!');
        }

        $news['text'] = str_replace('[cut]', '', $news['text']);

        $comments = Comment::where('relate_type', News::class)
            ->where('relate_id', $id)
            ->limit(5)
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->get();

        $comments = $comments->reverse();

        App::view('news/view', compact('news', 'comments'));
    }

    /**
     * Комментарии
     */
    public function comments($id)
    {
        $news = News::find($id);

        if (! $news) {
            App::abort(404, 'Новость не существует, возможно она была удалена!');
        }

        $total = Comment::where('relate_type', News::class)
            ->where('relate_id', $id)
            ->count();

        $page = App::paginate(Setting::get('postnews'), $total);

        $comments = Comment::where('relate_type', News::class)
            ->where('relate_id', $id)
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->orderBy('created_at')
            ->with('user')
            ->get();

        App::view('news/comments', compact('news', 'comments', 'page'));
    }

    /**
     * Добавление комментариев
     */
    public function create($id)
    {
        $msg = check(Request::input('msg'));
        $token = check(Request::input('token'));
        $page = abs(intval(Request::input('page', 1)));

        if (is_user()) {

            $data = DB::run()->queryFetch("SELECT * FROM `news` WHERE `id`=? LIMIT 1;", [$id]);

            $validation = new Validation();

            $validation->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('equal', [Flood::isFlood(App::getUserId()), true], 'Антифлуд! Разрешается комментировать раз в ' . Flood::getPeriod() . ' сек!')
                ->addRule('not_empty', $data, 'Выбранной новости не существует, возможно она было удалена!')
                ->addRule('string', $msg, 'Слишком длинный или короткий комментарий!', true, 5, 1000)
                ->addRule('empty', $data['closed'], 'Комментирование данной новости запрещено!');

            if ($validation->run()) {

                $msg = antimat($msg);

                DB::run()->query("INSERT INTO `comments` (relate_type, `relate_id`, `text`, `user_id`, `created_at`, `ip`, `brow`) VALUES (?, ?, ?, ?, ?, ?, ?);", ['news', $id, $msg, App::getUserId(), SITETIME, App::getClientIp(), App::getUserAgent()]);

                DB::run()->query("DELETE FROM `comments` WHERE relate_type=? AND `relate_id`=? AND `created_at` < (SELECT MIN(`created_at`) FROM (SELECT `created_at` FROM `comments` WHERE relate_type=? AND `relate_id`=? ORDER BY `created_at` DESC LIMIT " . Setting::get('maxkommnews') . ") AS del);", ['news', $id, 'news', $id]);

                DB::run()->query("UPDATE `news` SET `comments`=`comments`+1 WHERE `id`=?;", [$id]);
                DB::run()->query("UPDATE `users` SET `allcomments`=`allcomments`+1, `point`=`point`+1, `money`=`money`+5 WHERE `login`=?", [App::getUsername()]);

                App::setFlash('success', 'Комментарий успешно добавлен!');

                if (isset($_GET['read'])) {
                    App::redirect('/news/' . $id);
                }

                App::redirect('/news/' . $id . '/end');

            } else {
                show_error($validation->getErrors());
            }
        } else {
            show_login('Вы не авторизованы, чтобы добавить сообщение, необходимо');
        }

        echo '<i class="fa fa-arrow-circle-up"></i> <a href="/news/' . $id . '/comments?page=' . $page . '">Вернуться</a><br>';
        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/news">К новостям</a><br>';
    }

    /**
     * Редактирование комментария
     */
    public function editcomment($nid, $id)
    {
        $page = abs(intval(Request::input('page', 1)));

        if (!is_user()) {
            App::abort(403, 'Для редактирования комментариев небходимо авторизоваться!');
        }

        $comment = Comment::where('relate_type', News::class)
            ->where('comments.id', $id)
            ->where('comments.user_id', App::getUserId())
            ->first();

        if (! $comment) {
            App::abort('default', 'Комментарий удален или вы не автор этого комментария!');
        }

        if ($comment['created_at'] + 600 < SITETIME) {
            App::abort('default', 'Редактирование невозможно, прошло более 10 минут!');
        }

        if (Request::isMethod('post')) {
            $msg   = check(Request::input('msg'));
            $token = check(Request::input('token'));

            $validation = new Validation();
            $validation
                ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
                ->addRule('string', $msg, ['msg' => 'Слишком длинный или короткий комментарий!'], true, 5, 1000);

            if ($validation->run()) {
                $msg = antimat($msg);

                $comment->update([
                    'text' => $msg,
                ]);

                App::setFlash('success', 'Комментарий успешно отредактирован!');
                App::redirect('/news/' . $nid . '/comments?page=' . $page);
            } else {
                App::setInput(Request::all());
                App::setFlash('danger', $validation->getErrors());
            }
        }
        App::view('news/editcomment', compact('comment', 'page'));
    }

    /**
     * Переадресация на последнюю страницу
     */
    public function end($id)
    {
        $news = News::find($id);

        if (empty($news)) {
            App::abort(404, 'Ошибка! Данной новости не существует!');
        }

        $end = ceil($news['comments'] / Setting::get('postnews'));
        App::redirect('/news/' . $id . '/comments?page=' . $end);
    }

    /**
     * Rss новостей
     */
    public function rss()
    {
        $newses = News::orderBy('created_at', 'desc')->limit(15)->get();

        if ($newses->isEmpty()) {
            App::abort('default', 'Новости не найдены!');
        }

        App::view('news/rss', compact('newses'));
    }
}
