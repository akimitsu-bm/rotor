<?php


    switch ($action):



        ############################################################################################
        ##                                    Удаление раздела                                    ##
        ############################################################################################
        case 'delforum':

            $token = check($_GET['token']);

            if (isAdmin([101])) {
                if ($token == $_SESSION['token']) {

                    $forum = Forum::query()
                        ->where('id', $fid)
                        ->with('children')
                        ->first();

                    if ($forum) {
                        if ($forum->children->isEmpty()) {

                            $topic = Topic::query()->where('forum_id', $fid)->first();
                            if (! $topic) {

                                $forum->delete();

                                setFlash('success', 'Раздел успешно удален!');
                                redirect("/admin/forum");

                            } else {
                                showError('Ошибка! В данном разделе имеются темы!');
                            }
                        } else {
                            showError('Ошибка! Данный раздел имеет подфорумы!');
                        }
                    } else {
                        showError('Ошибка! Данного раздела не существует!');
                    }
                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }
            } else {
                showError('Ошибка! Удалять разделы могут только суперадмины!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                    Просмотр тем в разделе                              ##
        ############################################################################################
        case 'forum':

            $forum = Forum::with('parent')->find($fid);

            if (!$forum) {
                abort('default', 'Данного раздела не существует!');
            }

            $forum->children = Forum::query()
                ->where('parent_id', $forum->id)
                ->with('lastTopic.lastPost.user')
                ->get();

            echo '<a href="/admin/forum">Форум</a> / ';
            echo '<a href="/forum/'.$fid.'?page='.$page.'">Обзор раздела</a><br><br>';

            echo '<i class="fab fa-forumbee fa-lg text-muted"></i> <b>'.$forum['title'].'</b><hr>';

            $total = Topic::query()->where('forum_id', $fid)->count();

            if ($total > 0) {

                $page = paginate(setting('forumtem'), $total);

                $topics = Topic::query()
                    ->where('forum_id', $fid)
                    ->orderBy('locked', 'desc')
                    ->orderBy('updated_at', 'desc')
                    ->limit($page['limit'])
                    ->offset($page['offset'])
                    ->with('lastPost.user')
                    ->get();

                echo '<form action="/admin/forum?act=deltopics&amp;fid='.$fid.'&amp;page='.$page['current'].'&amp;token='.$_SESSION['token'].'" method="post">';

                echo '<div class="form">';
                echo '<input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked"> <b><label for="all">Отметить все</label></b>';
                echo '</div>';

                foreach ($topics as $topic) {
                    echo '<div class="b">';

                    echo '<i class="fa '.$topic->getIcon().'"></i> ';

                    echo '<b><a href="/admin/forum?act=topic&amp;tid='.$topic['id'].'">'.$topic['title'].'</a></b> ('.$topic['posts'].')<br>';

                    echo '<input type="checkbox" name="del[]" value="'.$topic['id'].'"> ';

                    echo '<a href="/admin/forum?act=edittopic&amp;tid='.$topic['id'].'&amp;page='.$topic['current'].'">Редактировать</a> / ';
                    echo '<a href="/admin/forum?act=movetopic&amp;tid='.$topic['id'].'&amp;page='.$topic['current'].'">Переместить</a></div>';

                    echo '<div>';
                    /*Forum::pagination($topic);*/

                    echo 'Сообщение: '.$topic->lastPost->user->login.' ('.dateFixed($topic->lastPost->created_at).')</div>';
                }

                echo '<br><input type="submit" value="Удалить выбранное"></form>';

                pagination($page);
            } else {
                if (empty($forum['closed'])) {
                    showError('Тем еще нет, будь первым!');
                }
            }

            if (!empty($forum['closed'])) {
                showError('В данном разделе запрещено создавать темы!');
            }

            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum">К форумам</a><br>';
        break;

        ############################################################################################
        ##                            Подготовка к редактированию темы                            ##
        ############################################################################################
        case 'edittopic':

            $topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$tid]);

            if (!empty($topics)) {

                echo '<b><big>Редактирование</big></b><br><br>';

                echo '<div class="form">';
                echo '<form action="/admin/forum?act=addedittopic&amp;fid='.$topics['forum_id'].'&amp;tid='.$tid.'&amp;page='.$page.'&amp;token='.$_SESSION['token'].'" method="post">';
                echo 'Тема: <br>';
                echo '<input type="text" name="title" size="50" maxlength="50" value="'.$topics['title'].'"><br>';
                echo 'Кураторы темы: <br>';
                echo '<input type="text" name="moderators" size="50" maxlength="100" value="'.$topics['moderators'].'"><br>';

                echo 'Объявление:<br>';
                echo '<textarea class="markItUp" cols="25" rows="5" name="note">'.$topics['note'].'</textarea><br>';

                echo 'Закрепить тему: ';
                $checked = ($topics['locked'] == 1) ? ' checked' : '';
                echo '<input name="locked" type="checkbox" value="1"'.$checked.'><br>';

                echo 'Закрыть тему: ';
                $checked = ($topics['closed'] == 1) ? ' checked' : '';
                echo '<input name="closed" type="checkbox" value="1"'.$checked.'><br>';

                echo '<br><input type="submit" value="Изменить"></form></div><br>';
            } else {
                showError('Ошибка! Данной темы не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=forum&amp;fid='.$topics['forum_id'].'&amp;page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                     Редактирование темы                                ##
        ############################################################################################
        case 'addedittopic':

            $token = check($_GET['token']);
            $title = check($_POST['title']);
            $moderators = check($_POST['moderators']);
            $note = check($_POST['note']);
            $locked = (empty($_POST['locked'])) ? 0 : 1;
            $closed = (empty($_POST['closed'])) ? 0 : 1;

            if ($token == $_SESSION['token']) {
                if (utfStrlen($title) >= 5 && utfStrlen($title) <= 50) {
                    if (utfStrlen($note) <= 250) {

                        $moderators = implode(',', preg_split('/[\s]*[,][\s]*/', $moderators));

                        DB::update("UPDATE `topics` SET `title`=?, `closed`=?, `locked`=?, `moderators`=?, `note`=? WHERE `id`=?;", [$title, $closed, $locked, $moderators, $note, $tid]);

                        if ($locked == 1) {
                            $page = 1;
                        }
                        setFlash('success', 'Тема успешно отредактирована!');
                        redirect("/admin/forum?act=forum&fid=$fid&page=$page");

                    } else {
                        showError('Ошибка! Слишком длинное объявление (Не более 250 символов)!');
                    }
                } else {
                    showError('Ошибка! Слишком длинное или короткое название темы!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=edittopic&amp;tid='.$tid.'&amp;page='.$page.'">Вернуться</a><br>';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum?act=forum&amp;fid='.$fid.'&amp;page='.$page.'">К темам</a><br>';
        break;

        ############################################################################################
        ##                               Подготовка к перемещению темы                            ##
        ############################################################################################
        case 'movetopic':

            $topic = Topic::query()->find($tid);

            if (!empty($topic)) {
                echo '<i class="fa fa-folder-open"></i> <b>'.$topic['title'].'</b> (Автор темы: '.$topic->user->login.')<br><br>';

                $forums = Forum::query()
                    ->where('parent_id', 0)
                    ->with('children')
                    ->orderBy('sort')
                    ->get();

                if (count($forums) > 1) {

                    echo '<div class="form">';
                    echo '<form action="/admin/forum?act=addmovetopic&amp;fid='.$topic['forum_id'].'&amp;tid='.$tid.'&amp;token='.$_SESSION['token'].'" method="post">';


                    echo '<label for="inputSection">Раздел</label>';
                    echo '<select class="form-control" id="inputSection" name="section">';

                    foreach ($forums as $forum) {
                        if ($topic['forum_id'] != $forum['id']) {
                            $disabled = ! empty($forum['closed']) ? ' disabled' : '';
                            echo '<option value="'.$forum['id'].'"'.$disabled.'>'.$forum['title'].'</option>';
                        }

                        if ($forum->children->isNotEmpty()) {
                            foreach($forum->children as $datasub) {
                                if ($topic['forum_id'] != $datasub['id']) {
                                    $disabled = ! empty($datasub['closed']) ? ' disabled' : '';
                                    echo '<option value="'.$datasub['id'].'"'.$disabled.'>– '.$datasub['title'].'</option>';
                                }
                            }
                        }
                    }

                    echo '</select>';

                    echo '<button class="btn btn-primary">Переместить</button></form></div><br>';
                } elseif(count($forums) == 1) {
                    showError('Нет разделов для перемещения!');
                }else {
                    showError('Разделы форума еще не созданы!');
                }
            } else {
                showError('Ошибка! Данной темы не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=forum&amp;fid='.$topic['forum_id'].'&amp;page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                    Перемещение темы                                    ##
        ############################################################################################
        case 'addmovetopic':

            $token = check($_GET['token']);
            $section = abs(intval($_POST['section']));

            if ($token == $_SESSION['token']) {
                $forums = DB::run() -> queryFetch("SELECT * FROM `forums` WHERE `id`=? LIMIT 1;", [$section]);
                $topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$tid]);

                if (!empty($forums)) {
                    if (empty($forums['closed'])) {
                        // Обновление номера раздела
                        DB::update("UPDATE `topics` SET `forum_id`=? WHERE `id`=?;", [$section, $tid]);

                        // Ищем последние темы в форумах для обновления списка последних тем
                        $oldlast = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `forum_id`=? ORDER BY `updated_at` DESC LIMIT 1;", [$topics['forum_id']]);
                        $newlast = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `forum_id`=? ORDER BY `updated_at` DESC LIMIT 1;", [$section]);

                        DB::update("UPDATE `forums` SET `last_topic_id`=? WHERE `id`=?;", [$oldlast['id'], $oldlast['forum_id']]);

                        DB::update("UPDATE `forums` SET `last_topic_id`=? WHERE `id`=?;", [$newlast['id'], $newlast['forum_id']]);

                        setFlash('success', 'Тема успешно перемещена!');
                        redirect("/admin/forum?act=forum&fid=$section");

                    } else {
                        showError('Ошибка! В закрытый раздел запрещено перемещать темы!');
                    }
                } else {
                    showError('Ошибка! Выбранного раздела не существует!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=movetopic&amp;tid='.$tid.'">Вернуться</a><br>';
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum?act=forum&amp;fid='.$fid.'">К темам</a><br>';
        break;

        ############################################################################################
        ##                                     Удаление тем                                       ##
        ############################################################################################
        case 'deltopics':

            $token = isset($_GET['token']) ? check($_GET['token']) : '';
            $del = intar(Request::input('del'));
            if ($token == $_SESSION['token']) {
                if (!empty($del)) {
                    $delId = implode(',', $del);

                    // ------ Удаление загруженных файлов -------//
                    foreach($del as $topicId){
                        removeDir(UPLOADS.'/forum/'.$topicId);
                        array_map('unlink', glob(UPLOADS.'/thumbnail/uploads_forum_'.$topicId.'_*.{jpg,jpeg,png,gif}', GLOB_BRACE));

                        // Выбирает files.id только если они есть в posts
                        $delPosts = Post::query()
                            ->where('topic_id', $topicId)
                            ->join('files', function($join){
                                $join->on('posts.id', '=', 'files.relate_id')
                                    ->where('files.relate_type', '=', Post::class);
                            })
                            ->pluck('files.id')
                            ->all();

                        if ($delPosts) {
                            $delFilesIds = implode(',', $delPosts);
                            DB::delete("DELETE FROM `files` WHERE `id` IN (" . $delFilesIds . ");");
                        }
                    }
                    // ------ Удаление загруженных файлов -------//

                    $votesIds = Vote::query()->whereIn('topic_id', $del)->pluck('id')->all();

                    if ($votesIds) {
                        Vote::query()->whereIn('id', $votesIds)->delete();
                        VoteAnswer::query()->whereIn('vote_id', $votesIds)->delete();
                        VotePoll::query()->whereIn('vote_id', $votesIds)->delete();
                    }

                    $deltopics = DB::run() -> exec("DELETE FROM `topics` WHERE `id` IN (".$delId.");");
                    $delposts = DB::run() -> exec("DELETE FROM `posts` WHERE `topic_id` IN (".$delId.");");

                    // Удаление закладок
                    DB::delete("DELETE FROM `bookmarks` WHERE `topic_id` IN (".$delId.");");

                    // Обновление счетчиков
                    DB::update("UPDATE `forums` SET `topics`=`topics`-?, `posts`=`posts`-? WHERE `id`=?;", [$deltopics, $delposts, $fid]);

                    // ------------------------------------------------------------//
                    $oldlast = DB::run() -> queryFetch("SELECT `t`.id, `f`.parent_id FROM `topics` t LEFT JOIN `forums` f ON `t`.`forum_id`=`f`.`id` WHERE `t`.`forum_id`=? ORDER BY `t`.`updated_at` DESC LIMIT 1;", [$fid]);

                    if (empty($oldlast['id'])) {
                        $oldlast['id'] = 0;
                    }

                    DB::update("UPDATE `forums` SET `last_topic_id`=? WHERE `id`=?;", [$oldlast['id'], $fid]);

                    // Обновление родительского форума
                    if (! empty($oldlast['parent_id'])) {
                        DB::update("UPDATE `forums` SET `last_topic_id`=? WHERE `id`=?;", [$oldlast['id'], $oldlast['parent_id']]);
                    }

                    setFlash('success', 'Выбранные темы успешно удалены!');
                    redirect("/admin/forum?act=forum&fid=$fid&page=$page");

                } else {
                    showError('Ошибка! Отсутствуют выбранные темы форума!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=forum&amp;fid='.$fid.'&amp;page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                  Закрытие - Закрепление темы                           ##
        ############################################################################################
        case 'acttopic':

            $token = check($_GET['token']);
            $do = check($_GET['do']);

            if ($token == $_SESSION['token']) {
                $topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$tid]);

                if (!empty($topics)) {
                    switch ($do):
                        case 'closed':
                            DB::update("UPDATE `topics` SET `closed`=? WHERE `id`=?;", [1, $tid]);

                            $vote = Vote::query()->where('topic_id', $tid)->first();
                            if ($vote) {
                                $vote->closed = 1;
                                $vote->save();

                                VotePoll::query()->where('vote_id', $vote['id'])->delete();
                            }

                            setFlash('success', 'Тема успешно закрыта!');
                            redirect("/admin/forum?act=topic&tid=$tid&page=$page");
                            break;

                        case 'open':
                            DB::update("UPDATE `topics` SET `closed`=? WHERE `id`=?;", [0, $tid]);

                            $vote = Vote::query()->where('topic_id', $tid)->first();
                            if ($vote) {
                                $vote->closed = 0;
                                $vote->save();
                            }

                            setFlash('success', 'Тема успешно открыта!');
                            redirect("/admin/forum?act=topic&tid=$tid&page=$page");
                            break;

                        case 'locked':
                            DB::update("UPDATE `topics` SET `locked`=? WHERE `id`=?;", [1, $tid]);
                            setFlash('success', 'Тема успешно закреплена!');
                            redirect("/admin/forum?act=topic&tid=$tid&page=$page");
                            break;

                        case 'unlocked':
                            DB::update("UPDATE `topics` SET `locked`=? WHERE `id`=?;", [0, $tid]);
                            setFlash('success', 'Тема успешно откреплена!');
                            redirect("/admin/forum?act=topic&tid=$tid&page=$page");
                            break;

                        default:
                            showError('Ошибка! Не выбрано действие для темы!');
                            endswitch;
                    } else {
                        showError('Ошибка! Данной темы не существует!');
                    }
                } else {
                    showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
                }

                echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum">К форумам</a><br>';
            break;

        ############################################################################################
        ##                                     Просмотр сообщений                                 ##
        ############################################################################################
        case 'topic':
            if (!empty($tid)) {
                $topic = DB::run() -> queryFetch("SELECT `t`.*, `f`.`title` forum_title, `f`.parent_id FROM `topics` t LEFT JOIN `forums` f ON `t`.`forum_id`=`f`.`id` WHERE t.`id`=? LIMIT 1;", [$tid]);

                if (!empty($topic)) {
                    echo '<a href="/admin/forum">Форум</a> / ';

                    if (!empty($topic['parent'])) {
                        $forums = DB::run() -> queryFetch("SELECT `id`, `title` FROM `forums` WHERE `id`=? LIMIT 1;", [$topic['parent']]);
                        echo '<a href="/admin/forum?fid='.$forums['id'].'">'.$forums['title'].'</a> / ';
                    }

                    echo '<a href="/admin/forum?act=forum&amp;fid='.$topic['forum_id'].'">'.$topic['forum_title'].'</a> / ';
                    echo '<a href="/topic/'.$tid.'?page='.$page.'">Обзор темы</a><br><br>';

                    echo '<i class="fab fa-forumbee fa-lg text-muted"></i> <b>'.$topic['title'].'</b>';

                    if (!empty($topic['moderators'])) {
                        $moderators = User::query()->whereIn('id', explode(',', $topic['moderators']))->get();

                        echo '<br>Кураторы темы: ';
                        foreach ($moderators as $mkey => $mval) {
                            $comma = (empty($mkey)) ? '' : ', ';
                            echo $comma . profile($mval);
                        }
                    }

                    if (!empty($topic['note'])){
                        echo '<div class="info">'.bbCode($topic['note']).'</div>';
                    }

                    echo '<hr>';

                    if (empty($topic['closed'])) {
                        echo '<a href="/admin/forum?act=acttopic&amp;do=closed&amp;tid='.$tid.'&amp;page='.$page.'&amp;token='.$_SESSION['token'].'">Закрыть</a> / ';
                    } else {
                        echo '<a href="/admin/forum?act=acttopic&amp;do=open&amp;tid='.$tid.'&amp;page='.$page.'&amp;token='.$_SESSION['token'].'">Открыть</a> / ';
                    }

                    if (empty($topic['locked'])) {
                        echo '<a href="/admin/forum?act=acttopic&amp;do=locked&amp;tid='.$tid.'&amp;page='.$page.'&amp;token='.$_SESSION['token'].'">Закрепить</a> / ';
                    } else {
                        echo '<a href="/admin/forum?act=acttopic&amp;do=unlocked&amp;tid='.$tid.'&amp;page='.$page.'&amp;token='.$_SESSION['token'].'">Открепить</a> / ';
                    }

                    echo '<a href="/admin/forum?act=edittopic&amp;tid='.$tid.'&amp;page='.$page.'">Изменить</a> / ';
                    echo '<a href="/admin/forum?act=movetopic&amp;tid='.$tid.'">Переместить</a> / ';
                    echo '<a href="/admin/forum?act=deltopics&amp;fid='.$topic['id'].'&amp;del='.$tid.'&amp;token='.$_SESSION['token'].'" onclick="return confirm(\'Вы действительно хотите удалить данную тему?\')">Удалить</a><br>';

                    $total = DB::run() -> querySingle("SELECT count(*) FROM `posts` WHERE `topic_id`=?;", [$tid]);

                    if ($total > 0) {
                        $page = paginate(setting('forumpost'), $total);



                        $posts = Post::select('posts.*', 'pollings.vote')
                            ->where('topic_id', $tid)
                            ->leftJoin ('pollings', function($join) {
                                $join->on('posts.id', '=', 'pollings.relate_id')
                                    ->where('pollings.relate_type', '=', Post::class);
                            })
                            ->with('files', 'user', 'editUser')
                            ->offset($page['offset'])
                            ->limit($page['limit'])
                            ->orderBy('created_at', 'asc')
                            ->get();

                        echo '<form action="/admin/forum?act=delposts&amp;tid='.$tid.'&amp;page='.$page['current'].'&amp;token='.$_SESSION['token'].'" method="post">';

                        echo '<div align="right" class="form">';
                        echo '<b><label for="all">Отметить все</label></b> <input type="checkbox" id="all" onchange="var o=this.form.elements;for(var i=0;i&lt;o.length;i++)o[i].checked=this.checked">&nbsp;';
                        echo '</div>';

                        foreach ($posts as $key=>$data){
                            $num = ($page['offset'] + $key + 1);

                            echo '<div class="b">';

                            echo '<div class="img">'.userAvatar($data->user).'</div>';
                            echo '<span class="imgright"><a href="/admin/forum?act=editpost&amp;tid='.$tid.'&amp;pid='.$data['id'].'&amp;page='.$page['current'].'">Ред.</a> <input type="checkbox" name="del[]" value="'.$data['id'].'"></span>';


                            echo $num.'. <b>'.profile($data['user']).'</b>  <small>('.dateFixed($data['created_at']).')</small><br>';
                            echo userStatus($data->user).' '.userOnline($data->user).'</div>';

                            echo '<div>'.bbCode($data['text']).'<br>';

                            // -- Прикрепленные файлы -- //
                            if ($data->files->isNotEmpty()) {
                                echo '<div class="hiding"><i class="fa fa-paperclip"></i> <b>Прикрепленные файлы:</b><br>';
                                foreach ($data->files as $file){
                                    $ext = getExtension($file['hash']);
                                    echo icons($ext).' ';

                                    echo '<a href="/uploads/forum/'.$data['topic_id'].'/'.$file['hash'].'" target="_blank">'.$file['name'].'</a> ('.formatSize($file['size']).')<br>';
                                }
                                echo '</div>';

                            }
                            // --------------------------//

                            if (!empty($data['updated_at'])) {
                                echo '<small><i class="fa fa-exclamation-circle text-danger"></i> Отредактировано: '.$data->editUser->login.' ('.dateFixed($data['updated_at']).')</small><br>';
                            }

                            echo '<span class="data">('.$data['brow'].', '.$data['ip'].')</span></div>';
                        }

                        echo '<span class="imgright"><input type="submit" value="Удалить выбранное"></span></form>';

                        pagination($page);

                    } else {
                        showError('Сообщений еще нет, будь первым!');
                    }

                    if (empty($topic['closed'])) {
                        echo '<div class="form" id="form">';
                        echo '<form action="/topic/create/'.$tid.'" method="post" enctype="multipart/form-data">';
                        echo '<input type="hidden" name="token" value="'.$_SESSION['token'].'">';

                        echo 'Сообщение:<br>';
                        echo '<textarea class="markItUp" cols="25" rows="5" name="msg"></textarea><br>';

                        echo '<div class="js-attach-form" style="display: none;">
                            Прикрепить файл:<br><input type="file" name="file"><br>
                            <div class="info">
                                Максимальный вес файла: <b>'.round(setting('forumloadsize')/1024).'</b> Kb<br>
                                Допустимые расширения: '.str_replace(',', ', ', setting('forumextload')).'
                            </div><br>
                        </div>';

                        echo '<span class="imgright js-attach-button"><a href="#" onclick="return showAttachForm();">Загрузить файл</a></span>';

                        echo '<input type="submit" value="Написать">';
                        echo '</form></div><br>';

                    } else {
                        showError('Данная тема закрыта для обсуждения!');
                    }
                } else {
                    showError('Ошибка! Данной темы не существует!');
                }
            } else {
                showError('Ошибка! Не выбрана тема!');
            }
            echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/forum">К форумам</a><br>';
        break;

        ############################################################################################
        ##                                    Удаление сообщений                                  ##
        ############################################################################################
        case 'delposts':

            $token = check($_GET['token']);
            $del = intar(Request::input('del'));

            if ($token == $_SESSION['token']) {
                if (!empty($del)) {
                    $topics = DB::run() -> queryFetch("SELECT * FROM `topics` WHERE `id`=? LIMIT 1;", [$tid]);
                    $del = implode(',', $del);

                    // ------ Удаление загруженных файлов -------//
                    $queryfiles = DB::select("SELECT `hash` FROM `files_forum` WHERE `post_id` IN (".$del.");");
                    $files = $queryfiles->fetchAll(PDO::FETCH_COLUMN);

                    if (!empty($files)){
                        foreach ($files as $file){
                            deleteImage('uploads/forum/', $topics['id'].'/'.$file);
                        }
                    }

                    DB::delete("DELETE FROM `files_forum` WHERE `post_id` IN (".$del.");");
                    // ------ Удаление загруженных файлов -------//

                    $delposts = DB::run() -> exec("DELETE FROM `posts` WHERE `id` IN (".$del.") AND `topic_id`=".$tid.";");
                    DB::update("UPDATE `topics` SET `posts`=`posts`-? WHERE `id`=?;", [$delposts, $tid]);
                    DB::update("UPDATE `forums` SET `posts`=`posts`-? WHERE `id`=?;", [$delposts, $topics['forum_id']]);

                    setFlash('success', 'Выбранные сообщения успешно удалены!');
                    redirect("/admin/forum?act=topic&tid=$tid&page=$page");

                } else {
                    showError('Ошибка! Отсутствуют выбранные сообщения!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=topic&amp;tid='.$tid.'&amp;page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                   Подготовка к редактированию                          ##
        ############################################################################################
        case 'editpost':

            $pid = abs(intval($_GET['pid']));

            $post = Post::query()->where('id', $pid)->with('files')->first();

            if (!empty($post)) {

                echo '<i class="fa fa-pencil-alt"></i> <b>'.profile($post->user).'</b> <small>('.dateFixed($post['created_at']).')</small><br><br>';

                echo '<div class="form" id="form">';
                echo '<form action="/admin/forum?act=addeditpost&amp;tid='.$post['topic_id'].'&amp;pid='.$pid.'&amp;page='.$page.'&amp;token='.$_SESSION['token'].'" method="post">';
                echo 'Редактирование сообщения:<br>';
                echo '<textarea class="markItUp" cols="25" rows="10" name="msg">'.$post['text'].'</textarea><br>';

                if ($post->files->isNotEmpty()){
                    echo '<i class="fa fa-paperclip"></i> <b>Удаление файлов:</b><br>';
                    foreach ($post->files as $file){
                        echo '<input type="checkbox" name="delfile[]" value="'.$file['id'].'"> ';
                        echo '<a href="/uploads/forum/'.$post['topic_id'].'/'.$file['hash'].'" target="_blank">'.$file['name'].'</a> ('.formatSize($file['size']).')<br>';
                    }
                    echo '<br>';
                }

                echo '<input value="Редактировать" name="do" type="submit"></form></div><br>';
            } else {
                showError('Ошибка! Данного сообщения не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=topic&amp;tid='.$tid.'&amp;page='.$page.'">Вернуться</a><br>';
        break;

        ############################################################################################
        ##                                    Редактирование сообщения                            ##
        ############################################################################################
        case 'addeditpost':

            $pid     = int(Request::input('pid'));
            $token   = check(Request::input('token'));
            $msg     = check(Request::input('msg'));
            $delfile = intar(Request::input('delfile'));

            if ($token == $_SESSION['token']) {
                if (utfStrlen($msg) >= 5 && utfStrlen($msg) <= setting('forumtextlength')) {
                    $post = DB::run() -> queryFetch("SELECT * FROM `posts` WHERE `id`=? LIMIT 1;", [$pid]);
                    if (!empty($post)) {

                        DB::update("UPDATE `posts` SET `text`=?, `edit_user_id`=?, `updated_at`=? WHERE `id`=?;", [$msg, getUser('id'), SITETIME, $pid]);

                        // ------ Удаление загруженных файлов -------//
                        if ($delfile) {
                            $del = implode(',', $delfile);
                            $queryfiles = DB::select("SELECT * FROM `files` WHERE `relate_id`=? AND relate_type=? AND `id` IN (".$del.");", [$pid, Post::class]);
                            $files = $queryfiles->fetchAll();

                            if (!empty($files)){
                                foreach ($files as $file){
                                    deleteImage('uploads/forum/', $post['topic_id'].'/'.$file['hash']);
                                }
                                DB::delete("DELETE FROM `files` WHERE `relate_id`=? AND relate_type=? AND `id` IN (".$del.");", [$pid, Post::class]);
                            }
                        }
                        // ------ Удаление загруженных файлов -------//


                        setFlash('success', 'Сообщение успешно отредактировано!');
                        redirect("/admin/forum?act=topic&tid=$tid&page=$page");

                    } else {
                        showError('Ошибка! Данного сообщения не существует!');
                    }
                } else {
                    showError('Ошибка! Слишком длинное или короткое сообщение!');
                }
            } else {
                showError('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/forum?act=editpost&amp;tid='.$tid.'&amp;pid='.$pid.'&amp;page='.$page.'">Вернуться</a><br>';
        break;

    endswitch;
