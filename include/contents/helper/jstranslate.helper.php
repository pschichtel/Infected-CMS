<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    header('Content-type: text/javascript');
    if (is_null($info->modulParams('file')))
    {
        die('window.alert("NO FILE GIVEN");');
    }
    $file = html_entity_decode(rawurldecode($info->modulParams('file')));
    if (preg_match('/(\.|\/)/i', $file))
    {
        die('window.alert("INVALID FILE GIVEN");');
    }
    if (file_exists('include/includes/javascripts/' . $file . '.js'))
    {
        $file = 'include/includes/javascripts/' . $file . '.js';
    }
    elseif (file_exists('include/designs/' . $info->design . '/script/' . $file . '.js'))
    {
        $file = 'include/designs/' . $info->design . '/script/' . $file . '.js';
    }
    else
    {
        die('window.alert("FILE NOT FOUND");');
    }
    $lang = new Lang($info->modulParams('lang'));

    function translater_callback($matches)
    {
        global $lang;
        return $lang->{$matches[1]};
    }

    $file = file_get_contents($file);

    $file = preg_replace_callback('/\{LANG\[([\w\d-]+?)\]\}/', 'translater_callback', $file);

    echo $file;


?>
