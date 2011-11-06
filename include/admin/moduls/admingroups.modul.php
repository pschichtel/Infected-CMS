<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');
    if (!user::loggedIn())
    {
        user::backToLogin();
    }

    $lang = new Lang($info->modul);
    $modulLang = new Lang('moduls');
    $design = new Design();
    $design->printBegin();

    if (!is_null($info->modulParams('add')))
    {
        user::hasRight('admingroups_add') or headerTo($info->modulSelf . '&status=access_denied');
        $msg = '';
        $groupname = '';
        $rightArr = array();
        if (isset($_POST['post']))
        {
            if (trim($_POST['groupname']) === '')
            {
                $msg .= ' - ' . $lang->err_name_missing . '<br />';
            }
            elseif (preg_match('/[^\w\d\-]/i', $_POST['groupname']))
            {
                $msg .= ' - ' . $lang->err_forbidden_chars;
            }
            elseif (count($db->GetData('SELECT `id` FROM `PREFIX_admingroups` WHERE `groupname`=?', 's', array($_POST['groupname']))) > 0)
            {
                $msg .= ' - ' . $lang->err_name_in_use . '<br />';
            }
            if (!isset($_POST['type']))
            {
                $msg .= ' - ' . $lang->err_no_type . '<br />';
            }
            elseif ($_POST['type'] == 'all' && user::hasRight('ALL'))
            {
                $rights = '|ALL|';
            }
            elseif ($_POST['type'] == 'custom')
            {
                $rights = '|' . (isset($_POST['rights']) ? implode('|', $_POST['rights']) : '') . '|';
            }
            else
            {
                $msg .= ' - ' . $lang->err_wrong_type . '<br />';
            }

            if ($msg === '')
            {
                $query = 'INSERT INTO `PREFIX_admingroups` (`groupname`, `rights`) VALUES (?, ?)';
                $types = 'ss';
                $param_arr = array(htmlspecialchars($_POST['groupname']), $rights);
                $db->PushData($query, $types, $param_arr);

                headerTo($info->modulSelf . '&status=added');
            }
            else
            {
                $msg = $lang->err . ':<br />' . $msg;
                $groupname = ($_POST['groupname'] !== '' ? $_POST['groupname'] : '');
                $rightArr = (isset($_POST['rights']) ? $_POST['rights'] : array());
            }
        }

        $tpl = new Template('admingroups_new', $lang);
        $moduls = list_moduls(false, false);
        $selectsize = 0;
        $content = '';
        foreach ($moduls as $modul)
        {
            $rights = rights2array($modul);
            if (!$rights)
            {
                continue;
            }
            $selectsize++;
            $selects = '';
            foreach ($rights as $right)
            {
                $flag = $modul . '_' . $right;
                $selects .= '<option value="' . $flag . '"' . (in_array($flag, $rightArr) ? ' selected="selected"' : '') . '>'
                          . $lang->{'rights_' . $right}
                          . '</option>';
                $selectsize++;
            }
            $params = array(
                'MODUL' => $modulLang->{'m_' . $modul},
                'OPTIONS' => $selects
            );
            $tpl->setParams($params);
            $content .= $tpl->getPart(3, true);
        }
        
        $params = array(
            'STATUS' => $msg,
            'THIS' => $info->modulSelf . '&amp;add=new',
            'SIZE' => $selectsize,
            'GROUPNAME' => $groupname
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);
        if (user::hasRight('ALL'))
        {
            $tpl->printPart(1);
        }
        $tpl->printPart(2, true);
        echo $content;
        $tpl->printPart(4);
    }
    elseif (!is_null($info->modulParams('edit')))
    {
        user::hasRight('admingroups_edit') or headerTo($info->modulSelf . '&status=access_denied');

        $query = 'SELECT `id` FROM `PREFIX_admingroups` WHERE `id`=? AND `rights` LIKE \'%ALL%\'';
        $types = 'i';
        $param_arr = array((int) $info->modulParams('edit'));
        if ($db->GetData($query, $types, $param_arr) && !user::hasRight('ALL'))
        {
            headerTo($info->modulSelf . '&status=access_denied');
        }

        $query = 'SELECT `id` FROM `PREFIX_admingroups` WHERE `groupname`=\'' . $_SESSION['ci_admingroup'] . '\' AND `id`=' . (int) $info->modulParams('edit') . ' LIMIT 1';
        if (!user::hasRight('ALL') && $db->GetData($query))
        {
            headerTo($info->modulSelf . '&status=access_denied');
        }

        $msg = '';
        $groupname = '';
        $rightArr = array();

        if (isset($_POST['edit']))
        {
            if ($_POST['groupname'] != $_POST['old_groupname'])
            {
                if (trim($_POST['groupname']) === '')
                {
                    $msg .= ' - ' . $lang->err_name_missing . '<br />';
                }
                elseif (preg_match('/[^\w\d\-]/i', $_POST['groupname']))
                {
                    $msg .= ' - ' . $lang->err_forbidden_chars . '<br />';
                }
                elseif (count($db->GetData('SELECT `id` FROM `PREFIX_admingroups` WHERE `groupname`=?', 's', array($_POST['groupname']))) > 0)
                {
                    $msg .= ' - ' . $lang->err_name_in_use . '<br />';
                }
            }

            if (!isset($_POST['type']))
            {
                $msg .= ' - ' . $lang->err_no_type . '<br />';
            }
            elseif ($_POST['type'] == 'all' && user::hasRight('ALL'))
            {
                $rights = '|ALL|';
            }
            elseif ($_POST['type'] == 'custom')
            {
                $rights = '|' . (isset($_POST['rights']) ? implode('|', $_POST['rights']) : '') . '|';
            }
            else
            {
                $msg .= ' - ' . $lang->err_wrong_type . '<br />';
            }
            if ($msg === '')
            {
                $query = 'UPDATE `PREFIX_admingroups` SET `groupname`=?, `rights`=? WHERE `id`=? LIMIT 1';
                $types = 'ssi';
                $param_arr = array($_POST['groupname'], $rights, (int) $info->modulParams('edit'));
                $db->PushData($query, $types, $param_arr);
                $query = 'UPDATE `PREFIX_admins` SET `admingroup`=? WHERE `admingroup`=?';
                $types = 'ss';
                $param_arr = array($_POST['groupname'], $_POST['old_groupname']);
                $db->PushData($query, $types, $param_arr);
                headerTo($info->modulSelf . '&status=updated');
            }
            else
            {
                $msg = $lang->err . ':<br />' . $msg;
                $groupname = ($_POST['groupname'] !== '' ? $_POST['groupname'] : '');
                $rightArr = (isset($_POST['rights']) ? $_POST['rights'] : array());
            }
        }

        $moduls = list_moduls(false, false);
        $selectsize = 0;
        $query = 'SELECT `groupname`, `rights` FROM `PREFIX_admingroups` WHERE `id`=? LIMIT 1';
        $types = 'i';
        $param_arr = array($info->modulParams('edit'));
        $result = $db->GetData($query, $types, $param_arr);
        if ($db->affected_rows == 0)
        {
            headerTo($info->modulSelf);
        }
        $current_rights = explode('|', trim($result[0]->rights, '|'));

        $tpl = new Template('admingroups_edit', $lang);
        $content = '';

        foreach ($moduls as $modul)
        {
            $rights = rights2array($modul);
            if (!$rights)
            {
                continue;
            }
            $selectsize++;
            $selects = '';
            foreach ($rights as $right)
            {
                $flag = $modul . '_' . $right;
                if (count($rightArr) === 0)
                {
                    $selects .= '<option value="' . $flag . '"' . (in_array($flag, $current_rights) ? ' selected="selected"' : '') . '>'
                              . $lang->{'rights_' . $right}
                              . '</option>';
                }
                else
                {
                    $selects .= '<option value="' . $flag . '"' . (in_array($flag, $rightArr) ? ' selected="selected"' : '') . '>'
                              . $lang->{'rights_' . $right}
                              . '</option>';
                }
                $selectsize++;
            }
            $params['MODUL'] = $modulLang->{'m_' . $modul};
            $params['OPTIONS'] = $selects;
            $tpl->setParams($params);
            $content .= $tpl->getPart(3, true);
        }
        $params = array(
            'THIS' => $info->modulSelf . '&amp;edit=' . (int) $info->modulParams('edit'),
            'SIZE' => $selectsize,
            'STATUS' => $msg,
            'GROUPNAME' => (isset($_POST['groupname']) ? htmlspecialchars($_POST['groupname']) : $result[0]->groupname)
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);
        if (user::hasRight('ALL'))
        {
           $tpl->printPart(1);
        }
        $tpl->printPart(2, true);
        
        echo $content;
        
        $tpl->printPart(4);
    }
    elseif (!is_null($info->modulParams('del')))
    {
        user::hasRight('admingroups_del') or headerTo($info->modulSelf . '&status=access_denied');

        $query = 'SELECT `id` FROM `PREFIX_admingroups` WHERE `id`=? AND `rights` LIKE \'%ALL%\'';
        $types = 'i';
        $param_arr = array((int) $info->modulParams('del'));
        if ($db->GetData($query, $types, $param_arr) && !user::hasRight('ALL'))
        {
            headerTo($info->modulSelf . '&status=access_denied');
        }

        $query = 'SELECT `id` FROM `PREFIX_admingroups` WHERE `groupname`=\'' . $_SESSION['ci_admingroup'] . '\' AND `id`=' . (int) $info->modulParams('del') . ' LIMIT 1';
        if (!user::hasRight('ALL') && $db->GetData($query))
        {
            headerTo($info->modulSelf . '&status=access_denied');
        }

        if (isset($_POST['confirmation']))
        {
            if ($_POST['confirm'] == 'no')
            {
                headerTo($info->modulSelf);
            }
            elseif ($_POST['confirm'] == 'yes')
            {
                $query = 'DELETE FROM `PREFIX_admingroups` WHERE `id`=? LIMIT 1';
                $types = 'i';
                $param_arr = array((int) $info->modulParams('del'));
                $db->PushData($query, $types, $param_arr);
                headerTo($info->modulSelf . '&status=deleted');
            }
            else
            {
                headerTo($info->modulSelf);
            }
        }

        $tpl = new Template('confirm', $lang);
        $params = array(
            'THIS' => $info->modulSelf . '&amp;del=' . (int) $info->modulParams('del'),
            'LEGEND' => $lang->sure2delete
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    else
    {
        $tpl = new Template('admingroups', $lang);
        $params = array(
            'STATUS' => $info->statusMessage($lang),
            'THIS' => $info->modulSelf
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);

        $query = 'SELECT `id`, `groupname`, `rights` FROM `PREFIX_admingroups` ORDER BY `id`';
        $result = $db->GetData($query);

        foreach ($result as $index => $row)
        {
            $rights = explode('|', trim($row->rights, '|'));
            $params = array(
                'GROUPNAME' => $row->groupname,
                'RIGHTS' => count($rights),
                'ID' => $row->id,
                'STYLE' => 'tablerow' . ($index % 2 == 0 ? '1' : '2'),
                'THIS' => $info->modulSelf
            );
            $tpl->setParams($params);
            $tpl->printPart(1, true);
        }
        $tpl->printPart(2);
    }

    $design->printEnd();
?>
