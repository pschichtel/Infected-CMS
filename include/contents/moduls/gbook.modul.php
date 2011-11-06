<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');

    $lang = new Lang($info->modul);
    $title = $cfg->cms_title . ' :: ' . $lang->gbook;
    $design = new Design($title, $lang->gbook);
    $design->printBegin();

    $msg = '';
    $author = '';
    $email = '';
    $title = '';
    $text = '';

    if (!IPBase::checkLock('gbook') && isset($_POST['post']))
    {
        if (trim($_POST['author']) === '')
        {
            $msg .= ' - ' . $lang->err_name . '<br />';
        }
        elseif (mb_strlen(trim($_POST['author']), 'UTF-8') > 40)
        {
            $msg .= ' - ' . $lang->err_name2long . '<br />';
        }
        if (trim($_POST['email']) !== '' && !Text::is_email($_POST['email']))
        {
            $msg .= ' - ' . $lang->err_email . '<br />';
        }
        elseif (mb_strlen(trim($_POST['email']), 'UTF-8') > 60)
        {
            $msg .= ' - ' . $lang->err_email2long . '<br />';
        }
        if (trim($_POST['text']) === '')
        {
            $msg .= ' - ' . $lang->err_text . '<br />';
        }
        elseif (mb_strlen(trim($_POST['text']), 'UTF-8') > $cfg->gbook_textlen)
        {
            $msg .= ' - ' . $lang->err_text2long . '<br />';
        }
        if (!isset($_SESSION['ci_captcha']))
        {
            $msg .= ' - ' . $lang->err_captchafail . '<br />';
        }
        elseif ($_SESSION['ci_captcha'] !== mb_strtolower(trim($_POST['captcha'])))
        {
            $msg .= ' - ' . $lang->err_captcha . '<br />';
        }

        if ($msg  == '')
        {
            $author = htmlspecialchars(Text::simpleChunk($_POST['author']));
            $email = htmlspecialchars($_POST['email']);
            $text = htmlentities($_POST['text']);
            $ptext = Text::parse($_POST['text'], true, true, true, array('list', 'code', 'video', 'quote'));

            $msg = $lang->added;

            $query = 'INSERT INTO `PREFIX_gbook` (`author`,`email`,`rawtext`,`parsedtext`,`date`,`ip`) VALUES (?,?,?,?,NOW(),?)';
            $types = 'sssss';
            $param_arr = array($author, $email, $text, $ptext, IPBase::getIP());

            $db->PushData($query, $types, $param_arr);
            IPBase::setLock('gbook', $cfg->gbook_lock);
            $author = '';
            $email = '';
            $text = '';
        }
        else
        {
            $msg = $lang->err . ':<br />' . $msg;
            $author = ($_POST['author']) ? $_POST['author'] : '';
            $email = ($_POST['email']) ? $_POST['email'] : '';
            $text = ($_POST['text']) ? $_POST['text'] : '';
        }
    }
    elseif (isset($_POST['post']))
    {
        $msg = $lang->lock;
    }
    
    $tpl = new Template('gbook', $lang);

    $params = array(
        'MSG' => $msg,
        'THIS' => $info->modulSelf,
        'AUTHOR' => $author,
        'EMAIL' => $email,
        'TITLE' => $title,
        'TEXT' => $text
    );
    $tpl->setParams($params);
    $tpl->printPart(0, true);

    $page = (int) $info->modulParams('page');
    $page = ($page > 0 ? $page : 1);

    $offset = ($page - 1) * $cfg->gbook_pps;
    $query = 'SELECT `author`,`email`,DATE_FORMAT(`date`,\'%d.%c.%Y\') AS \'date\',`parsedtext` FROM `PREFIX_gbook` ORDER BY `id` DESC LIMIT ' . $offset . ', ' . $cfg->gbook_pps;

    $result = $db->GetData($query);
    $rows = $db->CountTable('gbook');
    $pages_count = ceil($rows / $cfg->gbook_pps);
    $pages_count = ($pages_count == 0 ? 1 : $pages_count);

    if ($result)
    {
        foreach ($result as &$row)
        {
            $author = &$row->author;
            if ($row->email)
            {
                $author = '<a href="' . Text::entityEncode('mailto:' . $row->email) . '?subject=' . rawurlencode($cfg->gbook_email_subject) . '">' . $author . '</a>';
            }
            $params = array(
                'TEXT' => $row->parsedtext,
                'AUTHOR' => $author,
                'DATE' => $lang->posted_at($row->date)
            );
            $tpl->setParams($params);
            $tpl->printpart(1, true);
        }
    }

    $next_page = '&nbsp;';
    if ($page < $pages_count)
    {
         $next_page = '<a href="' . SEO::makeAddress($info->modul, array('page' => $page + 1), true) . '">' . $lang->older . '</a>';
    }

    $prev_page = '&nbsp;';
    if ($page > 1)
    {
        $prev_page = '<a href="' . SEO::makeAddress($info->modul, array('page' => $page - 1), true) . '">' . $lang->newer . '</a>';
    }

    $params = array(
        'PAGE-B' => $prev_page,
        'PAGE-F' => $next_page,
        'PAGE' => $page,
        'PAGES-C' => $pages_count
    );

    $tpl->setParams($params);
    $tpl->printPart(2, true);

    $design->printEnd();
?>
