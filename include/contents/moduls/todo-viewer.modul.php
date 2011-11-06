<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');
    
    $todoFile = '';
    $handle = @fsockopen('quick-wango.dyndns.org', '80', $errno, $errstr, 2);
    if ($handle !== false)
    {
        fclose($handle);
        $todoFile = @file_get_contents('http://quick-wango.dyndns.org/cms/todo.dev.txt');
    }
    else
    {
        $lang = new Lang($info->modul);
        echo $lang->qw_not_reachable . "\n\n";
        $todoFile = @file_get_contents('./todo.dev.txt');
    }
    header('Content-type: text/plain');
    echo $todoFile;
?>
