<?php
    defined('MODE') or die('<strong>Access denied!</strong>');


    $result = $db->GetData('SELECT `count` FROM `PREFIX_stats` WHERE `type`=\'counter\' LIMIT 1');
    if (count($result) == 0)
    {
        $count = 0;
    }
    else
    {
        $count = &$result[0]->count;
    }
    echo '<div style="padding:5px 10px;">Counter: ' . $count . '</div>';
?>
