<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    class SEO
    {
        /**
         * builds an SE optimized absolute address if activted in the configuration
         *
         * @access public
         * @static
         * @global Config $cfg
         * @global Info $info
         * @param string $modul the modul name
         * @param array $params the mudol params
         * @param bool $escapewhether to esccape or not
         * @return string the address
         */
        public static function makeAddress($modul = '', $params = array(), $escape = false)
        {
            global $cfg, $info;
            if ($modul === '')
            {
                $modul = $cfg->cms_std_modul;
            }
            if (!is_array($params))
            {
                throw new Exception('makeAddress failed while building the address! Reason: wrong parameter type');
            }
            $linkstring = ($cfg->seo_uri_rewrite ? '' : CI_INDEXFILE . '?modul=') . $modul;
            if (count($params) > 0)
            {
                if ($cfg->seo_uri_rewrite)
                {
                    $linkstring .= '.';
                }
                else
                {
                    $linkstring .= ($escape ? '&amp;' : '&') . 'params=';
                }
                 
            }
            $tmp = '';
            foreach ($params as $index => $value)
            {
                $tmp .= '.' . $index . '-' . Text::urlEncode($value, '.');
            }
            $linkstring .= mb_substr($tmp, 1);
            if ($cfg->seo_uri_rewrite)
            {
               $linkstring .= '.html';
            }
            if ($escape)
            {
                $linkstring = Text::EscapeAmp($linkstring);
            }
            $filename = preg_quote(basename($_SERVER['PHP_SELF']), '/');
            $cmsPath = $_SERVER['PHP_SELF'];
            $cmsPath = preg_replace("/$filename$/i", '', $cmsPath);
            return $cmsPath . $linkstring;
        }
    }
?>
