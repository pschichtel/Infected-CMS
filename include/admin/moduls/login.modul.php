<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');
    if (user::loggedIn())
    {
        headerTo('admin.php?modul=' . urldecode($info->modulParams('referer') ? $info->modulParams('referer') : 'overview'));
    }
    $lang = new Lang($info->modul);

    $msg = '';
    $name = '';
    if (isset($_POST['post']))
    {
        if (trim($_POST['name']) === '')
        {
            $msg .= ' - ' . $lang->err_name . '<br />';
        }
        if ($_POST['pass'] === '')
        {
            $msg .= ' - ' . $lang->err_pass . '<br />';
        }

        if ($msg === '')
        {
            $query = 'SELECT `id`,`name`,`password`,`dynsalt`,`admingroup`,`loginfails`,`nextlogin` FROM `PREFIX_admins` WHERE `name`=?';
            $types = 's';
            $param_arr = array(mb_strtolower($_POST['name']));
            $result = $db->GetData($query, $types, $param_arr);
            if ($result)
            {
                if ($result[0]->nextlogin <= time())
                {
                    if ($result[0]->dynsalt . '|' . $result[0]->password === password($_POST['pass'], $result[0]->dynsalt . '|'))
                    {
                        $rights = $db->GetData('SELECT `rights` FROM `PREFIX_admingroups` WHERE `groupname`=\'' . $result[0]->admingroup . '\';');
                        $rights = explode('|', trim($rights[0]->rights, '|'));
                        
                        $_SESSION['ci_adminlogin'] = true;
                        $_SESSION['ci_adminid'] = (int) $result[0]->id;
                        $_SESSION['ci_adminname'] = $result[0]->name;
                        $_SESSION['ci_admingroup'] = $result[0]->admingroup;
                        $_SESSION['ci_adminrights'] = $rights;
                        $_SESSION['ci_userip'] = IPBase::getIP();
                        $_SESSION['ci_loginuseragent'] = user::useragent(true);
                        $_SESSION['ci_afk_since'] = time();

                        $query = 'UPDATE `PREFIX_admins` SET `loginfails`=0 WHERE `id`=' . $result[0]->id;
                        $db->PushData($query);

                        headerTo('admin.php?modul=' . urldecode(trim($_POST['referer']) !== '' ? $_POST['referer'] : 'overview'));
                    }
                    $deley = 0;
                    if ($result[0]->loginfails > 2)
                    {
                        $deley = 30;
                    }
                    if ($result[0]->loginfails > 5)
                    {
                        $deley = 300;
                    }
                    if ($result[0]->loginfails > 10)
                    {
                        $deley = 600;
                    }
                    $query = 'UPDATE `PREFIX_admins` SET `nextlogin`=' . (time() + $deley) . ',`loginfails`=`loginfails`+1 WHERE `id`=' . $result[0]->id;
                    $db->PushData($query);
                }
                else
                {
                    $msg = $lang->next_in($result[0]->nextlogin - time());
                }
            }
            $msg = $msg ? $msg : $lang->failed;
        }
        else
        {
            $msg = $lang->err . ':<br />' . $msg;
        }
    }

    $design = new Design();
    $design->printBegin();

    $params = array(
        'MSG' => $msg,
        'THIS' => $info->modulSelf,
        'REFERER' => (string) $info->modulParams('referer')
    );
    $tpl = new Template('login', $lang);
    $tpl->setParams($params);
    $tpl->printpart(0, true);

    $design->printEnd();
?>
