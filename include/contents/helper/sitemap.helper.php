<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    $tpl = new Template('xmlsitemap');
    $tpl->printPart(0);

    $query = 'SELECT `address`,`type` FROM `PREFIX_menus` WHERE `visible`=1 AND (`type`=2 OR `type`=3) ORDER BY `name` ASC';
    $result = $db->GetData($query);

    $base = 'http://' . $_SERVER['SERVER_NAME'];
    
    $params = array('LOC' => $base . '/');
    $tpl->setParams($params);
    $tpl->printPart(1, true);
    foreach ($result as $row)
    {
        if ($row->type == 3)
        {
            $params = array('LOC' => $base . SEO::makeAddress('self', array('cid' => $row->address), true));
        }
        else
        {
            $params = array('LOC' => $base . SEO::makeAddress($row->address, array(), true));
        }
        $tpl->setParams($params);
        $tpl->printPart(1, true);
    }
    $tpl->printPart(2);
    $log->write(4, 'info', 'referer: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'no referer given'));
    header('Content-type: application/xml');
?>
