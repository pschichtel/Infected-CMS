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

    $msg = '';
    if (!is_null($info->modulParams('edit')))
    {
        user::hasRight('gbook_edit') or headerTo($info->modulSelf . '&status=access_denied', true);
        $msg = '';
        $author = '';
        $email = '';
        $title = '';
        $text = '';
        
        if (isset($_POST['edit']))
        {
            if (trim($_POST['author']) === '')
            {
                $msg .= ' - ' . $lang->err_name . '<br />';
            }
            elseif (mb_strlen(trim($_POST['author']), 'UTF-8') > 40)
            {
                $msg .= ' - ' . $lang->err_name2long . '<br />';
            }
            if (trim($_POST['email']) !== '' && !Text::is_email($_POST['email']))
            {
                $msg .= ' - ' . $lang->err_email . '<br />';
            }
            elseif (mb_strlen(trim($_POST['email']), 'UTF-8') > 60)
            {
                $msg .= ' - ' . $lang->err_email2long . '<br />';
            }
            if (trim($_POST['text']) === '')
            {
                $msg .= ' - ' . $lang->err_text . '<br />';
            }
            elseif (mb_strlen(trim($_POST['text']), 'UTF-8') > $cfg->gbook_textlen)
            {
                $msg .= ' - ' . $lang->err_text2long . '<br />';
            }

            if ($msg == '')
            {
                $author = htmlspecialchars($_POST['author']);
                $email = htmlspecialchars($_POST['email']);
                $text = &$_POST['text'];
                $ptext = Text::parse($_POST['text'], true, true, true, array('list', 'code', 'video', 'quote'));

                $query = 'UPDATE `PREFIX_gbook` SET `author`=?,`email`=?,`rawtext`=?,`parsedtext`=? WHERE `id`=? LIMIT 1';
                $types = 'ssssi';
                $param_arr = array($author, $email, $text, $ptext, (int) $info->modulParams('edit'));
                $db->PushData($query, $types, $param_arr);
                headerTo($info->modulSelf . '&status=updated&page=' . (int) $info->modulParams('page'));
            }
            else
            {
                $msg = $lang->err . ':<br />' . $msg;
                $author = ($_POST['author'] !== '' ? $_POST['author'] : '');
                $email = ($_POST['email'] !== '' ? $_POST['email'] : '');
                $text = ($_POST['text'] !== '' ? $_POST['text'] : '');
            }
        }

        $tpl = new Template('gbook_edit', $lang);
        $query = 'SELECT `id`,`author`,`email`,`rawtext`,`ip` FROM `PREFIX_gbook` WHERE `id`=' . (int) $info->modulParams('edit');
        $result = $db->getData($query);
        if ($db->affected_rows == 0)
        {
            headerTo($info->modulSelf);
        }
        $result = &$result[0];

        $params = array(
            'PAGE' => (int) $info->modulParams('page'),
            'MSG' => $msg,
            'AUTHOR' => ($msg ? $author : $result->author),
            'EMAIL' => ($msg ? $email : $result->email),
            'TEXT' => htmlspecialchars(($msg ? $text : $result->rawtext)),
            'THIS' => $info->modulSelf,
            'ID' => $result->id,
            'IP' => $result->ip
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    elseif (!is_null($info->modulParams('del')))
    {
        user::hasRight('gbook_del') or headerTo($info->modulSelf . '&status=access_denied');
        if (isset($_POST['confirmation']))
        {
            if ($_POST['confirm'] == 'no')
            {
                headerTo($info->modulSelf);
            }
            elseif ($_POST['confirm'] == 'yes')
            {
                $query = 'DELETE FROM `PREFIX_gbook` WHERE `id`=? LIMIT 1';
                $types = 'i';
                $param_arr = array(
                    (int) $info->modulParams('del')
                );
                $db->PushData($query, $types, $param_arr);
                headerTo($info->modulSelf . '&status=deleted');
            }
        }

        $params = array(
            'THIS' => $info->modulSelf . '&amp;del=' . $info->modulParams('del'),
            'LEGEND' => $lang->sure2delete
        );
        $tpl = new Template('confirm', $lang);
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    elseif (!is_null($info->modulParams('delpage')))
    {
        user::hasRight('gbook_del') or headerTo($info->modulSelf . '&status=access_denied');
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
                $query = 'SELECT `id` FROM `PREFIX_gbook` ORDER BY `id` DESC LIMIT ' . $delindex . ', ' . PAGELEN;
                $result = $db->getData($query);
                foreach ($result as $row)
                {
                    $query = 'DELETE FROM `PREFIX_gbook` WHERE `id`=' . $row->id . ' LIMIT 1';
                    $db->PushData($query);
                }
                headerTo($info->modulSelf . '&status=deleted_page');
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
        user::hasRight('gbook_del') or headerTo($info->modulSelf . '&status=access_denied');
        if (isset($_POST['confirmation']))
        {
            if ($_POST['confirm'] == 'no')
            {
                headerTo($info->modulSelf);
            }
            elseif ($_POST['confirm'] == 'yes')
            {
                $query = 'DELETE FROM `PREFIX_gbook`';
                $db->PushData($query);

                headerTo($info->modulSelf . '&status=deleted_all');
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
    else
    {
        $tpl = new Template('gbook', $lang);

        $page = (int) $info->modulParams('page');
        $page = ($page > 0 ? $page : 1);
        $offset = ($page - 1) * PAGELEN;

        $params = array(
            'THIS' => $info->modulSelf,
            'MSG' => $info->statusMessage($lang),
            'DELINDEX' => $offset
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);

        $query = 'SELECT `id`,`author`,`email`,DATE_FORMAT(`date`, \'%d.%c.%Y\') AS \'date\' FROM `PREFIX_gbook` ORDER BY `id` DESC LIMIT ' . $offset . ', ' . PAGELEN;

        $result = $db->GetData($query);
        $rows = $db->CountTable('gbook');
        $pages_count = ceil($rows / PAGELEN);
        $pages_count = ($pages_count == 0 ? 1 : $pages_count);

        if ($result)
        {
            foreach ($result as $index => &$row)
            {
                $fullEmail = $row->email;
                $fullAuthor = $row->author;
                if (mb_strlen($fullEmail) > 20)
                {
                    $row->email = mb_substr($row->email, 0, 20) . '...';
                }
                if (mb_strlen($fullAuthor) > 20)
                {
                    $row->author = mb_substr($row->author, 0, 20) . '...';
                }
                $params = array(
                    'PAGE' => (int) $info->modulParams('page'),
                    'DATE' => $row->date,
                    'AUTHOR' => $row->author,
                    'FULLAUTHOR' => $fullAuthor,
                    'EMAIL' => $row->email,
                    'FULLEMAIL' => $fullEmail,
                    'ID' => $row->id,
                    'THIS' => $info->modulSelf,
                    'STYLE' => 'tablerow' . ($index % 2 == 0 ? '1' : '2')
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

        $design->printEnd();
    }
?>
