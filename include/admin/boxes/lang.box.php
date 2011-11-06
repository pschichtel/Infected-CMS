<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    $langs = list_langs($info->lang);
    $options = '';
    foreach ($langs as $index => $value)
    {
        $options .= '<option value="' . $index . '">'. $value . '</option>';
    }
    $params['LANGS'] = &$options;
    $params['THIS'] = htmlspecialchars($_SERVER['REQUEST_URI']);
    $tpl = new Template('lang', null, 2);
    $tpl->setParams($params);
    $tpl->printPart(0, true);
?>
