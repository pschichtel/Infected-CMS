<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');

    if ($info->modulParams('cid'))
    {
        $cid = $info->modulParams('cid');
    }
    else
    {
        $cid = 0;
    }
    $query = 'SELECT `name`, `content`, `html`, `bbcode`, `smiles` FROM `PREFIX_selfcontent` WHERE `id`=' . $cid;
    $result = $db->GetData($query);
    if ($db->affected_rows == 0)
    {
        headerTo(SEO::makeAddress($cfg->cms_std_modul));
    }
    $title = $cfg->cms_title . ' :: ' . $result[0]->name;
    $design = new Design($title, $result[0]->name);
    $design->printBegin();
    
    $html = (bool) $result[0]->html;
    $bbcode = (bool) $result[0]->bbcode;
    $smiles = (bool) $result[0]->smiles;

    echo Text::parse($result[0]->content, true, $bbcode, $smiles, array(), $html);

    $design->printEnd();
?>
