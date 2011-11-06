<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    $lang = new Lang('date');
    $tpl = new Template('date', $lang, 2);
    $date = date('j. ');
    $date .= $lang->{mb_strtolower(date('F'))};
    $date .= date(' Y');
    $tpl->setParams(array('TODAY_IS' => $lang->today_is, 'DATE' => $date));
    $tpl->printPart(0, true);
?>
