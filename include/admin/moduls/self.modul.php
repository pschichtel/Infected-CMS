<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');
    if (!user::loggedIn())
    {
        user::backToLogin();
    }

    $lang = new Lang($info->modul);
    $design = new Design();
    $design->printBegin();

    $msg = '';
    if (!is_null($info->modulParams('add')))
    {
        user::hasRight('self_add') or headerTo($info->modulSelf . '&status=access_denied');

        $name = '';
        $selfcontent = '';
        if (isset($_POST['post']))
        {
            $query = 'INSERT INTO `PREFIX_selfcontent` (`name`,`content`,`html`,`bbcode`,`smiles`,`added`,`modified`) VALUES (?,?,?,?,?,NOW(),NOW())';
            $types = 'ssiii';
            $param_arr = array(
                htmlspecialchars((trim($_POST['name']) !== '' ? $_POST['name'] : $lang->unnamed)),
                $_POST['content'],
                (isset($_POST['html']) ? 1 : 0),
                (isset($_POST['bbcode']) ? 1 : 0),
                (isset($_POST['smiles']) ? 1 : 0)
            );
            $db->PushData($query, $types, $param_arr);

            headerTo($info->modulSelf . '&status=added');
        }
        $tpl = new Template('self_new-edit', $lang);

        $params = array(
            'THIS' => $info->modulSelf . '&amp;add=new',
            'NAME' => $name,
            'SELFCONTENT' => $selfcontent,
            'STATUS-HTML' => '',
            'STATUS-BBCODE' => '',
            'STATUS-SMILES' => ''
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    elseif (!is_null($info->modulParams('edit')))
    {
        user::hasRight('self_edit') or headerTo($info->modulSelf . '&status=access_denied');

        if(isset($_POST['post']))
        {
            $query = 'UPDATE `PREFIX_selfcontent` SET `name`=?,`content`=?,`html`=?,`bbcode`=?,`smiles`=?,`modified`=NOW() WHERE `id`=? LIMIT 1';
            $types = 'ssiiii';
            $param_arr = array(
                (trim($_POST['name']) !== '' ? $_POST['name'] : $lang->unnamed),
                $_POST['content'],
                (isset($_POST['html']) ? 1 : 0),
                (isset($_POST['bbcode']) ? 1 : 0),
                (isset($_POST['smiles']) ? 1 : 0),
                $info->modulParams('edit')
            );
            $db->PushData($query, $types, $param_arr);
            headerTo($info->modulSelf . '&status=updated');
        }
        $query = 'SELECT `name`, `content`, `html`, `bbcode`, `smiles` FROM `PREFIX_selfcontent` WHERE `id`=?';
        $types = 'i';
        $param_arr = array((int) $info->modulParams('edit'));
        $result = $db->GetData($query, $types, $param_arr);
        if ($db->affected_rows == 0)
        {
            headerTo($info->modulSelf);
        }

        $result = &$result[0];
        $tpl = new Template('self_new-edit', $lang);

        $params = array(
            'THIS' => $info->modulSelf . '&amp;edit=' . $info->modulParams('edit'),
            'NAME' => htmlspecialchars($result->name),
            'SELFCONTENT' => htmlspecialchars($result->content),
            'STATUS-HTML' => ($result->html ? 'checked="checked"' : ''),
            'STATUS-BBCODE' => ($result->bbcode ? 'checked="checked"' : ''),
            'STATUS-SMILES' => ($result->smiles ? 'checked="checked"' : '')
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    elseif (!is_null($info->modulParams('del')))
    {
        user::hasRight('self_del') or headerTo($info->modulSelf . '&status=access_denied');

        if (isset($_POST['confirmation']))
        {
            if ($_POST['confirm'] == 'no')
            {
                headerTo($info->modulSelf);
            }
            elseif ($_POST['confirm'] == 'yes')
            {
                $query = 'DELETE FROM `PREFIX_selfcontent` WHERE `id`=? LIMIT 1';
                $types = 'i';
                $param_arr = array((int) $info->modulParams('del'));
                $db->PushData($query, $types, $param_arr);
                headerTo($info->modulSelf . '&status=deleted');
            }
        }
        $tpl = new Template('confirm', $lang);

        $params = array(
            'THIS' => $info->modulSelf . '&amp;del=' . $info->modulParams('del'),
            'LEGEND' => $lang->sure2delete
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    else
    {
        $tpl = new Template('self', $lang);
        
        $params = array(
            'THIS' => $info->modulSelf,
            'MSG' => ($msg ? $msg : $info->statusMessage($lang))
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);

        $query = 'SELECT `id`,`name`,`html`,`bbcode`,`smiles`,'
               . 'DATE_FORMAT(`added`,\'%e.%c.%Y %k:%i\') AS \'added\',DATE_FORMAT(`modified`,\'%e.%c.%Y %k:%i\') AS \'modified\' '
               . 'FROM `PREFIX_selfcontent` ORDER BY `modified`';
        $result = $db->GetData($query);

        foreach ($result as $index => &$row)
        {
            $params = array(
                'NAME' => $row->name,
                'ID' => $row->id,
                'HTML' => ($row->html ? $lang->Yes : $lang->No),
                'BBCODE' => ($row->bbcode ? $lang->Yes : $lang->No),
                'SMILES' => ($row->smiles ? $lang->Yes : $lang->No),
                'THIS' => $info->modulSelf,
                'STYLE' => 'tablerow' . ($index % 2 == 0 ? '1' : '2'),
                'ADDED' => $row->added,
                'MODIFIED' => $row->modified
            );
            $tpl->setParams($params);
            $tpl->printPart(1, true);
        }
        $tpl->printPart(2);
    }

    $design->printEnd();
?>
