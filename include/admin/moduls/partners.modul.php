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
        user::hasRight('partners_add') or headerTo($info->modulSelf . '&status=access_denied');
        $msg = '';
        $name = '';
        $pageuri = '';
        $banneruri = '';

        if (isset($_POST['post']))
        {
            if (trim($_POST['name']) === '')
            {
                $msg .= ' - ' . $lang->err_name . '<br />';
            }
            elseif (mb_strlen(trim($_POST['name']), 'UTF-8') > 100)
            {
                $msg .= ' - ' . $lang->err_name2long . '<br />';
            }
            if (trim($_POST['pageuri']) === '')
            {
                $msg .= ' - ' . $lang->err_pageuri . '<br />';
            }
            elseif (mb_strlen(trim($_POST['pageuri']), 'UTF-8') > 150)
            {
                $msg .= ' - ' . $lang->err_pageuri2long . '<br />';
            }
            if (trim($_POST['banneruri']) === '')
            {
                $msg .= ' - ' . $lang->err_banneruri . '<br />';
            }
            elseif (mb_strlen(trim($_POST['banneruri']), 'UTF-8') > 150)
            {
                $msg .= ' - ' . $lang->err_banneruri2long . '<br />';
            }


            if ($msg == '')
            {
                $name = htmlspecialchars($_POST['name']);
                $pageuri = htmlspecialchars($_POST['pageuri']);
                $banneruri = htmlspecialchars($_POST['banneruri']);

                $msg = $lang->added;

                $query = 'INSERT INTO `PREFIX_partners` (`name`, `pageuri`, `banneruri`,`position`) VALUES (?, ?, ?, ?)';
                $types = 'sssi';
                $param_arr = array($name, $pageuri, $banneruri, $db->CountTable('partners') + 1);
                $db->PushData($query, $types, $param_arr);
                headerTo($info->modulSelf . '&status=added');
            }
            else
            {
                $msg = $lang->err . ':<br />' . $msg;
                $name = ($_POST['name'] !== '' ? $_POST['name'] : '');
                $pageuri = ($_POST['pageuri'] !== '' ? $_POST['pageuri'] : '');
                $banneruri = ($_POST['banneruri'] !== '' ? $_POST['banneruri'] : '');
            }
        }

        $tpl = new Template('partners_new', $lang);
        $params = array(
            'MSG' => $msg,
            'NAME' => $name,
            'PAGEURI' => $pageuri,
            'BANNERURI' => $banneruri,
            'THIS' => $info->modulSelf
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    elseif (!is_null($info->modulParams('edit')))
    {
        user::hasRight('partners_edit') or headerTo($info->modulSelf . '&status=access_denied');
        $msg = '';
        $name = '';
        $pageuri = '';
        $banneruri = '';

        if (isset($_POST['edit']))
        {
            if (trim($_POST['name']) === '')
            {
                $msg .= ' - ' . $lang->err_name . '<br />';
            }
            elseif (mb_strlen(trim($_POST['name']), 'UTF-8') > 100)
            {
                $msg .= ' - ' . $lang->err_name2long . '<br />';
            }
            if (trim($_POST['pageuri']) === '')
            {
                $msg .= ' - ' . $lang->err_pageuri . '<br />';
            }
            elseif (mb_strlen(trim($_POST['pageuri']), 'UTF-8') > 150)
            {
                $msg .= ' - ' . $lang->err_pageuri2long . '<br />';
            }
            if (trim($_POST['banneruri']) === '')
            {
                $msg .= ' - ' . $lang->err_banneruri . '<br />';
            }
            elseif (mb_strlen(trim($_POST['banneruri']), 'UTF-8') > 150)
            {
                $msg .= ' - ' . $lang->err_banneruri2long . '<br />';
            }

            if ($msg  === '')
            {
                $name = htmlspecialchars($_POST['name']);
                $pageuri = htmlspecialchars($_POST['pageuri']);
                $banneruri = htmlspecialchars($_POST['banneruri']);

                $query = 'UPDATE `PREFIX_partners` SET `name`=?,`pageuri`=?,`banneruri`=? WHERE `id`=? LIMIT 1';
                $types = 'sssi';
                $param_arr = array($name, $pageuri, $banneruri, (int) $info->modulParams('edit'));

                $db->PushData($query, $types, $param_arr);
                headerTo($info->modulSelf . '&status=updated&page=' . (int) $info->modulParams('page'));
            }
            else
            {
                $msg = $lang->err . ':<br />' . $msg;
                $name = (trim($_POST['name']) !== '' ? trim($_POST['name']) : '');
                $pageuri = (trim($_POST['pageuri']) !== '' ? trim($_POST['pageuri']) : '');
                $banneruri = (trim($_POST['banneruri']) !== '' ? trim($_POST['banneruri']) : '');
            }
        }

        $tpl = new Template('partners_edit', $lang);
        $query = 'SELECT `id`,`name`,`pageuri`,`banneruri` FROM `PREFIX_partners` WHERE `id`=' . (int) $info->modulParams('edit');
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
            'PAGEURI' => ($msg ? $pageuri : $result->pageuri),
            'BANNERURI' => ($msg ? $banneruri : $result->banneruri),
            'THIS' => $info->modulSelf,
            'ID' => $result->id
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    elseif (!is_null($info->modulParams('del')))
    {
        user::hasRight('partners_del') or headerTo($info->modulSelf . '&status=access_denied');
        if (isset($_POST['confirmation']))
        {
            if ($_POST['confirm'] == 'no')
            {
                headerTo($info->modulSelf);
            }
            elseif ($_POST['confirm'] == 'yes')
            {
                $query = 'SELECT `position` FROM `PREFIX_partners` WHERE `id`=' . (int) $info->modulParams('del') . ' LIMIT 1';
                $result = $db->GetData($query);
                $posi = $result[0]->position;
                $query = 'DELETE FROM `PREFIX_partners` WHERE `id`=' . (int) $info->modulParams('del');
                $db->PushData($query);
                $query = 'UPDATE `PREFIX_partners` SET `position`=`position`-1 WHERE `position` >=' . $posi;
                $db->PushData($query);
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
        user::hasRight('partners_del') or headerTo($info->modulSelf . '&status=access_denied');
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
                    headerTo($info->modelSelf);
                }
                $query = 'SELECT `id` FROM `PREFIX_partners` ORDER BY `id` LIMIT ' . $delindex . ', ' . PAGELEN;
                $result = $db->getData($query);
                foreach ($result as $row)
                {
                    $query = 'DELETE FROM `PREFIX_partners` WHERE `id`=' . $row->id . ' LIMIT 1';
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
        user::hasRight('partners_del') or headerTo($info->modulSelf . '&status=access_denied');
        if (isset($_POST['confirmation']))
        {
            if ($_POST['confirm'] == 'no')
            {
                headerTo($info->modulSelf);
            }
            elseif ($_POST['confirm'] == 'yes')
            {
                $query = 'DELETE FROM `PREFIX_partners`';
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
    elseif (!is_null($info->modulParams('pos_up')))
    {
        user::hasRight('partners_move') or headerTo($info->modulSelf . '&status=access_denied');

        $pos = (int) $info->modulParams('pos');
        $page = (int) $info->modulParams('page');
        $id = (int) $info->modulParams('pos_up');

        $query = 'SELECT `position` FROM `PREFIX_partners` WHERE `id`=' . $id;
        $posi = $db->GetData($query);
        if ($posi[0]->position <= 1)
        {
            headerTo($info->modulSelf . '&page=' . $page);
        }

        $query = 'UPDATE `PREFIX_partners` SET `position`=0 WHERE `id`=' . $id;
        $db->PushData($query);
        $query = "UPDATE `PREFIX_partners` SET `position`=`position`+1 WHERE `position`=" . --$pos;
        $db->PushData($query);
        $query = "UPDATE `PREFIX_partners` SET `position`=$pos WHERE `id`=$id";
        $db->PushData($query);
        headerTo($info->modulSelf . '&page=' . $page . '&status=pos_upped');
    }
    elseif (!is_null($info->modulParams('pos_down')))
    {
        user::hasRight('partners_move') or headerTo($info->modulSelf . '&status=access_denied');

        $pos = (int) $info->modulParams('pos');
        $page = (int) $info->modulParams('page');
        $id = (int) $info->modulParams('pos_down');

        $query = 'SELECT count(*) AS \'count\' FROM `PREFIX_partners`';
        $count = $db->GetData($query);
        if ($pos >= $count[0]->count)
        {
            headerTo($info->modulSelf . '&page=' . $page);
        }
        $query = 'UPDATE `PREFIX_partners` SET `position`=0 WHERE `id`=' . $id;
        $db->PushData($query);
        $query = "UPDATE `PREFIX_partners` SET `position`=`position`-1 WHERE `position`=" . ++$pos;
        $db->PushData($query);
        $query = "UPDATE `PREFIX_partners` SET `position`=$pos WHERE `id`=$id";
        $db->PushData($query);
        headerTo($info->modulSelf . '&page=' . $page . '&status=pos_downed');
    }
    else
    {
        $page = (int) $info->modulParams('page');
        $page = ($page > 0 ? $page : 1);
        $offset = ($page - 1) * PAGELEN;
        
        $tpl = new Template('partners', $lang);
        $params = array(
            'MSG' => $info->statusMessage($lang),
            'THIS' => $info->modulSelf,
            'DELINDEX' => $offset
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);


        $query = 'SELECT * FROM `PREFIX_partners` ORDER BY `position` LIMIT ' . $offset . ', ' . PAGELEN;

        $result = $db->GetData($query);
        $rows = $db->CountTable('partners');
        $pages_count = ceil($rows / PAGELEN);
        $pages_count = ($pages_count == 0 ? 1 : $pages_count);

        foreach ($result as $index => &$row)
        {
            $fullName = $row->name;
            if (mb_strlen($row->name) > 20)
            {
                $row->name = mb_substr($row->name, 0, 20) . '...';
            }
            $fullPageuri = $row->pageuri;
            if (mb_strlen($row->pageuri) > 20)
            {
                $row->pageuri = mb_substr($row->pageuri, 0, 20) . '...';
            }
            $params = array(
                'PAGE' => (int) $info->modulParams('page'),
                'NAME' => $row->name,
                'FULLNAME' => $fullName,
                'BANNERURI' => $row->banneruri,
                'PAGEURI' => $row->pageuri,
                'FULLPAGEURI' => $fullPageuri,
                'ID' => $row->id,
                'POSITION' => $row->position,
                'THIS' => $info->modulSelf,
                'STYLE' => 'tablerow' . ($index % 2 == 0 ? '1' : '2')
            );
            $tpl->setParams($params);
            $tpl->printPart(1, true);
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
