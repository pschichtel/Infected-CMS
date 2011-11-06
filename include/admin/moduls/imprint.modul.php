<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');
    if (!user::loggedIn())
    {
        user::backToLogin();
    }

    $lang = new Lang($info->modul);
    $design = new Design();
    $design->printBegin();

    $msg = '';
    if (isset($_POST['edit']))
    {
        user::hasRight('imprint_edit') or headerTo($info->modulSelf . '&status=access_denied');

        $imprint = htmlspecialchars($_POST['imprint']);
        $pimprint = Text::parse($_POST['imprint']);
        $liability = htmlspecialchars($_POST['liability']);
        $pliability = Text::parse($_POST['liability']);


        $query = 'UPDATE `PREFIX_imprint` SET `rawimprint`=?,`parsedimprint`=?,`rawliability`=?,`parsedliability`=? LIMIT 1';
        $types = 'ssss';
        $param_arr = array(
            $imprint,
            $pimprint,
            $liability,
            $pliability
        );
        $db->PushData($query, $types, $param_arr);
        $msg = $lang->updated;
    }

    $query = 'SELECT `rawimprint`,`rawliability` FROM `PREFIX_imprint` LIMIT 1';
    $result = $db->GetData($query);
    
    $tpl = new Template('imprint', $lang);

    $params = array(
        'MSG' => ($msg ? $msg : $info->statusMessage($lang)),
        'THIS' => $info->modulSelf,
        'IMPRINT' => $result[0]->rawimprint,
        'LIABILITY' => $result[0]->rawliability
    );
    $tpl->setParams($params);
    $tpl->printPart(0, true);

    $design->printEnd();
?>
