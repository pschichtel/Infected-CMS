<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');

    $lang = new Lang($info->modul);
    $title = $cfg->cms_title . ' :: ' . $lang->drive;
    $design = new Design($title, $lang->drive);

    $query = 'SELECT `key`,`lang`,`type`,`zoom`,`posl`,`posb`,`markertext` FROM `PREFIX_drive`';
    $result = $db->GetData($query);
    $result = $result[0];

    $params = array(
        'GOOGLE-MAP-API'    => 'http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $result->key . '&amp;hl=' . $result->lang,
        'POSL'              => $result->posl,
        'POSB'              => $result->posb,
        'ZOOM'              => $result->zoom,
        'INFOTEXT'          => '<div>' . str_replace("\n", '', Text::parse($result->markertext)) . '</div>',
        'MAPTYPE'           => $result->type
    );

    $tpl = new Template('drive', $lang);
    $tpl->setParams($params);
    $design->addToBody($tpl->getPart(1, true));
    $design->printBegin();

    $tpl->printPart(0);

    $design->printEnd();
?>
