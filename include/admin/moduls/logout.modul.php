<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');

    if (!user::loggedIn())
    {
        headerTo('admin.php?modul=login');
    }
    
    $_SESSION['ci_adminlogin'] = false;
    
    $lang = new Lang($info->modul);
    $design = new Design();
    $design->addToHead('<meta http-equiv="refresh" content="4;url=./' . CI_INDEXFILE . '" />');
    $design->printBegin();

    $tpl = new Template('logout', $lang);
    $tpl->printPart(0);

    $design->printEnd();
?>
