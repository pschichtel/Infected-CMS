<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');
    if (!user::loggedIn())
    {
        user::backToLogin();
    }

    define('PAGELEN', 20);
    $lang = new Lang($info->modul);
    $design = new Design();
    $design->printBegin();

    if (!is_null($info->modulParams('add')))
    {
        user::hasRight('news_add') or headerTo($info->modulSelf . '&status=access_denied');
        $msg = '';
        $author = '';
        $title = '';
        $text = '';
        $shorten = '';
        $comments = 'checked="checked" ';

        if (isset($_POST['post']))
        {
            if (trim($_POST['title']) === '')
            {
                $msg .= ' - ' . $lang->err_title . '<br />';
            }
            elseif (mb_strlen(trim($_POST['title']), 'UTF-8') > 70)
            {
                $msg .= ' - ' . $lang->err_title2long . '<br />';
            }
            if (trim($_POST['text']) === '')
            {
                $msg .= ' - ' . $lang->err_text . '<br />';
            }

            if ($msg == '')
            {
                $author = htmlspecialchars($_SESSION['ci_adminname']);
                $title = htmlspecialchars(Text::simpleChunk($_POST['title']));
                $text = &$_POST['text'];
                $ptext = Text::parse($_POST['text']);
                $shorten = (isset($_POST['shorten']) ? 1 : 0);
                $comments = (isset($_POST['comments']) ? 1 : 0);

                $msg = $lang->added;

                $query = 'INSERT INTO `PREFIX_news` (`author`,`title`,`rawtext`,`parsedtext`,`datetime`,`shorten`,`comments_allowed`) VALUES (?,?,?,?,NOW(),?,?)';
                $types = 'ssssii';
                $param_arr = array($author, $title, $text, $ptext, $shorten, $comments);
                $db->PushData($query, $types, $param_arr);
                headerTo($info->modulSelf . '&status=added');
            }
            else
            {
                $msg = $lang->err . ':<br />' . $msg;
                $title = ($_POST['title'] !== '' ? $_POST['title'] : '');
                $text = ($_POST['text'] !== '' ? $_POST['text'] : '');
                $shorten = (isset($_POST['shorten']) ? 'checked="checked" ' : '');
                $comments = (isset($_POST['comments']) ? 'checked="checked" ' : '');
            }
        }
        
        $tpl = new Template('news_new', $lang);
        $params = array(
            'MSG' => $msg,
            'AUTHOR' => htmlspecialchars($_SESSION['ci_adminname']),
            'TITLE' => $title,
            'TEXT' => htmlspecialchars($text),
            'THIS' => $info->modulSelf,
            'SHORTEN' => $shorten,
            'COMMENTS' => $comments
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    elseif (!is_null($info->modulParams('edit')))
    {
        user::hasRight('news_edit') or headerTo($info->modulSelf . '&status=access_denied');
        $msg = '';
        $author = '';
        $title = '';
        $text = '';
        $shorten = '';
        $comments = '';

        if (isset($_POST['edit']))
        {
            if (trim($_POST['title']) === '')
            {
                $msg .= ' - ' . $lang->err_title . '<br />';
            }
            elseif (mb_strlen(trim($_POST['title']), 'UTF-8') > 70)
            {
                $msg .= ' - ' . $lang->err_title2long . '<br />';
            }
            if (trim($_POST['text']) === '')
            {
                $msg .= ' - ' . $lang->err_text . '<br />';
            }

            if ($msg  === '')
            {
                $title = htmlspecialchars(Text::simpleChunk($_POST['title']));
                $text = &$_POST['text'];
                $ptext = Text::parse($_POST['text']);
                $shorten = (isset($_POST['shorten']) ? 1 : 0);
                $comments = (isset($_POST['comments']) ? 1 : 0);

                $query = 'UPDATE `PREFIX_news` SET `title`=?,`rawtext`=? ,`parsedtext`=?,`shorten`=?,`comments_allowed`=? WHERE `id`=? LIMIT 1';
                $types = 'sssiii';
                $param_arr = array($title, $text, $ptext, $shorten, $comments, (int) $info->modulParams('edit'));

                $db->PushData($query, $types, $param_arr);
                headerTo($info->modulSelf . '&status=updated&page=' . (int) $info->modulParams('page'));
            }
            else
            {
                $msg = $lang->err . ':<br />' . $msg;
                $title = (trim($_POST['title']) !== '' ? trim($_POST['title']) : '');
                $text = (trim($_POST['text']) !== '' ? trim($_POST['text']) : '');
                $shorten = (isset($_POST['shorten']) ? 'checked="checked" ' : '');
                $comments = (isset($_POST['comments']) ? 'checked="checked" ' : '');
            }
        }

        $tpl = new Template('news_edit', $lang);
        $query = 'SELECT `id`,`author`,`title`,`rawtext`,`shorten`,`comments_allowed` FROM `PREFIX_news` WHERE `id`=' . (int) $info->modulParams('edit');
        $result = $db->getData($query);
        if ($db->affected_rows == 0)
        {
            headerTo($info->modulSelf);
        }
        $result = &$result[0];

        if (!isset($_POST['edit']))
        {
            $shorten = ($result->shorten === '0' ? '' : 'checked="checked" ');
            $comments = ($result->comments_allowed === '0' ? '' : 'checked="checked" ');
        }

        $params = array(
            'PAGE' => (int) $info->modulParams('page'),
            'MSG' => $msg,
            'AUTHOR' =>  $result->author,
            'TITLE' => ($msg ? $title : $result->title),
            'TEXT' => htmlspecialchars(($msg ? $text : $result->rawtext)),
            'THIS' => $info->modulSelf,
            'ID' => $result->id,
            'SHORTEN' => $shorten,
            'COMMENTS' => $comments
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    elseif (!is_null($info->modulParams('del')))
    {
        user::hasRight('news_del') or headerTo($info->modulSelf . '&status=access_denied');
        if (isset($_POST['confirmation']))
        {
            if ($_POST['confirm'] == 'no')
            {
                headerTo($info->modulSelf);
            }
            elseif ($_POST['confirm'] == 'yes')
            {
                $id = (int) $info->modulParams('del');
                $query = 'DELETE FROM `PREFIX_news` WHERE `id`=? LIMIT 1';
                $types = 'i';
                $param_arr = array(
                    $id
                );
                $db->PushData($query, $types, $param_arr);
                Comments::deleteAll('news', $id);
                headerTo($info->modulSelf . '&status=comment_deleted');
            }
        }

        $params = array(
            'THIS' => $info->modulSelf . '&amp;del=' . $info->modulParams('del') . '&amp;page=' . $info->modulParams('page'),
            'LEGEND' => $lang->sure2delete
        );
        $tpl = new Template('confirm', $lang);
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    elseif (!is_null($info->modulParams('delpage')))
    {
        user::hasRight('news_del') or headerTo($info->modulSelf . '&status=access_denied');
        if (isset($_POST['confirmation']))
        {
            if ($_POST['confirm'] == 'no')
            {
                headerTo($info->modulSelf);
            }
            elseif ($_POST['confirm'] == 'yes')
            {
                $delindex = $info->modulParams('delpage');
                if ($delindex !== '0' && !is_numeric($delindex))
                {
                    headerTo($info->modulSelf);
                }
                $query = 'SELECT `id` FROM `PREFIX_news` ORDER BY `id` LIMIT ' . $delindex . ', ' . PAGELEN;
                $result = $db->getData($query);
                foreach ($result as &$row)
                {
                    $query = 'DELETE FROM `PREFIX_news` WHERE `id`=' . $row->id . ' LIMIT 1';
                    $db->PushData($query);
                    Comments::deleteAll('news', $row->id);
                }
                headerTo($info->modulSelf . '&status=comments_deleted_page');
            }
        }

        $params = array(
            'THIS' => $info->modulSelf . '&amp;delpage=' . $info->modulParams('delpage'),
            'LEGEND' => $lang->sure2delete_page
        );
        $tpl = new Template('confirm', $lang);
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    elseif (!is_null($info->modulParams('delall')))
    {
        user::hasRight('news_del') or headerTo($info->modulSelf . '&status=access_denied');
        if (isset($_POST['confirmation']))
        {
            if ($_POST['confirm'] == 'no')
            {
                headerTo($info->modulSelf);
            }
            elseif ($_POST['confirm'] == 'yes')
            {
                $query = 'DELETE FROM `PREFIX_news`';
                $db->PushData($query);

                headerTo($info->modulSelf . '&status=comments_deleted_all');
            }
        }

        $params = array(
            'THIS' => $info->modulSelf . '&amp;delall=true',
            'LEGEND' => $lang->sure2delete_all
        );
        $tpl = new Template('confirm', $lang);
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    elseif (!is_null($info->modulParams('comments')))
    {
        $id = abs((int) $info->modulParams('comments'));
        $do = $info->modulParams('do');
        if (is_null($do))
        {
            $do = 'show';
        }

        if ($do == 'show')
        {
            $count = Comments::countComments('news', $id);
            $cpage = $info->modulParams('cpage');
            $cpage = (is_null($cpage) ? 1 : $cpage);
            $offset = ($cpage - 1) * PAGELEN;
            $cpages = ceil($count / PAGELEN);
            $cpages = ($cpages == 0 ? 1 : $cpages);

            $tpl = new Template('news_comments', $lang);
            $tpl->setParams(array(
                'THIS' => $info->modulSelf,
                'ID' => $id,
                'DELINDEX' => $offset,
                'MSG' => $info->statusMessage($lang)
            ));
            $tpl->printPart(0, true);

            $comments = Comments::getRange('news', $id, $offset, PAGELEN);

            foreach ($comments as $i => &$comment)
            {
                $params = array(
                    'AUTHOR' => $comment['author'],
                    'DATE' => $comment['date'],
                    'ID' => $id,
                    'GUID' => $comment['guid'],
                    'PAGE' => $cpage,
                    'THIS' => $info->modulSelf,
                    'STYLE' => 'tablerow' . ($i % 2 == 0 ? '1' : '2')
                );
                $tpl->setParams($params);
                $tpl->printPart(1, true);
            }

            $next_page = '&nbsp;';
            if ($cpage < $cpages)
            {
                 $next_page = '<a href="' . SEO::makeAddress($info->modul, array('view' => $id, 'page' => $cpage + 1), true) . '">' . $lang->older . '</a>';
            }

            $prev_page = '&nbsp;';
            if ($cpage > 1)
            {
                $prev_page = '<a href="' . SEO::makeAddress($info->modul, array('view' => $id, 'page' => $cpage - 1), true)  . '">' . $lang->newer . '</a>';
            }
            $params = array(
                'PAGE' => $cpage,
                'PAGES-C' => $cpages,
                'PAGE-B' => $prev_page,
                'PAGE-F' => $next_page
            );
            $tpl->setParams($params);
            $tpl->printPart(2, true);
        }
        elseif ($do == 'switch')
        {
            user::hasRight('news_edit') or headerTo($info->modulSelf . '&status=access_denied');
            
            $query = 'SELECT `comments_allowed` FROM `PREFIX_news` WHERE `id`=' . $id . ' LIMIT 1';
            $state = $db->GetData($query);
            if ($db->affected_rows == 0)
            {
                headerTo($info->modulSelf);
            }
            $state = &$state[0]->comments_allowed;
            if ($state === '1')
            {
                $state = 0;
            }
            else
            {
                $state = 1;
            }
            $query = 'UPDATE `PREFIX_news` SET `comments_allowed`=' . $state . ' WHERE `id`=' . $id . ' LIMIT 1';
            $db->PushData($query);

            headerTo($info->modulSelf . '&page=' . (int) $info->modulParams('page') . '&status=switched_' . ($state ? 'on' : 'off'));
        }
        elseif ($do == 'edit' && !is_null($info->modulParams('guid')))
        {
            user::hasRight('news_edit') or headerTo($info->modulSelf . '&comments=' . $id . '&status=access_denied');
            $guid = abs((int) $info->modulParams('guid'));

            $msg = '';
            $author = '';
            $text = '';

            if (isset($_POST['edit']))
            {
                if (trim($_POST['author']) === '')
                {
                    $msg .= ' - ' . $lang->err_author . '<br />';
                }
                elseif (mb_strlen(trim($_POST['author']), 'UTF-8') > 50)
                {
                    $msg .= ' - ' . $lang->err_author2long(50) . '<br />';
                }
                if (trim($_POST['text']) === '')
                {
                    $msg .= ' - ' . $lang->err_c_text . '<br />';
                }
                if (trim($_POST['text']) === '')
                {
                    $msg .= ' - ' . $lang->err_c_text2long($cfg->news_commentlength) . '<br />';
                }

                if ($msg  === '')
                {
                    $author = &$_POST['author'];
                    $text = &$_POST['text'];
                    $ptext = Text::parse($_POST['text']);

                    Comments::edit('news', $id, $guid, $author, $text, $ptext);
                    headerTo($info->modulSelf . '&comments=' . $id . '&status=comment_updated&page=' . (int) $info->modulParams('page'));
                }
                else
                {
                    $msg = $lang->err . ':<br />' . $msg;
                    $author = htmlspecialchars((trim($_POST['author']) !== '' ? trim($_POST['author']) : ''));
                    $text = htmlspecialchars((trim($_POST['text']) !== '' ? trim($_POST['text']) : ''));
                }
            }

            $tpl = new Template('news_comments_edit', $lang);
            $comment = Comments::get('news', $id, $guid);
            if (!$comment)
            {
                headerTo($info->modulSelf . '&comments=' . $id);
            }

            $params = array(
                'PAGE' => (int) $info->modulParams('page'),
                'MSG' => $msg,
                'AUTHOR' =>  htmlspecialchars($comment['author']),
                'TEXT' => htmlspecialchars(($msg ? $text : $comment['raw'])),
                'THIS' => $info->requestUri,
                'ID' => $comment['guid']
            );
            $tpl->setParams($params);
            $tpl->printPart(0, true);
        }
        elseif ($do == 'del' && !is_null($info->modulParams('guid')))
        {
            user::hasRight('news_del') or headerTo($info->modulSelf . '&comments=' . $id . '&status=access_denied');
            $guid = abs((int) $info->modulParams('guid'));
            if (isset($_POST['confirmation']))
            {
                if ($_POST['confirm'] == 'no')
                {
                    headerTo($info->modulSelf . '&comments=' . $id);
                }
                elseif ($_POST['confirm'] == 'yes')
                {
                    Comments::delete('news', $id, $guid);

                    headerTo($info->modulSelf . '&comments=' . $id . '&status=comment_deleted');
                }
            }

            $params = array(
                'THIS' => $info->modulSelf . '&amp;comments=' . $id . '&amp;do=del&amp;guid=' . $guid,
                'LEGEND' => $lang->sure2delete_comment
            );
            $tpl = new Template('confirm', $lang);
            $tpl->setParams($params);
            $tpl->printPart(0, true);
        }
        elseif ($do == 'delpage' && !is_null($info->modulParams('offset')))
        {
            user::hasRight('news_del') or headerTo($info->modulSelf . '&comments=' . $id . '&status=access_denied');
            if (isset($_POST['confirmation']))
            {
                if ($_POST['confirm'] == 'no')
                {
                    headerTo($info->modulSelf . '&comments=' . $id);
                }
                elseif ($_POST['confirm'] == 'yes')
                {
                    $offset = (int) $info->modulParams('offset');
                    if (!Text::is_numeric($offset))
                    {
                        headerTo($info->modulSelf . '&comments=' . $id);
                    }
                    Comments::deleteRange('news', $id, $offset, PAGELEN);

                    headerTo($info->modulSelf . '&comments=' . $id . '&status=comments_deleted_page');
                }
            }

            $params = array(
                'THIS' => $info->requestUri,
                'LEGEND' => $lang->sure2delete_page
            );
            $tpl = new Template('confirm', $lang);
            $tpl->setParams($params);
            $tpl->printPart(0, true);
        }
        elseif ($do == 'delall')
        {
            user::hasRight('news_del') or headerTo($info->modulSelf . '&comments=' . $id . '&status=access_denied');
            if (isset($_POST['confirmation']))
            {
                if ($_POST['confirm'] == 'no')
                {
                    headerTo($info->modulSelf . '&comments=' . $id);
                }
                elseif ($_POST['confirm'] == 'yes')
                {
                    $offset = $info->modulParams('offset');
                    if ($offset !== '0' && !is_numeric($offset))
                    {
                        headerTo($info->modulSelf . '&comments=' . $id);
                    }
                    Comments::deleteAll('news', $id);

                    headerTo($info->modulSelf . '&comments=' . $id . '&status=comments_deleted_all');
                }
            }

            $params = array(
                'THIS' => $info->modulSelf . '&amp;do=delall',
                'LEGEND' => $lang->sure2delete_all
            );
            $tpl = new Template('confirm', $lang);
            $tpl->setParams($params);
            $tpl->printPart(0, true);
        }
    }
    else
    {
        $page = (int) $info->modulParams('page');
        $page = ($page > 0 ? $page : 1);
        $offset = ($page - 1) * PAGELEN;
        
        $tpl = new Template('news', $lang);
        $params = array(
            'MSG' => $info->statusMessage($lang),
            'THIS' => $info->modulSelf,
            'DELINDEX' => $offset
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);
        $query = 'SELECT `id`,`title`,`author`,DATE_FORMAT(`datetime`, \'%d.%c.%Y %k:%i\') AS \'date\',`comments_allowed` FROM `PREFIX_news` ORDER BY `datetime` DESC LIMIT ' . $offset . ', ' . PAGELEN;

        $result = $db->GetData($query);
        $rows = $db->CountTable('news');
        $pages_count = ceil($rows / PAGELEN);
        $pages_count = ($pages_count == 0 ? 1 : $pages_count);

        if ($result)
        {
            foreach ($result as $index => &$row)
            {
                $fullTitle = $row->title;
                if (mb_strlen($row->title) > 20)
                {
                    $row->title = mb_substr($row->title, 0, 20) . '...';
                }
                $switch = ($row->comments_allowed === '0' ? 'on' : 'off');
                $params = array(
                    'PAGE' => (int) $info->modulParams('page'),
                    'DATE' => $row->date,
                    'TITLE' => $row->title,
                    'FULLTITLE' => $fullTitle,
                    'AUTHOR' => $row->author,
                    'ID' => $row->id,
                    'THIS' => $info->modulSelf,
                    'STYLE' => 'tablerow' . ($index % 2 == 0 ? '1' : '2'),
                    'COMMENTS_SWITCH' => $lang->{'switch_' . $switch},
                    'COMMENTS_COUNT' => Comments::countComments('news', $row->id)
                );
                $tpl->setParams($params);
                $tpl->printPart(1, true);
            }
        }

        $next_page = '&nbsp;';
        if ($page < $pages_count)
        {
             $next_page = '<a href="' . $info->modulSelf . '&amp;page=' . ($page + 1) . '">' . $lang->older . '</a>';
        }

        $prev_page = '&nbsp';
        if ($page > 1)
        {
            $prev_page = '<a href="' . $info->modulSelf . '&amp;page=' . ($page - 1) . '">' . $lang->newer . '</a>';
        }

        $params = array(
            'PAGE-B' => $prev_page,
            'PAGE-F' => $next_page,
            'PAGE' => $page,
            'PAGES-C' => $pages_count
        );

        $tpl->setParams($params);
        $tpl->printPart(2, true);
    }
    
    $design->printEnd();
?>
