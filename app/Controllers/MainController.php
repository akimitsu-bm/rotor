<?php

namespace App\Controllers;

use App\Models\Ban;
use Gregwar\Captcha\PhraseBuilder;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Http\Request;

class MainController
{
    /**
     * Бан по IP
     *
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function banip(Request $request): string
    {
        header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');

        $ban = Ban::query()
            ->where('ip', getIp())
            ->whereNull('user_id')
            ->first();

        if ($ban && $request->isMethod('post') && captchaVerify()) {

            $ban->delete();
            ipBan(true);

            setFlash('success', 'IP успешно разбанен!');
            redirect('/');
        }

        return view('pages/banip', compact('ban'));
    }

    /**
     * Защитная картинка
     *
     * @return void
     */
    public function captcha(): void
    {
        header('Content-type: image/jpeg');
        $phrase = new PhraseBuilder;
        $phrase = $phrase->build(setting('captcha_maxlength'), setting('captcha_symbols'));

        $builder = new CaptchaBuilder($phrase);
        $builder->setBackgroundColor(\mt_rand(200,255), \mt_rand(200,255), \mt_rand(200,255));
        $builder->setMaxOffset(setting('captcha_offset'));
        $builder->setMaxAngle(setting('captcha_angle'));
        $builder->setDistortion(setting('captcha_distortion'));
        $builder->setInterpolation(setting('captcha_interpolation'));
        $builder->build()->output();

        $_SESSION['protect'] = $builder->getPhrase();
    }

    /**
     * Быстрое изменение языка
     *
     * @param string  $lang
     * @param Request $request
     */
    public function language(string $lang, Request $request): void
    {
        $return    = $request->input('return');
        $languages = array_map('basename', glob(RESOURCES . '/lang/*', GLOB_ONLYDIR));

        if (preg_match('|^[a-z]+$|', $lang) && in_array($lang, $languages, true)) {
            if ($user = getUser()) {
                $user->update([
                    'language' => $lang,
                ]);
            } else {
                $_SESSION['language'] = $lang;
            }
        }

        redirect($return ?? '/');
    }

    /**
     * Returns the file with the translation text
     *
     * @param string  $lang
     * @return string
     */
    public function translation(string $lang): string
    {
        header('Content-type: application/javascript');
        $file = RESOURCES . '/lang/' . $lang . '/main.js';

        return file_exists($file) ? file_get_contents($file) : null;
    }
}
