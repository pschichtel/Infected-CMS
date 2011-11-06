<?php
    $tpl = new Template('smiles');
    $lang = new Lang($info->modul);

    $params = array(
        'TITLE' => $cfg->cms_title . ' :: ' . $lang->title,
        'DESIGN-DIR' => 'include/designs/' . $info->design,
        'TEXTAREA' => $info->modulParams('target')
    );
    $tpl->setParams($params);
    $tpl->printPart(0, true);

    $query = 'SELECT `smile`,`file` FROM `PREFIX_smiles`';
    $result = $db->GetData($query);

    foreach ($result as $row)
    {
        $params = array(
            'FILE' => $row->file,
            'SMILE' => $row->smile
        );
        $tpl->setParams($params);
        $tpl->printPart(1, true);
    }

    $tpl->printPart(2);
?>
