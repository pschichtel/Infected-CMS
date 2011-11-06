<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');

    echo '<pre>';
    echo timestamp() . ' - ' . time() . '<hr />';
    if (
        !is_null($info->modulParams('author')) &&
        !is_null($info->modulParams('text')) &&
        !is_null($info->modulParams('muid'))
    )
    {
        var_dump(Comments::add(
            'news',
            $info->modulParams('muid'),
            $info->modulParams('author'),
            $info->modulParams('text'),
            Text::parse($info->modulParams('text')) . mt_rand()
        ));
    }
    elseif (!is_null($info->modulParams('display')))
    {
        $muid = $info->modulParams('display');
        $i = 1;
        if (!is_null($info->modulParams('from')) || !is_null($info->modulParams('count')))
        {
            foreach (Comments::getRange(
                        'news',
                        $muid,
                        $info->modulParams('from'),
                        $info->modulParams('count')
                    ) as $comment)
            {
                echo 'Counter:   ' . $i                 . '<br />';
                echo 'Author:    ' . $comment['author'] . '<br />';
                echo 'Text:      ' . $comment['parsed'] . '<br />';
                echo 'GUID:      ' . $comment['guid']   . '<br />';
                echo '<hr />';
                $i++;
            }
        }
        elseif (!is_null($info->modulParams('get')))
        {
            $guids = explode(',', $info->modulParams('get'));
            foreach (Comments::getMultiple('news', $muid, $guids) as $comment)
            {
                echo 'Counter:   ' . $i                 . '<br />';
                echo 'Author:    ' . $comment['author'] . '<br />';
                echo 'Text:      ' . $comment['parsed'] . '<br />';
                echo 'GUID:      ' . $comment['guid']   . '<br />';
                echo '<hr />';
                $i++;
            }
        }
        else
        {
            foreach (Comments::getAll('news', $muid) as $comment)
            {
                echo 'Counter:   ' . $i                 . '<br />';
                echo 'Author:    ' . $comment['author'] . '<br />';
                echo 'Text:      ' . $comment['parsed'] . '<br />';
                echo 'GUID:      ' . $comment['guid']   . '<br />';
                echo '<hr />';
                $i++;
            }
        }
    }
    elseif (!is_null($info->modulParams('del')))
    {
        $muid = $info->modulParams('del');
        if (!is_null($info->modulParams('from')) || !is_null($info->modulParams('count')))
        {
            var_dump(Comments::deleteRange('news', $muid, $info->modulParams('from'), $info->modulParams('count')));
        }
        elseif (!is_null($info->modulParams('guids')))
        {
            $guids = explode(',', $info->modulParams('guids'));
            var_dump(Comments::deleteMultiple('news', $muid, $guids));
        }
        else
        {
            var_dump(Comments::deleteAll('news', $muid));
        }
    }
    echo '</pre>';
?>
