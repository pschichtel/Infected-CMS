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
    
    $tpl = new Template('overview', $lang);
    $tpl->printPart(0);

    $query = 'SELECT DISTINCT `modul` FROM `PREFIX_overview` ORDER BY `modul`';
    $result = $db->GetData($query);
    foreach ($result as &$row)
    {
        $moduls[] = &$row->modul;
    }

    foreach ($moduls as &$modul)
    {
        $query = 'SELECT * FROM `PREFIX_overview` WHERE `modul`=\'' . $modul . '\' ORDER BY `langindex` DESC';
        $result = $db->GetData($query);
        $infos = '';
        foreach ($result as &$row)
        {
            $value = $db->query($row->dataquery, false);
            $value = $db->fetch_row($value);
            $infos .= '<span>' . $lang->{$row->langindex} . ':</span> ' . $value[0] . "<br />\n";
        }
        $params = array(
            'INFOS' => $infos,
            'LEGEND' => $modulLang->{'m_' . $modul}
        );
        $tpl->setParams($params);
        $tpl->printPart(1, true);
    }
    $tpl->printPart(2);
?>
