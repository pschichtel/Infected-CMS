<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');

    $lang = new Lang($info->modul);
    $title = $cfg->cms_title . ' :: ' . $lang->news;
    
    if (!is_null($info->modulParams('view')) && Text::is_numeric($info->modulParams('view')))
    {
        $id = $info->modulParams('view');
        $query = 'SELECT `title`,`rawtext`,`parsedtext`,`author`,`parsedtext`,DATE_FORMAT(`datetime`, \'%d.%c.%Y\') AS \'date\',DATE_FORMAT(`datetime`, \'%k:%i\') AS \'time\',`comments_allowed` FROM `PREFIX_news` WHERE `id`=? LIMIT 1';
        $types = 'i';
        $params = array($id);
        $news = $db->GetData($query, $types, $params);
        $tpl = new Template('news_view', $lang);

        $msg = '';
        $c_author = '';
        $c_text = '';
        if (count($news) > 0)
        {
            $news = $news[0];
            $title .= ' :: ' . $news->title;
            $design = new Design($title, $lang->news);
            $design->printBegin();


            if (isset($_POST['post']))
            {
                if (trim($_POST['c_author']) === '')
                {
                    $msg .= ' - ' . $lang->err_c_author_missing . '<br />';
                }
                elseif (mb_strlen($_POST['c_author']) > 50)
                {
                    $msg .= ' - ' . $lang->err_c_author_toolong(50) . '<br />';
                }
                if (trim($_POST['c_text']) === '')
                {
                    $msg .= ' - ' . $lang->err_c_text_missing . '<br />';
                }
                elseif (mb_strlen($_POST['c_text']) > $cfg->news_commentlength)
                {
                    $msg .= ' - ' . $lang->err_c_text_toolong($cfg->news_commentlength) . '<br />';
                }
                if (!isset($_SESSION['ci_captcha']))
                {
                    $msg .= ' - ' . $lang->err_captchafail . '<br />';
                }
                elseif ($_SESSION['ci_captcha'] !== mb_strtolower(trim($_POST['captcha'])))
                {
                    $msg .= ' - ' . $lang->err_captcha . '<br />';
                }
                if (isset($_COOKIE['news_' . $id . '_locked']))
                {
                    $msg = ' - ' . $lang->err_locked . '<br />';
                }
                if ($news->comments_allowed === '0')
                {
                    $msg = ' - ' . $lang->err_comments_disallwed . '<br />';
                }

                if ($msg === '')
                {
                    $c_author = htmlspecialchars(Text::simpleChunk($_POST['c_author']));
                    $c_text = $_POST['c_text'];
                    $c_ptext = Text::parse($_POST['c_text'], true, true, true);

                    $msg = $lang->comment_added;

                    Comments::add('news', $id, $c_author, $c_text, $c_ptext);

                    setcookie('news_' . $id . '_locked', 'true', time() + 60 * 60 * 3);
                    $c_author = '';
                    $c_text = '';
                }
                else
                {
                    $msg = $lang->err . ':<br />' . $msg;
                    $c_author = ($_POST['c_author']) ? $_POST['c_author'] : '';
                    $c_text = ($_POST['c_text']) ? $_POST['c_text'] : '';
                }
            }
            if (!is_null($info->modulParams('quote')) && Text::is_numeric($info->modulParams('quote')))
            {
                $comment = Comments::get('news', $id, $info->modulParams('quote'));
                $c_text = htmlspecialchars('[quote="' . $comment['author'] . '"]' . $comment['raw'] . '[/quote]');
            }
            
            $params = array(
                'AUTHOR' => $news->author,
                'DATE' => $news->date,
                'TIME' => $news->time,
                'TITLE' => $news->title,
                'TEXT' => $news->parsedtext,
                'SIGNATURE' => $lang->signature($news->author, $news->date, $news->time)
            );
            $tpl->setParams($params);
            $tpl->printPart(0, true);

            $disallowed = false;
            if ($news->comments_allowed === '1')
            {
                $params = array(
                    'ADD_ADDR' => SEO::makeAddress('news', array('view' => $id, 'add' => 'new'), true),
                    'AUTHOR' => $c_author,
                    'TEXT' => $c_text,
                    'STATUS' => $msg
                );
                $tpl->setParams($params);
                $tpl->printPart(1, true);
            }
            else
            {
                $disallowed = true;
                echo $lang->err_comments_disallwed . '<br />';
            }

            $count = Comments::countComments('news', $id);
            if ($count > 0)
            {
                $cpage = (int) $info->modulParams('cpage');
                $cpage = ($cpage > 0 ? $cpage : 1);

                $offset = ($cpage - 1) * $cfg->news_cps;
                $cpages = ceil($count / $cfg->news_cps);
                $cpages = ($cpages > 0 ? $cpages : 1);

                $comments = Comments::getRange('news', $id, $offset, $cfg->news_cps);

                foreach ($comments as &$comment)
                {
                    $params = array(
                        'AUTHOR' => $comment['author'],
                        'TEXT' => $comment['parsed'],
                        'QUOTE_ADDR' => SEO::makeAddress('news', array('view' => $id, 'quote' => $comment['guid']), true),
                        'DATE' => $lang->comment_posted_at($comment['date'])
                    );
                    $tpl->setParams($params);
                    $tpl->printPart(2, true);
                }

                $next_page = '&nbsp;';
                if ($cpage < $cpages)
                {
                     $next_page = '<a href="' . SEO::makeAddress($info->modul, array('view' => $id, 'page' => $cpage + 1), true) . '">' . $lang->older . '</a>';
                }

                $prev_page = '&nbsp;';
                if ($cpage > 1)
                {
                    $prev_page = '<a href="' . SEO::makeAddress($info->modul, array('view' => $id, 'page' => $cpage - 1), true)  . '">' . $lang->newer . '</a>';
                }
                $params = array(
                    'PAGE' => $cpage,
                    'PAGES-C' => $cpages,
                    'PAGE-B' => $prev_page,
                    'PAGE-F' => $next_page
                );
                $tpl->setParams($params);
                $tpl->printPart(3, true);
            }
            else
            {
                if (!$disallowed)
                {
                    $tpl->printPart(4);
                }
            }

            $tpl->printPart(5);
            $design->printEnd();
        }
        else
        {
            headerTo($info->modulSelf);
        }
    }
    else
    {
        $design = new Design($title, $lang->news);
        $design->printBegin();

        $page = (int) $info->modulParams('page');
        $page = ($page > 0 ? $page : 1);

        $offset = ($page - 1) * $cfg->news_pps;
        $query = 'SELECT `id`,`title`,`rawtext`,`parsedtext`,`author`,`parsedtext`,DATE_FORMAT(`datetime`, \'%d.%c.%Y\') AS \'date\',DATE_FORMAT(`datetime`, \'%k:%i\') AS \'time\',`shorten`'
               . ' FROM `PREFIX_news` ORDER BY `datetime` DESC LIMIT ' . $offset . ',' . $cfg->news_pps;
        $result = $db->GetData($query);

        $tpl = new Template('news', $lang);

        if ($result)
        {
            foreach($result as &$row)
            {
                $view = SEO::makeAddress('news', array('view' => $row->id), true);
                $tmp_txt = Text::simpleChunk(Text::strip_bbcodes($row->rawtext, true));
                if ($row->shorten === '1' && mb_strlen($tmp_txt) > $cfg->news_shortenedlen)
                {
                    $text = &$tmp_txt;
                    $text = htmlspecialchars(mb_substr($text, 0, $cfg->news_shortenedlen));
                    $text .= '...<br /><br /><a href="' . $view . '" class="news_read_more">' . $lang->read_more . '</a>';
                }
                else
                {
                    $text = &$row->parsedtext;
                }

                $params = array(
                    'TITLE' => $row->title,
                    'SIGNATURE' => $lang->signature($row->author, $row->date, $row->time),
                    'AUTHOR' => $row->author,
                    'TEXT' => $text,
                    'DATE' => $row->date,
                    'TIME' => $row->time,
                    'VIEW' => &$view
                );
                $tpl->setParams($params);
                $tpl->printPart(0, true);
            }
        }
        else
        {
            echo $lang->no_available;
        }

        $rows = $db->CountTable('news');
        $pages_count = ceil($rows / $cfg->news_pps);
        $pages_count = ($pages_count == 0 ? 1 : $pages_count);

        $next_page = '&nbsp;';
        if ($page < $pages_count)
        {
             $next_page = '<a href="' . SEO::makeAddress($info->modul, array('page' => $page + 1), true) . '">' . $lang->older . '</a>';
        }

        $prev_page = '&nbsp;';
        if ($page > 1)
        {
            $prev_page = '<a href="' . SEO::makeAddress($info->modul, array('page' => $page - 1), true)  . '">' . $lang->newer . '</a>';
        }

        $params = array(
            'PAGE-B' => $prev_page,
            'PAGE-F' => $next_page,
            'PAGE' => $page,
            'PAGES-C' => $pages_count
        );
        $tpl->setParams($params);
        $tpl->printPart(1, true);
        $design->printEnd();
    }
?>