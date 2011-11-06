<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');
    if (!user::loggedIn())
    {
        user::backToLogin();
    }

    $lang = new Lang($info->modul);
    $msg = '';
    if (isset($_POST['edit']))
    {
        user::hasRight('config_edit') or headerTo($info->modulSelf . '&status=access_denied', true);

        unset($_POST['edit']);
        foreach ($_POST as $index => $value)
        {
            if ($value === '' || mb_strlen($value, 'UTF-8') > 500)
            {
                $msg = $lang->error;
            }
        }
        if ($msg == '')
        {
            foreach ($_POST as $index => &$value)
            {
                $query = 'UPDATE `PREFIX_config` SET `value` = ? WHERE `index` = ? LIMIT 1';
                $types = 'ss';
                $params = array(htmlspecialchars($value), $index);
                $db->PushData($query, $types, $params);
            }
            $msg = $lang->changed;
            $cfg->GetConfig();
        }
    }

    $modulLang = new Lang('moduls');
    $design = new Design();
    $design->printBegin();
    
    $tpl = new Template('config', $lang);
    $params['MSG'] = ($msg ? $msg : $info->statusMessage($lang));
    $params['THIS'] = $info->modulSelf;
    $tpl->setParams($params);
    $tpl->printPart(0, true);

    $query = 'SELECT DISTINCT `modul` FROM `PREFIX_config` WHERE NOT `mode`=\'none\' ORDER BY `modul`';
    $result = $db->GetData($query);

    foreach ($result as $row)
    {
            $moduls[] = $row->modul;
    }

    foreach ($moduls as $modul) {
        $query = 'SELECT * FROM `PREFIX_config` WHERE `modul`=\'' . $modul . '\'';
        $result = $db->GetData($query);
        $inputs = '';
        foreach ($result as $row)
        {
            $tmp = '<label for="' . $row->index . '">' . $lang->{'cfg_' . $row->index} . ': </label>' . "\n";
            list($mode, $type) = explode('|', $row->mode);
            if ($mode == 'input')
            {
                $tmp .= '<input type="' . $type . '" name="' . $row->index . '" id="' . $row->index . '" value="' . $cfg->{$row->index} . '" /><br />' . "\n";
            }
            elseif ($mode == 'select')
            {
                list($dMode, $dString) = explode(':', $type);
                if ($dMode == 'callback')
                {
                    
                    $data = array();
                    eval('$data = ' . $dString . ';');
                }
                else
                {
                    $dString = mb_substr($dString, 1, mb_strlen($dString) - 2);
                    $data = Text::explode2assoc(',', $dString);
                }

                $tmp .= '<select name="' . $row->index . '" id="' . $row->index . '">' . "\n";
                foreach ($data as $index => $value)
                {
                    $tmp .= '<option value="' . $index . '">' . ucfirst($value) . '</option>' . "\n";
                }
                $tmp .= '</select><br />' . "\n";
            }
            
             
                     
                     
            $inputs .= $tmp;
        }
        $params = array(
            'MSG' => $msg,
            'MODUL-NAME' => $modulLang->{'m_' . $modul},
            'INPUTS' => $inputs
        );
        $tpl->setParams($params);
        $tpl->printPart(1, true);
    }
    $tpl->printPart(2);

    $design->printEnd();
?>
