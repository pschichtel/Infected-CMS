<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');
    if (!user::loggedIn())
    {
        user::backToLogin();
    }

    $lang = new Lang($info->modul);
    $design = new Design();
    $design->printBegin();
    
    if (!is_null($info->modulParams('add')))
    {
        user::hasRight('admins_add') or headerTo($info->modulSelf . '&status=access_denied');
        $msg = '';
        $name = '';
        if (isset($_POST['post']))
        {
            if (trim($_POST['name']) === '')
            {
                $msg .= ' - ' . $lang->err_name_missing . '<br />';
            }
            elseif (count($db->GetData('SELECT `id` FROM `PREFIX_admins` WHERE `name`=?', 's', array($_POST['name']))) > 0)
            {
                $msg .= ' - ' . $lang->err_name_in_use . '<br />';
            }
            elseif (preg_match('/[^\w\d\-]/i', $_POST['name']))
            {
                $msg .= ' - ' . $lang->err_forbidden_chars . '<br />';
            }
            if ($_POST['pass'] != $_POST['repass'])
            {
                $msg .= ' - ' . $lang->err_pwds_not_equal . '<br />';
            }
            elseif (mb_strlen($_POST['pass']) < 6)
            {
                $msg .= ' - ' . $lang->err_pwd_too_short(6) . '<br />';
            }
            if (!user::hasRight('ALL'))
            {
                $admingroup = $_SESSION['ci_admingroup'];
            }
            else
            {
                $admingroup = $_POST['admingroup'];
            }

            if ($msg === '')
            {
                $query = 'INSERT INTO `PREFIX_admins` (`name`, `password`,`dynsalt`,`admingroup`) VALUES (?,?,?,?)';
                $types = 'ssss';
                list($dynsalt, $pass) = explode('|', password($_POST['pass']));
                $param_arr = array(
                    htmlspecialchars($_POST['name']),
                    $pass,
                    $dynsalt,
                    htmlspecialchars($admingroup)
                );
                $db->PushData($query, $types, $param_arr);
                headerTo($info->modulSelf . '&status=added');
            }
            else
            {
                $msg = $lang->err . ':<br />' . $msg;
                $name = ($_POST['name'] !== '' ? $_POST['name'] : '');
            }
        }
        $groupselect = '';
        if (user::hasRight('ALL'))
        {
            $query = 'SELECT `groupname` FROM `PREFIX_admingroups`';
            $result = $db->GetData($query);
            $groupselect = '<label for="admingroup">' . $lang->group . ':</label><select name="admingroup" id="admingroup">';
            if (isset($_POST['admingroup']))
            {
                $groupselect .= '<option value="' . $_POST['admingroup'] . '">' . $_POST['admingroup'] . '</option>';
            }
            foreach ($result as $row)
            {
                $groupselect .= '<option value="' . $row->groupname . '">' . $row->groupname . '</option>';
            }
            $groupselect .= '</select><br />';
        }
        $params = array(
            'THIS' => $info->modulSelf . '&amp;add=new',
            'NAME' => $name,
            'ADMINGROUP' => $groupselect,
            'STATUS' => $msg
        );
        $tpl = new Template('admins_new', $lang);
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    elseif (!is_null($info->modulParams('edit')))
    {
        user::hasRight('admins_edit') or headerTo($info->modulSelf . '&status=access_denied');
        $msg = '';
        $name = '';
        if(isset($_POST['edit']))
        {
            if (!user::hasRight('ALL'))
            {
                $query = 'SELECT `id`,`password`,`dynsalt` FROM `PREFIX_admins` WHERE `id`=? AND `name`=?';
                $types = 'is';
                $param_arr = array($info->modulParams('edit'), $_POST['oldname']);
                $result = $db->GetData($query, $types, $param_arr);
                if (!$result || $result[0]->dynsalt . '|' . $result[0]->password !== password($_POST['oldpass'], $result[0]->dynsalt .'|'))
                {
                    $msg .= ' - ' . $lang->err_wrong_pwd . '<br />';
                }
            }
            if (empty($_POST['pass']) && empty($_POST['repass']))
            {
                $query = 'UPDATE `PREFIX_admins` SET `name`=? /*? ?*/, `admingroup`=? WHERE `id`=? AND `name`=? LIMIT 1';
            }
            else
            {
                if ($_POST['pass'] != $_POST['repass'])
                {
                    $msg .= ' - ' . $lang->err_pwds_not_equal . '<br />';
                }
                elseif (mb_strlen($_POST['pass']) < 6)
                {
                    $msg .= ' - ' . $lang->err_pwd_too_short(6) . '<br />';
                }
                else
                {
                    $query = 'UPDATE `PREFIX_admins` SET `name`=?, `dynsalt`=?,`password`=?,`admingroup`=? WHERE `id`=? AND `name`=? LIMIT 1';
                }
            }
            if ($msg === '')
            {
                $types = 'ssssis';
                if (user::hasRight('ALL'))
                {
                    $admingroup = $_POST['admingroup'];
                }
                else
                {
                    $checkQuery = 'SELECT `admingroup` FROM `PREFIX_admins` WHERE `id`=? LIMIT 1';
                    $checkTypes = 'i';
                    $checkParams = array($info->modulParams('edit'));
                    $result = $db->GetData($checkQuery, $checkTypes, $checkParams);
                    $result = $result[0];
                    $admingroup = $result->admingroup;
                }
                list($salt, $pass) = explode('|', password($_POST['pass']));
                $params = array(
                    $_POST['name'],
                    $salt,
                    $pass,
                    $admingroup,
                    $info->modulParams('edit'),
                    $_POST['oldname']
                );
                $db->PushData($query, $types, $params);
                headerTo($info->modulSelf . '&status=updated');
            }
            else
            {
                $msg = $lang->err . ':<br />' . $msg;
                $name = ($_POST['name'] !== '' ? $_POST['name'] : '');
            }
        }
        
        $query = 'SELECT `id`, `name`,`admingroup` FROM `PREFIX_admins` WHERE `id`=?';
        $types = 'i';
        $param_arr = array((int) $info->modulParams('edit'));
        $result = $db->GetData($query, $types, $param_arr);
        if ($db->affected_rows == 0)
        {
            headerTo($info->modulSelf);
        }

        $result = $result[0];
        if (!user::hasRight('ALL') && $result->id != $_SESSION['ci_adminid'])
        {
            headerTo($info->modulSelf . '&status=access_denied');
        }
        $groupselect = '';
        if (user::hasRight('ALL'))
        {
            $query = 'SELECT `id`, `groupname` FROM `PREFIX_admingroups` WHERE `groupname`!=?';
            $types = 's';
            $param = array($result->admingroup);
            $tmp_result = $db->GetData($query, $types, $param);
            $groupselect = <<<select
<label for="admingroup">{$lang->group}:</label>
<select name="admingroup" id="admingroup">
select;
            $groupselect .= '<option value="' . $result->admingroup . '">' . $result->admingroup . '</option>';
            foreach ($tmp_result as $row)
            {
                $groupselect .= '<option value="' . $row->groupname . '">' . $row->groupname . '</option>';
            }
            $groupselect .= '</select><br />';
        }
        $params = array(
            'THIS' => $info->modulSelf . '&amp;edit=' . $info->modulParams('edit'),
            'NAME' => (isset($_POST['name']) ? htmlspecialchars($_POST['name']) : $result->name),
            'OLDNAME' => $result->name,
            'ADMINGROUP' => $groupselect,
            'STATUS' => $msg
        );
        $tpl = new Template('admins_edit', $lang);
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    elseif (!is_null($info->modulParams('del')))
    {
        user::hasRight('admins_del') or headerTo($info->modulSelf . '&status=access_denied');
        if (isset($_POST['confirmation']))
        {
            if ($_POST['confirm'] == 'no')
            {
                headerTo($info->modulSelf);
            }
            elseif ($_POST['confirm'] == 'yes')
            {
                $query = 'DELETE FROM `PREFIX_admins` WHERE `id`=? LIMIT 1';
                $types = 'i';
                $param_arr = array((int) $info->modulParams('del'));
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
    else
    {
        $tpl = new Template('admins', $lang);

        $params = array(
            'THIS' => $info->modulSelf,
            'STATUS' => $info->statusMessage($lang)
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);

        $query = 'SELECT `id`, `name`, `admingroup` FROM `PREFIX_admins`';
        $result = $db->GetData($query);

        if ($result)
        {
            foreach ($result as $index => $row)
            {
                $params = array(
                    'THIS' => $info->modulSelf,
                    'NAME' => $row->name,
                    'ID' => $row->id,
                    'GROUP' => $row->admingroup,
                    'STYLE' => 'tablerow' . ($index % 2 == 0 ? '1' : '2')
                );
                $tpl->setParams($params);
                $tpl->printPart(1, true);
            }
        }
        $tpl->printPart(2);
    }
    $design->printEnd();
?>
