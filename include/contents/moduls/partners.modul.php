<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');

    $lang = new Lang($info->modul);
    $title = $cfg->cms_title . ' :: ' . $lang->partners;
    $design = new Design($title, $lang->partners);
    $design->printBegin();

    $tpl = new Template('partners', $lang);

    $page = (int) $info->modulParams('page');
    $page = ($page > 0 ? $page : 1);

    $offset = ($page - 1) * $cfg->partners_pps;
    $query = 'SELECT * FROM `PREFIX_partners` ORDER BY `position` LIMIT ' . $offset . ', ' . $cfg->partners_pps;

    $result = $db->GetData($query);
    $rows = $db->CountTable('partners');
    $pages_count = ceil($rows / $cfg->partners_pps);
    $pages_count = ($pages_count == 0 ? 1 : $pages_count);

    if ($result)
    {
        foreach ($result as $row)
        {
            $params = array(
                'PAGE-URI' => $row->pageuri,
                'PIC-URI' => $row->banneruri,
                'NAME' => $row->name
            );
            $tpl->setParams($params);
            $tpl->printPart(0, true);
        }
    }

    $next_page = '&nbsp;';
    if ($page < $pages_count)
    {
         $next_page = '<a href="' . SEO::makeAddress($info->modul, array('page' => $page + 1), true) . '">' . $lang->next_page . '</a>';
    }

    $prev_page = '&nbsp;';
    if ($page > 1)
    {
        $prev_page = '<a href="' . SEO::makeAddress($info->modul, array('page' => $page - 1), true) . '">' . $lang->prev_page . '</a>';
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
?>