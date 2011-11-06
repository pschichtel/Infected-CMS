<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');

    $lang = new Lang($info->modul);
    $title = $cfg->cms_title . ' :: ' . $lang->imprint;
    $design = new Design($title, $lang->imprint);
    $design->printBegin();

    $query = 'SELECT `parsedimprint`, `parsedliability` FROM `PREFIX_imprint`';
    $result = $db->GetData($query);
    $row = $result[0];

    $params = array(
        'IMPRINT' => $row->parsedimprint,
        'LIABILITY' => $row->parsedliability
    );

    $tpl = new Template('imprint', $lang);
    $tpl->setParams($params);
    $tpl->printPart(0, true);

    $design->printEnd();
?>
