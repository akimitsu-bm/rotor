<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validation;
use App\Models\Blog;
use App\Models\Guest;
use App\Models\Inbox;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Spam;
use App\Models\Wall;
use Illuminate\Database\Capsule\Manager as DB;

class SpamController extends AdminController
{
    /**
     * @var array
     */
    public $types;

    /**
     * @var array
     */
    public $total;

    public function __construct()
    {
        parent::__construct();

        if (! isAdmin([101, 102, 103])) {
            abort('403', 'Доступ запрещен!');
        }

        $this->types = [
            'post'  => Post::class,
            'guest' => Guest::class,
            'photo' => Photo::class,
            'blog'  => Blog::class,
            'inbox' => Inbox::class,
            'wall'  => Wall::class,
        ];

        $this->total = Spam::select(DB::raw("
            SUM(relate_type='".addslashes(Post::class)."') post,
            SUM(relate_type='".addslashes(Guest::class)."') guest,
            SUM(relate_type='".addslashes(Photo::class)."') photo,
            SUM(relate_type='".addslashes(Blog::class)."') blog,
            SUM(relate_type='".addslashes(Inbox::class)."') inbox,
            SUM(relate_type='".addslashes(Wall::class)."') wall
        "))->first();
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $type = check(Request::input('type'));
        $type = isset($this->types[$type]) ? $type : 'post';

        $page = paginate(setting('spamlist'),  $this->total['post']);

        $records = Spam::where('relate_type', $this->types[$type])
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit(setting('spamlist'))
            ->with('relate.user', 'user')
            ->get();

        $total = $this->total;

        return view('admin/spam/index', compact('records', 'page', 'total', 'type'));
    }

    /**
     * Удаление жалоб
     */
    public function delete()
    {
        $id    = abs(intval(Request::input('id')));
        $token = check(Request::input('token'));

        $validation = new Validation();
        $validation
            ->addRule('bool', Request::ajax(), 'Это не ajax запрос!')
            ->addRule('equal', [$token, $_SESSION['token']], 'Неверный идентификатор сессии, повторите действие!')
            ->addRule('not_empty', $id, ['Не выбрана запись для удаление!']);

        if ($validation->run()) {

            Spam::find($id)->delete();

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => current($validation->getErrors())
            ]);
        }
    }
}
