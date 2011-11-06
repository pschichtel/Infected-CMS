<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    $title = 'CP Template Installer';
    $design = new Design($cfg->cms_title . ' :: ' . $title, $title);
    $design->printBegin();

    if (isset($_POST['post']))
    {
        require_once 'include/includes/classes/http.class.php';
    }
    else
    {
        $tpl = new Template('cptplinstall');
        $tpl->printPart(0);
    }

    $design->printEnd();
?>
