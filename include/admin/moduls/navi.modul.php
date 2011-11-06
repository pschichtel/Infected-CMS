<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');
    if (!user::loggedIn())
    {
        user::backToLogin();
    }

    $lang = new Lang($info->modul);
    $design = new Design();
    $design->printBegin();

    $pos = (int) $info->modulParams('pos');
    $id = (int) $info->modulParams('id');
    $menuid = (int) $info->modulParams('menuid');
    
    if (!is_null($info->modulParams('add')))
    {
        user::hasRight('navi_add') or headerTo($info->modulSelf . '&status=access_denied');
        if (isset($_POST['post']))
        {
            $name = htmlspecialchars(trim($_POST['name']));
            $addr = htmlspecialchars(trim($_POST['addr']));
            $type = (int) $_POST['type'];
            $position = (int) $_POST['pos'];
            $level = (int) $_POST['level'];
            $extern = (isset($_POST['extern']) ? 1 : 0);
            $menuid = (int) $_POST['menuid'];
            $visible = (isset($_POST['visible']) ? 1 : 0);
            if ($name === '')
            {
                $name = $lang->unnamed;
            }
            if ($addr === '')
            {
                headerTo($info->modulSelf . '&status=no_address');
            }
            if ($type < 1 || $type > 5)
            {
                headerTo($info->modulSelf);
            }
            $query = 'SELECT count(`id`) as \'count\' FROM `PREFIX_menus` WHERE `menuid`=' . $menuid;
            $result = $db->GetData($query);
            $count = $result[0]->count;
            if ($position < 1 || $position > $count)
            {
                $position = $count + 1;
            }
            if ($level < 0)
            {
                $level = 0;
            }

            $query = "SELECT count(*) as 'count' FROM `PREFIX_menus` WHERE `position`=$position AND `menuid`=$menuid";
            $result = $db->GetData($query);
            if ($result[0]->count > 0)
            {
                $query = "UPDATE `PREFIX_menus` SET `position`=`position`+1 WHERE `position`>=$position";
                $db->PushData($query);
            }

            $query = 'INSERT INTO `PREFIX_menus` (`menuid`,`type`,`name`,`address`,`position`,`level`,`visible`,`extern`) VALUES ';
            $query .= "($menuid, $type, ?, ?, $position, $level, $visible, $extern)";
            $types = 'ss';
            $param_arr = array(
                $name,
                $addr
            );
            $db->PushData($query, $types, $param_arr);
            headerTo($info->modulSelf . '&status=added&menuid=' . $menuid);
        }
        if (!isset($_POST['type']) || !Text::is_numeric($_POST['type']))
        {
            headerTo($info->modulSelf);
        }
        $type = (int) $_POST['type'];
        if ($type > 0 && $type <= 5)
        {
            $tpl = new Template('navi_type' . $type, $lang);
            $params = array(
                'THIS' => $info->modulSelf,
                'DO' => 'add=new',
                'NAME' => '',
                'ADDR' => '',
                'VISIBLE-TRUE' => ' selected="selected"',
                'VISIBLE-FALSE' => '',
                'LEVEL' => '0',
                'POS' => '',
                'MENU1' => '',
                'MENU2' => '',
                'MENU3' => '',
                'MENU4' => '',
                'MENU5' => '',
                'EXTERN' => '',
                'LEGEND' => $lang->{'new' . $type}
            );
            if ($type == 2 || $type == 3 || $type == 4)
            {
                switch ($type)
                {
                    case 2:
                        $options = list_moduls();
                        if (count($options) == 0)
                        {
                            headerTo($info->modulSelf . '&status=no_available');
                        }
                        break;
                    case 3:
                        $options = list_selfs();
                        if (count($options) == 0)
                        {
                            headerTo($info->modulSelf . '&status=no_available');
                        }
                        break;
                    case 4:
                        $options = list_boxes();
                        if (count($options) == 0)
                        {
                            headerTo($info->modulSelf . '&status=no_available');
                        }
                        break;
                }
                $modulsStr = '';
                foreach($options as $value => $option)
                {
                    $modulsStr .= '<option value="' . $value . '">' . ucfirst($option) . '</option>';
                }
                $params['ADDR'] = $modulsStr;
            }

            $tpl->setParams($params);
            $tpl->printPart(0, true);
        }
    }
    elseif (!is_null($info->modulParams('del')))
    {
        user::hasRight('navi_del') or headerTo($info->modulSelf . '&status=access_denied');

        if (isset($_POST['confirmation']))
        {
            if ($_POST['confirm'] == 'no')
            {
                headerTo($info->modulSelf . '&menuid=' . $menuid);
            }
            elseif ($_POST['confirm'] == 'yes')
            {
                $query = 'SELECT `position` FROM `PREFIX_menus` WHERE `id`=' . (int) $info->modulParams('del') . ' LIMIT 1';
                $result = $db->GetData($query);
                $posi = $result[0]->position;
                $query = 'DELETE FROM `PREFIX_menus` WHERE `menuid`= ' . $menuid . ' AND `id`=' . (int) $info->modulParams('del');
                $db->PushData($query);
                $query = 'UPDATE `PREFIX_menus` SET `position`=`position`-1 WHERE `menuid`= ' . $menuid . ' AND `position` >=' . $posi;
                $db->PushData($query);
                headerTo($info->modulSelf . '&status=deleted&menuid=' . $menuid);
            }
            else
            {
                headerTo($info->modulSelf);
            }
        }

        $tpl = new Template('confirm', $lang);
        $params = array(
            'THIS' => $info->modulSelf . '&amp;del=' . (int) $info->modulParams('del') . '&amp;menuid=' . $menuid,
            'LEGEND' => $lang->sure2delete
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    elseif (!is_null($info->modulParams('edit')) && !is_null($info->modulParams('id')) && !is_null($info->modulParams('menuid')))
    {
        if ($info->modulParams('edit') == 'visible')
        {
            user::hasRight('navi_edit') or headerTo($info->modulSelf . '&status=access_denied');
            
            $query = 'SELECT `visible` FROM `PREFIX_menus` WHERE `id`=' . $id;
            $result = $db->GetData($query);
            if ($db->affected_rows == 0)
            {
                headerTo($info->modulSelf . '&menuid=' . $menuid);
            }
            $query = 'UPDATE `PREFIX_menus` SET `visible`=';
            $query .= ($result[0]->visible == 1) ? '0' : '1';
            $query .= ' WHERE `id`=' . $id;
            $db->PushData($query);
            headerTo($info->modulSelf . '&menuid=' . $menuid . '&status=visible_updated');
        }
        elseif ($info->modulParams('edit') == 'extern')
        {
            user::hasRight('navi_edit') or headerTo($info->modulSelf . '&status=access_denied');

            $query = 'SELECT `type` FROM `PREFIX_menus` WHERE `id`=' . $id . ' LIMIT 1';
            $result = $db->GetData($query);
            if ($db->affected_rows == 0)
            {
                headerTo($info->modulSelf . '&menuid=' . $menuid);
            }
            $result = &$result[0];
            if ($result->type == 1 || $result->type == 4)
            {
                headerTo($info->modulSelf . '&menuid=' . $menuid);
            }

            $query = 'SELECT `extern` FROM `PREFIX_menus` WHERE `id`=' . $id;
            $result = $db->GetData($query);
            $query = 'UPDATE `PREFIX_menus` SET `extern`=';
            $query .= ($result->extern == 1 ? '0' : '1');
            $query .= ' WHERE `id`=' . $id;
            $db->PushData($query);
            headerTo($info->modulSelf . '&menuid=' . $menuid . '&status=extern_updated');
        }
        elseif ($info->modulParams('edit') == 'pos_up')
        {
            user::hasRight('navi_move') or headerTo($info->modulSelf . '&status=access_denied');

            $query = 'SELECT `position` FROM `PREFIX_menus` WHERE `id`=' . $id;
            $posi = $db->GetData($query);
            if ($db->affected_rows == 0)
            {
                headerTo($info->modulSelf . '&menuid=' . $menuid);
            }
            if ($posi[0]->position <= 1)
            {
                headerTo($info->modulSelf . '&menuid=' . $menuid);
            }
            
            $query = 'UPDATE `PREFIX_menus` SET `position`=0 WHERE `id`=' . $id;
            $db->PushData($query);
            $query = "UPDATE `PREFIX_menus` SET `position`=`position`+1 WHERE `menuid`=$menuid AND `position`=" . --$pos;
            $db->PushData($query);
            $query = "UPDATE `PREFIX_menus` SET `position`=$pos WHERE `id`=$id";
            $db->PushData($query);
            headerTo($info->modulSelf . '&menuid=' . $menuid . '&status=pos_upped');
        }
        elseif ($info->modulParams('edit') == 'pos_down')
        {
            user::hasRight('navi_move') or headerTo($info->modulSelf . '&status=access_denied');

            $query = 'SELECT count(*) AS \'count\' FROM `PREFIX_menus` WHERE `menuid`=' . $menuid;
            $count = $db->GetData($query);
            if ($db->affected_rows == 0)
            {
                headerTo($info->modulSelf);
            }
            if ($pos >= $count[0]->count)
            {
                headerTo($info->modulSelf . '&menuid=' . $menuid);
            }
            $query = 'UPDATE `PREFIX_menus` SET `position`=0 WHERE `id`=' . $id;
            $db->PushData($query);
            $query = "UPDATE `PREFIX_menus` SET `position`=`position`-1 WHERE `menuid`=$menuid AND `position`=" . ++$pos;
            $db->PushData($query);
            $query = "UPDATE `PREFIX_menus` SET `position`=$pos WHERE `id`=$id";
            $db->PushData($query);
            headerTo($info->modulSelf . '&menuid=' . $menuid . '&status=pos_downed');
        }
        elseif ($info->modulParams('edit') == 'level_up')
        {
            user::hasRight('navi_move') or headerTo($info->modulSelf . '&status=access_denied');
            
            $query = 'SELECT `type` FROM `PREFIX_menus` WHERE `id`=' . $id . ' LIMIT 1';
            $result = $db->GetData($query);
            if ($db->affected_rows == 0)
            {
                headerTo($info->modulSelf . '&menuid=' . $menuid);
            }
            $result = &$result[0];
            if ($result->type == 1 || $result->type == 4)
            {
                headerTo($info->modulSelf . '&menuid=' . $menuid);
            }
            $query = 'UPDATE `PREFIX_menus` SET `level`=`level`+1 WHERE `id`=' . $id . ' LIMIT 1';
            $db->PushData($query);

            headerTo($info->modulSelf . '&menuid=' . $menuid . '&status=level_upped');
        }
        elseif ($info->modulParams('edit') == 'level_down')
        {
            user::hasRight('navi_move') or headerTo($info->modulSelf . '&status=access_denied');

            $query = 'SELECT `level`,`type` FROM `PREFIX_menus` WHERE `id`=' . $id . ' LIMIT 1';
            $result = $db->GetData($query);
            if ($db->affected_rows == 0)
            {
                headerTo($info->modulSelf . '&menuid=' . $menuid);
            }
            $result = &$result[0];
            if ($result->type == 1 || $result->type == 4)
            {
                headerTo($info->modulSelf . '&menuid=' . $menuid);
            }
            if (((int) $result->level - 1) < 0)
            {
                headerTo($info->modulSelf . '&menuid=' . $menuid);
            }
            $query = 'UPDATE `PREFIX_menus` SET `level`=`level`-1 WHERE `id`=' . $id . ' LIMIT 1';
            $db->PushData($query);

            headerTo($info->modulSelf . '&menuid=' . $menuid . '&status=level_downed');
        }
        elseif ($info->modulParams('edit') == 'full')
        {
            user::hasRight('navi_edit') or headerTo($info->modulSelf . '&status=access_denied');
            
            if (isset($_POST['post']))
            {
                $name = htmlspecialchars(trim($_POST['name']));
                $addr = htmlspecialchars(trim($_POST['addr']));
                $type = (int) $_POST['type'];
                $position = (int) $_POST['pos'];
                $level = (int) $_POST['level'];
                $extern = (isset($_POST['extern']) ? 1 : 0);
                $menuid = (int) $_POST['menuid'];
                $visible = (isset($_POST['visible']) ? 1 : 0);
                if (trim($name) === '')
                {
                    $name = $lang->unnamed;
                }
                if (trim($addr) === '')
                {
                    headerTo($info->modulSelf . '&status=no_address&menuid=' . $menuid);
                }
                if ($type < 1 || $type > 5)
                {
                    headerTo($info->modulSelf . '&menuid=' . $menuid);
                }
                $query = 'SELECT count(`id`) as \'count\' FROM `PREFIX_menus` WHERE `menuid`=' . $menuid;
                $result = $db->GetData($query);
                $count = $result[0]->count;
                if ($position < 1 || $position > $count)
                {
                    $position = $count + 1;
                }
                if ($level < 0)
                {
                    $level = 0;
                }

                $query = "SELECT count(*) as 'count' FROM `PREFIX_menus` WHERE `position`=$position AND `menuid`=$menuid";
                $result = $db->GetData($query);
                if ($result[0]->count > 0)
                {
                    $query = 'SELECT `position` FROM `PREFIX_menus` WHERE `id`=' . $id . ' LIMIT 1';
                    $result = $db->GetData($query);
                    $posi = $result[0]->position;
                    $op0 = ($position < $posi ? '+' : '-');
                    $op1 = ($position < $posi ? '>=' : '<=');
                    $op2 = ($position < $posi ? '<' : '>');
                    $query = "UPDATE `PREFIX_menus` SET `position`=`position`{$op0}1 WHERE `position`{$op1}{$position} AND `position`{$op2}{$posi}";
                    $db->PushData($query);
                }

                $query = 'UPDATE `PREFIX_menus` SET ';
                $query .= "`menuid`=$menuid, ";
                $query .= "`name`=?, ";
                $query .= "`address`=?, ";
                $query .= "`position`=$position, ";
                $query .= "`level`=$level, ";
                $query .= "`visible`=$visible, ";
                $query .= "`extern`=$extern ";
                $query .= 'WHERE `id`=' . $id . ' LIMIT 1';
                $types = 'ss';
                $param_arr = array(
                    $name,
                    $addr
                );
                $db->PushData($query, $types, $param_arr);
                headerTo($info->modulSelf . '&status=edited&menuid=' . $menuid);
            }
            $query = 'SELECT `name`,`address`,`menuid`,`type`,`level`,`position`,`visible`,`extern` FROM `PREFIX_menus` WHERE `id`=' . $id;
            $result = $db->GetData($query);
            if ($db->affected_rows == 0)
            {
                headerTo($info->modulSelf . '&menuid=' . $menuid);
            }
            $result = &$result[0];
            $type = &$result->type;
            $tpl = new Template('navi_type' . $type, $lang);
            $params = array(
                'THIS' => $info->modulSelf,
                'DO' => 'edit=full&amp;id=' . $id . '&amp;menuid=' . $menuid,
                'NAME' => $result->name,
                'VISIBLE-TRUE' => '',
                'VISIBLE-FALSE' => '',
                'LEVEL' => $result->level,
                'POS' => $result->position,
                'MENU1' => '',
                'MENU2' => '',
                'MENU3' => '',
                'MENU4' => '',
                'MENU5' => '',
                'EXTERN' => $result->extern ? 'checked="checked"' : '',
                'LEGEND' => $lang->{'edit' . $type}
            );
            $params['MENU' . $result->menuid] = 'selected="selected"';
            $params['VISIBLE-' . ($result->visible ? 'TRUE' : 'FALSE')] = 'selected="selected"';
            if ($type == 2 || $type == 3 || $type == 4)
            {
                switch ($type)
                {
                    case 2:
                        $options = list_moduls($result->address);
                        if (count($options) == 0)
                        {
                            headerTo($info->modulSelf . '&status=no_available&menuid=' . $menuid);
                        }
                        break;
                    case 3:
                        $options = list_selfs($result->address);
                        if (count($options) == 0)
                        {
                            headerTo($info->modulSelf . '&status=no_available&menuid=' . $menuid);
                        }
                        break;
                    case 4:
                        $options = list_boxes($result->address);
                        if (count($options) == 0)
                        {
                            headerTo($info->modulSelf . '&status=no_available&menuid=' . $menuid);
                        }
                        break;
                }
                $modulsStr = '';
                foreach($options as $value => $option)
                {
                    $modulsStr .= '<option value="' . $value . '">' . ucfirst($option) . '</option>';
                }
                $params['ADDR'] = $modulsStr;
            }
            else
            {
                $params['ADDR'] = $result->address;
            }

            $tpl->setParams($params);
            $tpl->printPart(0, true);
        }
        else
        {
            headerTo($info->modulSelf . '&menuid=' . $menuid);
        }
    }
    else
    {
        if ($menuid === 0)
        {
            $menuid = 1;
        }
        $tpl = new Template('navi', $lang);
        $params = array(
            'STATUS' => $info->statusMessage($lang),
            'THIS' => $info->modulSelf
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);

        $query = 'SELECT * FROM `PREFIX_menus` WHERE `menuid`=' . $menuid . ' ORDER BY `position`';
        $result = $db->GetData($query);
        foreach ($result as $index => &$row)
        {
            $params = array(
                'ID' => $row->id,
                'POS' => $row->position,
                'MENUID' => $row->menuid,
                'NAME' => $row->name,
                'ADDR' => $row->address,
                'LEVEL' => $row->level,
                'VISIBLE' => ($row->visible == 1 ? $lang->Yes : $lang->No),
                'EXTERN' => ($row->extern == 1 ? $lang->Yes : $lang->No),
                'THIS' => $info->modulSelf
            );
            if ($row->type == 1)
            {
                $params['NAME'] = '<span style="font-weight:bolder;">' . $params['NAME'] . '</span>';
                $params['ADDR'] = '<span style="font-weight:bolder;">' . $lang->menu . '</span>';
                $params['LEVEL'] = '-';
                $params['EXTERN'] = '-';
            }
            elseif ($row->type == 2)
            {
                $params['ADDR'] = 'Modul:' . $params['ADDR'];
            }
            elseif ($row->type == 3)
            {
                $params['ADDR'] = 'Self:' . $params['ADDR'];
            }
            elseif ($row->type == 4)
            {
                $params['NAME'] = '<span style="font-weight:bolder;">' . $params['NAME'] . '</span>';
                $params['ADDR'] = '<span style="font-weight:bolder;">Box:' . $params['ADDR'] . '</span>';
                $params['LEVEL'] = '-';
                $params['EXTERN'] = '-';
            }
            elseif ($row->type == 5)
            {
                $trimmed = mb_substr($params['ADDR'], 0, 20);
                $trimmed = $trimmed . (mb_strlen($params['ADDR']) > mb_strlen($trimmed) ? '...' : '');
                $params['ADDR'] = '<span title="' . $params['ADDR'] . '">' . $trimmed . '</span>';
            }
            $params['STYLE'] = 'tablerow' . ($index % 2 == 0 ? '1' : '2');

            $tpl->setParams($params);
            $tpl->printPart(1, true);
        }

        $params['THIS'] = $info->modulSelf;
        $tpl->setParams($params);
        $tpl->printPart(2, true);
    }

    $design->printEnd();
?>
