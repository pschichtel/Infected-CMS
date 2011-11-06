<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    $langs = list_langs($info->lang);
    $designs = list_designs($info->design);

    $lang = new Lang('userconfig');

    $params['LANG'] = $lang->lang;
    $params['DESIGN'] = $lang->design;

    $options = '';
    foreach ($langs as $index => $value)
    {
        $options .= '<option value="' . $index . '">'. $value . '</option>';
    }
    $params['LANGS'] = $options;

    $options = '';
    foreach ($designs as $index => $value)
    {
        $options .= '<option value="' . $index . '">'. $value . '</option>';
    }
    $params['DESIGNS'] = $options;
    $params['THIS'] = htmlspecialchars($_SERVER['REQUEST_URI']);

    $tpl = new Template('userconfig', $lang, 2);
    $tpl->setParams($params);
    $tpl->printPart(0, true);
?>
