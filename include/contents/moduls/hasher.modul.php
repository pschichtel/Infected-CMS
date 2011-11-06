<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');

    $lang = new Lang($info->modul);
    $title = $cfg->cms_title . ' :: ' . $lang->hasher;
    $ctitle = $lang->hasher;
    $design = new Design($title, $ctitle);
    $design->printBegin();

    $tpl = new Template('hasher', $lang);
    $tpl->setParams(array('THIS' => $info->modulSelf));
    $tpl->printPart(0, true);
    
    if (isset($_POST['post']))
    {
        $text = &$_POST['text'];
        $log->write(4, 'info', 'hashed: ' . preg_replace('/(\n|\r)/si', '', $text));
        foreach (hash_algos() as $algo)
        {
            $params = array(
                'HASH' => hash($algo, $text),
                'ALGO' => $algo
            );
            $tpl->setParams($params);
            $tpl->printPart(1, true);
        }
    }

    $design->printEnd();
?>
