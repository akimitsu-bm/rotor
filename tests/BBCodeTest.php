<?php

use PHPUnit\Framework\TestCase;

class BBCodeTest extends TestCase
{
    /**
     * Тестирует подсветку текста
     */
    public function testCode(): void
    {
        $text      = '[code]<?php var_dump([1,2,4]);[/code]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<pre class="prettyprint linenums pre-scrollable"><?php var_dump(&#91;1,2,4]);</pre>');
    }

    /**
     * Тестирует жирность текста
     */
    public function testBold(): void
    {
        $text      = '[b]Привет[/b]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<strong>Привет</strong>');
    }

    /**
     * Тестирует наклон текста
     */
    public function testItalic(): void
    {
        $text      = '[i]Привет[/i]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<em>Привет</em>');
    }

    /**
     * Тестирует подчеркивание текста
     */
    public function testUnderLine(): void
    {
        $text      = '[u]Привет[/u]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<u>Привет</u>');
    }

    /**
     * Тестирует зачеркивание текста
     */
    public function testLineThrough(): void
    {
        $text      = '[s]Привет[/s]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<del>Привет</del>');
    }

    /**
     * Тестирует размер текста
     */
    public function testFontSize(): void
    {
        $text      = '[size=5]Привет[/size]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<span style="font-size:x-large">Привет</span>');
    }

    /**
     * Тестирует цвет текста
     */
    public function testFontColor(): void
    {
        $text      = '[color=#ff0000]Привет[/color]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<span style="color:#ff0000">Привет</span>');
    }

    /**
     * Тестирует вложенность цветов текста
     */
    public function testIterateFontColor(): void
    {
        $text      = '[color=#ff0000]П[color=#00ff00]р[color=#0000ff]и[/color][color=#00ff00]в[/color][/color]ет[/color]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<span style="color:#ff0000">П<span style="color:#00ff00">р<span style="color:#0000ff">и</span><span style="color:#00ff00">в</span></span>ет</span>');
    }

    /**
     * Тестирует центрирование текста
     */
    public function testCenter(): void
    {
        $text      = '[center]Привет[/center]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<div style="text-align:center;">Привет</div>');
    }

    /**
     * Тестирует цитирование текста
     */
    public function testQuote(): void
    {
        $text      = '[quote]Привет[/quote]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<blockquote>Привет</blockquote>');
    }

    /**
     * Тестирует цитирование текста с именем
     */
    public function testNamedQuote(): void
    {
        $text      = '[quote=Имя]Привет[/quote]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<blockquote>Привет<small>Имя</small></blockquote>');
    }

    /**
     * Тестирует ссылку в тексте
     */
    public function testHttp(): void
    {
        $text      = 'http://сайт.рф http://сайт.рф/http://сайт.рф:80';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<a href="http://сайт.рф" target="_blank" rel="nofollow">http://сайт.рф</a> <a href="http://сайт.рф/http://сайт.рф:80" target="_blank" rel="nofollow">http://сайт.рф/http://сайт.рф:80</a>');
    }

    /**
     * Тестирует ссылку в тексте совпадающую с именем сайта
     */
    public function testHttpNotTarget(): void
    {
        $text      = 'http://rotor.ll';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<a href="//rotor.ll">http://rotor.ll</a>');
    }

    /**
     * Тестирует ссылку в тексте совпадающую с именем сайта
     */
    public function testHttpsComplex(): void
    {
        $text      = 'https://rotor.ll/dir/index.php?name=name&name2=name2#anchor';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<a href="//rotor.ll/dir/index.php?name=name&name2=name2#anchor">https://rotor.ll/dir/index.php?name=name&name2=name2#anchor</a>');
    }

    /**
     * Тестирует ссылку в тексте
     */
    public function testLink(): void
    {
        $text      = '[url]https://rotor.ll[/url]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<a href="//rotor.ll">https://rotor.ll</a>');
    }

    /**
     * Тестирует именованную ссылку в тексте
     */
    public function testNamedLink(): void
    {
        $text      = '[url=http://rotor.ll/dir/index.php?name=name&name2=name2#anchor]Сайт[/url] [url=https://site.com/http://site.net:80/]Sitename[/url]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<a href="//rotor.ll/dir/index.php?name=name&name2=name2#anchor">Сайт</a> <a href="https://site.com/http://site.net:80/" target="_blank" rel="nofollow">Sitename</a>');
    }

    /**
     * Тестирует картинку в тексте
     */
    public function testImage(): void
    {
        $text      = '[img]http://rotor.ll/assets/images/img/logo.png[/img]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<img src="http://rotor.ll/assets/images/img/logo.png" class="img-fluid" alt="image">');
    }

    /**
     * Тестирует сортированный список в тексте
     */
    public function testOrderedList(): void
    {
        $text      = '[list=1]Список'.PHP_EOL.'список2[/list]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<ol><li>Список</li><li>список2</li></ol>');
    }

    /**
     * Тестирует несортированный список в тексте
     */
    public function testUnorderedList(): void
    {
        $text      = '[list]Список[/list]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<ul><li>Список</li></ul>');
    }

    /**
     * Тестирует спойлер в тексте
     */
    public function testSpoiler(): void
    {
        $text      = '[spoiler]Спойлер[/spoiler]';
        $parseText = bbCode($text);
        $parseText = trim(preg_replace('/\s\s+/', '', $parseText));

        $this->assertEquals($parseText, '<div class="spoiler"><b class="spoiler-title">Развернуть для просмотра</b><div class="spoiler-text" style="display: none;">Спойлер</div></div>');
    }

    /**
     * Тестирует именованный спойлер в тексте
     */
    public function testShortSpoiler(): void
    {
        $text      = '[spoiler=Открыть]Спойлер[/spoiler]';
        $parseText = bbCode($text);
        $parseText = trim(preg_replace('/\s\s+/', '', $parseText));

        $this->assertEquals($parseText, '<div class="spoiler"><b class="spoiler-title">Открыть</b><div class="spoiler-text" style="display: none;">Спойлер</div></div>');
    }

    /**
     * Тестирует скрытый текст
     */
    public function testHide(): void
    {
        $text      = '[hide]Скрытый текст[/hide]';
        $parseText = bbCode($text);
        $parseText = trim(preg_replace('/\s\s+/', '', $parseText));

        $this->assertEquals($parseText, '<div class="hiding"><span class="font-weight-bold">Скрытый контент:</span> Для просмотра необходимо авторизоваться!</div>');
    }

    /**
     * Тестирует видео в тексте
     */
    public function testYoutube(): void
    {
        $text      = '[youtube]https://www.youtube.com/watch?v=85bkCmaOh4o[/youtube]';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="//www.youtube.com/embed/85bkCmaOh4o" allowfullscreen></iframe></div>');
    }

    /**
     * Тестирует стикеры в тексте
     */
    public function testSticker(): void
    {
        $text      = 'Привет :D :hello';
        $parseText = bbCode($text);

        $this->assertEquals($parseText, 'Привет <img src="/uploads/stickers/D.gif" alt="D.gif"> <img src="/uploads/stickers/hello.gif" alt="hello.gif">');
    }

    /**
     * Тестирует очистку тегов в тексте
     */
    public function testClear(): void
    {
        $text      = '[center][b]Привет[/b] [i]Привет[/i][/center]';
        $parseText = bbCode($text, false);

        $this->assertEquals($parseText, 'Привет Привет');
    }
}
