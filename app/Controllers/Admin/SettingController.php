<?php

namespace App\Controllers\Admin;

use App\Classes\Validator;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;

class SettingController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }
    }

    /**
     * Главная страница
     *
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function index(Request $request, Validator $validator): string
    {
        $act = check($request->input('act', 'main'));

        if (! \in_array($act, Setting::getActions(), true)) {
            abort(404, 'Недопустимая страница!');
        }

        if ($request->isMethod('post')) {

            $sets  = check($request->input('sets'));
            $mods  = check($request->input('mods'));
            $opt   = check($request->input('opt'));
            $token = check($request->input('token'));

            $validator->equal($token, $_SESSION['token'], ['msg' => trans('validator.token')])
                ->notEmpty($sets, ['sets' => 'Ошибка! Не переданы настройки сайта']);

            foreach ($sets as $name => $value) {
                if (empty($opt[$name]) || ! empty($sets[$name])) {
                    $validator->length($sets[$name], 1, 255, ['sets['.$name.']' => 'Поле '. check($name) .' обязательно для заполнения']);
                }
            }

            if ($validator->isValid()) {

                foreach ($sets as $name => $value) {
                    if (isset($mods[$name])) {
                        $value *= $mods[$name];
                    }

                    Setting::query()->where('name', $name)->update(['value' => $value]);
                }

                saveSettings();

                setFlash('success', 'Настройки сайта успешно изменены!');
                redirect('/admin/settings?act=' . $act);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $settings = Setting::query()->pluck('value', 'name')->all();

        return view('admin/settings/index', compact('settings', 'act'));
    }
}
