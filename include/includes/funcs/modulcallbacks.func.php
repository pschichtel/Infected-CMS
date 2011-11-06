<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    function CBack_config_YesNo($rewind)
    {
        $lang = new Lang('other');
        if ($rewind)
        {
            $options['1'] = $lang->Yes;
            $options['0'] = $lang->No;
        }
        else
        {
            $options['0'] = $lang->No;
            $options['1'] = $lang->Yes;
        }
        return $options;
    }
?>
