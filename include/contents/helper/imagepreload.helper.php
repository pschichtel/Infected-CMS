<?php
    defined('MODE') or die('<strong>Access denied!</strong>');
    
    if (!isset($_SESSION['ci_images_preloaded']))
    {
        $_SESSION['ci_images_preloaded'] = false;
    }
    if (!$_SESSION['ci_images_preloaded'])
    {
        if ($info->modulParams('sprefix'))
        {
            $paths = list_images('include/designs/' . $info->design . '/images/', true, $info->modulParams('sprefix'));
        }
        else
        {
            $paths = list_images('include/designs/' . $info->design . '/images');
        }

        $tpl = new Template('preload_script');
        $tpl->printPart(0);

        $log->write(4, 'info', 'images listed: ' . count($paths));
        echo implode("','", $paths);

        $tpl->printPart(1);
    }
    else
    {
        echo 'function ci_imagePreloader() {}';
    }

    $_SESSION['ci_images_preloaded'] = true;
?>
