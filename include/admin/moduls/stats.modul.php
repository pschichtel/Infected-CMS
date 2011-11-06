<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');
    if (!user::loggedIn())
    {
        user::backToLogin();
    }

    $lang = new Lang($info->modul);
    $design = new Design();
    $design->printBegin();

    if ($info->modulParams('reset'))
    {
        user::hasRight('stats_reset') or headerTo($info->modulSelf . '&status=access_denied');

        if (isset($_POST['confirmation']))
        {
            if ($_POST['confirm'] == 'no')
            {
                headerTo($info->modulSelf);
            }
            elseif ($_POST['confirm'] == 'yes')
            {
                $query = 'DELETE FROM `PREFIX_stats`';
                $db->PushData($query);
                headerTo($info->modulSelf . '&status=clear');
            }
            else
            {
                headerTo($info->modulSelf);
            }
        }
        $tpl = new Template('confirm', $lang);
        $params = array(
            'THIS' => $info->modulSelf . '&amp;reset=stats',
            'LEGEND' => $lang->sure2reset
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);
    }
    else
    {
        $tpl = new Template('stats', $lang);

        $params = array(
            'STATUS' => $info->statusMessage($lang),
            'THIS' => $info->modulSelf
        );
        $tpl->setParams($params);
        $tpl->printPart(0, true);

        $query = 'SELECT DISTINCT `type` FROM `PREFIX_stats`';
        $stat_types = $db->GetData($query);

        if ($stat_types)
        {
            foreach ($stat_types as $stat_type)
            {
                $query = "SELECT `value`, `count` FROM `PREFIX_stats` WHERE `type`='{$stat_type->type}' ORDER BY `count` DESC LIMIT 5";
                $result = $db->GetData($query);
                $stats = '<table>' . "\n";
                foreach ($result as $row)
                {
                    $stats .= '<tr><td>' . ($stat_type->type != 'counter' ? $row->value : $lang->all) . '</td><td>' . $row->count . '</td></tr>' . "\n";
                }
                $stats .= '</table>';
                $params = array (
                    'BOX-HEADLINE' => $lang->{$stat_type->type},
                    'STATS' => $stats
                );

                $tpl->setParams($params, false);
                $tpl->printPart(1, true);
            }
        }
        else
        {
            echo $lang->no_available;
        }
        $tpl->printPart(2);
    }

    $design->printEnd();
?>
