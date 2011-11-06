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
        user::hasRight('contact_add') or headerTo($info->modulSelf . '&status=access_denied');
        $msg = '';
        $name = '';
        $email = '';
        if (isset($_POST['post']))
        {
            if (trim($_POST['name']) === '')
            {
                $msg .= ' - ' . $lang->err_name . '<br />';
            }
            elseif (mb_strlen(trim($_POST['name']), 'UTF-8') > 70)
            {
                $msg .= ' - ' . $lang->err_name2long . '<br />';
            }
            if (trim($_POST['email']) === '' || !Text::is_email($_POST['email']))
            {
                $msg .= ' - ' . $lang->err_email . '<br />';
            }
            elseif (mb_strlen(trim($_POST['email']), 'UTF-8') > 100)
            {
                $msg .= ' - ' . $lang->err_email2long . '<br />';
            }


            if ($msg == '')
            {
                $name = htmlspecialchars($_POST['name']);
                $email = htmlspecialchars($_POST['email']);
                $msg = $lang->added;

                $query = 'INSERT INTO `PREFIX_contact` (`name`, `email`) VALUES (?, ?)';
                $types = 'ss';
                $param_arr = array($name, $email);
                $db->PushData($query, $types, $param_arr);
                headerTo($info->modulSelf . '&status=added');
            }
            else
            {
                $msg = $lang->err . ':<br />' . $msg;
                $name = ($_POST['name']) ? $_POST['name'] : '';
                $email = ($_POST['email']) ? $_POST['email'] : '';
            }
        }

        $tpl = new Template('contact_new', $lang);
        $params = array(
            'MSG' => $msg,
            'NAME' => $name,
            'EMAIL' => $email,
            'THIS' => $info->modulSelf
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    elseif (!is_null($info->modulParams('edit')))
    {
        user::hasRight('contact_edit') or headerTo($info->modulSelf . '&status=access_denied');
        $msg = '';
        $name = '';
        $email = '';

        if (isset($_POST['edit']))
        {
            if (trim($_POST['name']) === '')
            {
                $msg .= ' - ' . $lang->err_name . '<br />';
            }
            elseif (mb_strlen(trim($_POST['name']), 'UTF-8') > 70)
            {
                $msg .= ' - ' . $lang->err_name2long . '<br />';
            }
            if (trim($_POST['email']) === '' || !Text::is_email($_POST['email']))
            {
                $msg .= ' - ' . $lang->err_email . '<br />';
            }
            elseif (mb_strlen(trim($_POST['email']), 'UTF-8') > 100)
            {
                $msg .= ' - ' . $lang->err_email2long . '<br />';
            }

            if ($msg  === '')
            {
                $name = htmlspecialchars($_POST['name']);
                $email = htmlspecialchars($_POST['email']);

                $query = 'UPDATE `PREFIX_contact` SET `name`=?,`email`=? WHERE `id`=? LIMIT 1';
                $types = 'ssi';
                $param_arr = array($name, $email, (int) $info->modulParams('edit'));

                $db->PushData($query, $types, $param_arr);
                headerTo($info->modulSelf . '&status=updated&page=' . (int) $info->modulParams('page'));
            }
            else
            {
                $msg = $lang->err . ':<br />' . $msg;
                $name = (trim($_POST['name']) !== '' ? trim($_POST['name']) : '');
                $email = (trim($_POST['email']) !== '' ? trim($_POST['email']) : '');
            }
        }

        $tpl = new Template('contact_edit', $lang);
        $query = 'SELECT `id`,`name`,`email` FROM `PREFIX_contact` WHERE `id`=' . (int) $info->modulParams('edit');
        $result = $db->getData($query);
        if ($db->affected_rows == 0)
        {
            headerTo($info->modulSelf);
        }
        $result = &$result[0];

        $params = array(
            'PAGE' => (int) $info->modulParams('page'),
            'MSG' => $msg,
            'NAME' =>  ($msg ? $name : $result->name),
            'EMAIL' => ($msg ? $email : $result->email),
            'THIS' => $info->modulSelf,
            'ID' => $result->id
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    elseif (!is_null($info->modulParams('del')))
    {
        user::hasRight('contact_del') or headerTo($info->modulSelf . '&status=access_denied');
        if (isset($_POST['confirmation']))
        {
            if ($_POST['confirm'] == 'no')
            {
                headerTo($info->modulSelf);
            }
            elseif ($_POST['confirm'] == 'yes')
            {
                $query = 'DELETE FROM `PREFIX_contact` WHERE `id`=? LIMIT 1';
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
        user::hasRight('contact_del') or headerTo($info->modulSelf . '&status=access_denied');
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
                $query = 'SELECT `id` FROM `PREFIX_contact` ORDER BY `id` LIMIT ' . $delindex . ', ' . PAGELEN;
                $result = $db->getData($query);
                foreach ($result as $row)
                {
                    $query = 'DELETE FROM `PREFIX_contact` WHERE `id`=' . $row->id . ' LIMIT 1';
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
        user::hasRight('contact_del') or headerTo($info->modulSelf . '&status=access_denied');
        if (isset($_POST['confirmation']))
        {
            if ($_POST['confirm'] == 'no')
            {
                headerTo($info->modulSelf);
            }
            elseif ($_POST['confirm'] == 'yes')
            {
                $query = 'DELETE FROM `PREFIX_contact`';
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
        $tpl = new Template('contact', $lang);

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

        $query = 'SELECT * FROM `PREFIX_contact` ORDER BY `id` DESC LIMIT ' . $offset . ', ' . PAGELEN;

        $result = $db->GetData($query);
        $rows = $db->CountTable('contact');
        $pages_count = ceil($rows / PAGELEN);
        $pages_count = ($pages_count == 0 ? 1 : $pages_count);

        if ($result)
        {
            foreach ($result as $index => $row)
            {
                $fullName = $row->name;
                $fullEmail = $row->email;
                if (mb_strlen($fullName) > 20)
                {
                    $row->name = mb_substr($row->name, 0, 20) . '...';
                }
                if (mb_strlen($fullEmail) > 20)
                {
                    $row->email = mb_substr($row->email, 0, 30) . '...';
                }
                $params = array(
                    'PAGE' => (int) $info->modulParams('page'),
                    'NAME' => $row->name,
                    'FULLNAME' => $fullName,
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
             $next_page = '<a href="' . $info->modulSelf . '&amp;page=' . ($page + 1) . '">' . $lang->next_page . '</a>';
        }

        $prev_page = '&nbsp';
        if ($page > 1)
        {
            $prev_page = '<a href="' . $info->modulSelf . '&amp;page=' . ($page - 1) . '">' . $lang->prev_page . '</a>';
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
