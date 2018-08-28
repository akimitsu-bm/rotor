<?php

namespace App\Controllers\User;

use App\Classes\Request;
use App\Controllers\BaseController;
use App\Models\User;

class SearchController extends BaseController
{
    /**
     * Главная страница
     *
     * @return string
     */
    public function index(): string
    {
        return view('users/search');
    }

    /**
     * Поиск пользователя
     *
     * @return string
     */
    public function search(): string
    {
        $find = check(Request::input('find'));

        if (utfStrlen($find) < 2 || utfStrlen($find) > 20) {
            setInput(Request::all());
            setFlash('danger', ['find' => 'Слишком короткий или длинный запрос, от 2 до 20 символов!']);
            redirect('/searchusers');
        }

        $users = User::query()
            ->where('login', 'like', '%' . $find . '%')
            ->orWhere('name', 'like', '%' . $find . '%')
            ->orderBy('point', 'desc')
            ->limit(setting('usersearch'))
            ->get();

        return view('users/search_result', compact('users'));
    }

    /**
     * Поиск по первой букве
     *
     * @param string $letter
     * @return string
     */
    public function sort($letter): string
    {
        $search = is_numeric($letter) ? "RLIKE '^[-0-9]'" : "LIKE '$letter%'";

        $total = User::query()
            ->whereRaw('login ' . $search)
            ->count();

        $page = paginate(setting('usersearch'), $total);

        $users = User::query()
            ->whereRaw('login ' . $search)
            ->orderBy('point', 'desc')
            ->offset($page->offset)
            ->limit($page->limit)
            ->get();

        return view('users/search_sort', compact('users', 'page'));
    }
}
