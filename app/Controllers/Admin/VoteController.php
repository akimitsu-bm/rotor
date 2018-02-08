<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Capsule\Manager as DB;

class VoteController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::MODER)) {
            abort('403', 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $total = Vote::query()->where('closed', 0)->count();
        $page = paginate(setting('allvotes'), $total);

        $votes = Vote::query()
            ->where('closed', 0)
            ->orderBy('created_at', 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->get();

        return view('admin/vote/index', compact('votes', 'page'));
    }

    /**
     * Редактирование голосования
     */
    public function edit($id)
    {
        $vote = Vote::query()->where('id', $id)->first();

        if (! $vote) {
            abort(404, 'Данного голосования не существует!');
        }

        if (Request::isMethod('post')) {

            $token   = check(Request::input('token'));
            $title   = check(Request::input('title'));
            $answers = check(Request::input('answers'));

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!');

            $validator->length($title, 5, 100, ['title' => 'Слишком длинный или короткий текст вопроса!']);
            $answers = array_unique(array_diff($answers, ['']));

            foreach ($answers as $answer) {
                if (utfStrlen($answer) > 50) {
                    $validator->addError(['answers' => 'Длина вариантов ответа не должна быть более 50 символов!']);
                    break;
                }
            }

            $validator->between(count($answers), 2, 10, ['answer' => 'Недостаточное количество вариантов ответов!']);

            if ($validator->isValid()) {

                $vote->update([
                    'title' => $title,
                ]);

                foreach ($answers as $answerId => $answer) {
                    $vote->answers()
                        ->updateOrCreate(['id' => $answerId], [
                            'answer' => $answer
                        ]);
                }

                setFlash('success', 'Голосование успешно изменено!');
                redirect('/admin/votes/edit/'.$vote->id);
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $getAnswers = $vote->answers->pluck('answer', 'id')->all();

        return view('admin/vote/edit', compact('vote', 'getAnswers'));
    }

    /**
     * Удаление голосования
     */
    public function delete($id)
    {
        $token = check(Request::input('token'));
        $vote  = Vote::query()->where('id', $id)->first();

        if (! $vote) {
            abort(404, 'Данного голосования не существует!');
        }

        if (! isAdmin(User::BOSS)) {
            abort(404, 'Доступ запрещен!');
        }

        if ($token === $_SESSION['token']) {

            DB::transaction(function () use ($vote) {
                $vote->delete();
                $vote->answers()->delete();
                $vote->polls()->delete();
            });

            setFlash('success', 'Голосование успешно удалено!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        redirect('/admin/votes');
    }

    /**
     * Открытие-закрытие голосования
     */
    public function close($id)
    {
        $token = check(Request::input('token'));
        $vote  = Vote::query()->where('id', $id)->first();

        if (! $vote) {
            abort(404, 'Данного голосования не существует!');
        }

        if ($token === $_SESSION['token']) {

            $closed = $vote->closed ^ 1;

            $vote->update([
                'closed' => $closed,
            ]);

            $type = $closed ? 'закрыто' : 'открыто';

            setFlash('success', 'Голосование успешно ' . $type . '!');
        } else {
            setFlash('danger', 'Ошибка! Неверный идентификатор сессии, повторите действие!');
        }

        if ($closed) {
            redirect('/admin/votes/history');
        }  else {
            redirect('/admin/votes');
        }
    }
}
