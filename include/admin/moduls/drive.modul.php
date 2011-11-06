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
    if (isset($_POST['edit']))
    {
        user::hasRight('drive_edit') or headerTo($info->modulSelf . '&status=access_denied');

        if (trim($_POST['key']) === '')
        {
            $msg .= ' - ' . $lang->err_key . '<br />';
        }
        elseif (mb_strlen(trim($_POST['key']), 'UTF-8') > 200)
        {
            $msg .= ' - ' . $lang->err_key2long . '<br />';
        }
        if (trim($_POST['lang']) === '')
        {
            $msg .= ' - ' . $lang->err_lang . '<br />';
        }
        elseif (mb_strlen(trim($_POST['lang']), 'UTF-8') > 5)
        {
            $msg .= ' - ' . $lang->err_lang2long . '<br />';
        }
        if (trim($_POST['zoom']) === '')
        {
            $msg .= ' - ' . $lang->err_zoom . '<br />';
        }
        elseif (mb_strlen(trim($_POST['zoom']), 'UTF-8') > 6)
        {
            $msg .= ' - ' . $lang->err_zoom2long . '<br />';
        }
        if (trim($_POST['posl']) === '')
        {
            $msg .= ' - ' . $lang->err_posl . '<br />';
        }
        elseif (mb_strlen(trim($_POST['posl']), 'UTF-8') > 30)
        {
            $msg .= ' - ' . $lang->err_posl2long . '<br />';
        }
        if (trim($_POST['posb']) === '')
        {
            $msg .= ' - ' . $lang->err_posb . '<br />';
        }
        elseif (mb_strlen(trim($_POST['posb']), 'UTF-8') > 30)
        {
            $msg .= ' - ' . $lang->err_posb2long . '<br />';
        }
        if (trim($_POST['markertext']) === '')
        {
            $msg .= ' - ' . $lang->err_markertext . '<br />';
        }

        if ($msg === '')
        {
            $query = 'UPDATE `PREFIX_drive` SET `key`=?, `lang`=?, `zoom`=?, `posl`=?, `posb`=?, `type`=?, `markertext`=?';
            $types = 'ssissss';
            $param_arr = array(
                htmlspecialchars($_POST['key']),
                htmlspecialchars($_POST['lang']),
                htmlspecialchars($_POST['zoom']),
                htmlspecialchars($_POST['posl']),
                htmlspecialchars($_POST['posb']),
                htmlspecialchars($_POST['type']),
                $_POST['markertext']
            );
            $db->PushData($query, $types, $param_arr);
            $msg = $lang->updated;
        }
        else
        {
            $msg = $lang->err . ':<br />' . $msg;
        }
    }
    $result = $db->GetData('SELECT `key`,`lang`,`type`,`zoom`,`posl`,`posb`,`markertext` FROM `PREFIX_drive`');
    $result = $result[0];

    $options = array(
        'G_NORMAL_MAP' => $lang->G_NORMAL_MAP,
        'G_HYBRID_MAP' => $lang->G_HYBRID_MAP,
        'G_SATELLITE_MAP' => $lang->G_SATELLITE_MAP
    );
    $option_s = '<option value="' . $result->type . '">' . $options[$result->type] . '</option>' . "\n";
    foreach ($options as $index => $option) {
        if ($index != $result->type) {
            $option_s .= '<option value="' . $index . '">' . $option . '</option>' . "\n";
        }
    }

    $params = array(
        'MSG' => ($msg ? $msg : $info->statusMessage($lang)),
        'THIS' => $info->modulSelf,
        'KEY' => ($msg ? $_POST['key'] : $result->key),
        'LANG' => ($msg ? $_POST['lang'] : $result->lang),
        'ZOOM' => ($msg ? $_POST['zoom'] : $result->zoom),
        'POSL' => ($msg ? $_POST['posl'] : $result->posl),
        'POSB' => ($msg ? $_POST['posb'] : $result->posb),
        'MARKERTEXT' => ($msg ? $_POST['markertext'] : $result->markertext),
        'OPTIONS' => $option_s
    );
    $tpl = new Template('drive', $lang);
    $tpl->setParams($params, false);
    $tpl->printPart(0, true);

    $design->printEnd();
?>
