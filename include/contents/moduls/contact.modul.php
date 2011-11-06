<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');

    $lang = new Lang($info->modul);
    $title = $cfg->cms_title . ' :: ' . $lang->contact;
    $design = new Design($title, $lang->contact);
    $design->printBegin();

    $msg = '';
    $email = '';
    $target = '';
    $ref = '';
    $text = '';

    if (!IPBase::checkLock('contact') && isset($_POST['post']))
    {
        if (trim($_POST['email']) === '')
        {
            $msg .= ' - ' . $lang->err_emailmissing . '<br />';
        }
        elseif (!Text::is_email($_POST['email']))
        {
            $msg .= ' - ' . $lang->err_emailwrong . '<br />';
        }
        if (trim($_POST['target']) === '')
        {
            $msg .= ' - ' . $lang->err_target . '<br />';
        }
        if (trim($_POST['ref']) === '')
        {
            $msg .= ' - ' . $lang->err_subject . '<br />';
        }
        if (trim($_POST['text']) === '')
        {
            $msg .= ' - ' . $lang->err_text . '<br />';
        }
        elseif (mb_strlen(trim($_POST['text']), 'UTF-8') > $cfg->contact_textlen)
        {
            $msg .= ' - ' . $lang->err_text2long . '<br />';
        }
        if (!isset($_SESSION['ci_captcha']))
        {
            $msg .= ' - ' . $lang->err_captchafail . '<br />';
            $log->write(1, 'error', 'couldn\'t get the captcha-code');
        }
        elseif ($_SESSION['ci_captcha'] !== mb_strtolower(trim($_POST['captcha'])))
        {
            $msg .= ' - ' . $lang->err_captcha . '<br />';
            $log->write(4, 'info', 'captcha wrong (' . $_SESSION['ci_captcha'] . ' != ' . trim($_POST['captcha']) . ')');
        }

        if ($msg === '')
        {
            $query = 'SELECT `email` FROM `PREFIX_contact` WHERE `id`=' . (int) $_POST['target'];
            $result = $db->GetData($query);
            $email = htmlspecialchars($_POST['email']);
            $target = htmlspecialchars($result[0]->email);
            $ref = htmlspecialchars($_POST['ref']);
            $text = htmlspecialchars($_POST['text']);

            $tpl = new Template('mail_ext', $lang, 4);
            $tpl->setParams(array('IP' => IPBase::getIP()));

            $msg = $lang->sent;

            if (SMTP::sendmail($target, $email, $ref, $text . "\n\n\n" . $tpl->getPart(0, true)) == SMTP::OK)
            {
                IPBase::setLock('contact', $cfg->contact_lock);
                $log->write(4, 'info', "mail sent: receiver:'$target';ref:'$ref';email:'$email';IP:" . IPBase::getIP());

                $name = '';
                $ref = '';
                $email = '';
                $title = '';
                $text = '';
            }
            else
            {
                $log->write(4, 'error', "unknown error occurrered: receiver:'$target';ref:'$ref';email:'$email';IP:" . IPBase::getIP());
                $msg = $lang->unknown_err . ':<br />' . $msg;
            }
        }
        else
        {
            $msg = $lang->err . ':<br />' . $msg;
            $email = ($_POST['email']) ? $_POST['email'] : '';
            //$target = ($_POST['target']) ? $_POST['target'] : '';
            $ref = ($_POST['ref']) ? $_POST['ref'] : '';
            $text = ($_POST['text']) ? $_POST['text'] : '';
        }
    }
    elseif (isset($_POST['post']))
    {
        $msg = $lang->lock;
    }

    $tpl = new Template('contact', $lang);

    $params = array(
        'MSG' => $msg,
        'THIS' => $info->modulSelf,
        'EMAIL' => $email
    );
    $tpl->setParams($params);
    $tpl->printPart(0, true);
        
    $query = 'SELECT `id`,`name` FROM `PREFIX_contact`';
    $result = $db->GetData($query);

    if ($result)
    {
        foreach ($result as $row)
        {
            $params = array(
                'TARGET' => $row->id,
                'NAME' => $row->name
            );
            $tpl->setParams($params);
            $tpl->printPart(1, true);
        }
    }

    $params = array(
        'REF' => $ref,
        'TEXT' => $text
    );
    $tpl->setParams($params);
    $tpl->printPart(2, true);

    $design->printEnd();
?>
