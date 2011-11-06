<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    /**
     * gathers user data and saves it to the stats table
     *
     * @global Database $db
     */
    function GetStat()
    {
        if (!isset($_SERVER['HTTP_USER_AGENT']) || !isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        {
            return;
        }
        if (IPBase::checkLock('stats') || isset($_COOKIE['ci_countedin']))
        {
            return;
        }
        global $db, $log;

        if (isset($_SERVER['HTTP_USER_AGENT']))
        {
            $regexe = array(
                'Windows 7' => "/windows nt 6\.1/Usi",
                'Windows Vista' => "/windows nt 6\.0/Usi",
                'Windows XP' => "/windows nt 5\./Usi",
                'Windows NT' => "/win(dows )?nt 4\./Usi",
                'Windows 98' => "/win(dows.*)?98\./Usi",
                'Windows 95' => "/win(dows.*)?95/Usi",
                'Macintosh' => "/mac/Usi",
                'Linux' => "/linux/Usi",
                'FreeBSD' => "/freebsd/Usi",
                'SunOS' => "/sunos/Usi",
                'BeOS' => "/beos/Usi",
                'OS2' => "/os\/2/Usi"
            );

            $OS = '';
            foreach ($regexe as $os => $regex)
            {
                if (preg_match($regex, $_SERVER['HTTP_USER_AGENT']))
                {
                    $OS = $os;
                    break;
                }
            }
            if (!$OS)
            {
                $OS = 'Unbekannt';
                $log->write(1, 'notice', 'OS was not recogniced! User-Agent: "' . $_SERVER['HTTP_USER_AGENT'] . '"');
            }

            $query = "SELECT `id` FROM `PREFIX_stats` WHERE `type`='os' AND `value`='$OS'";
            $result = $db->GetData($query);
            if (!$db->GetData($query))
            {
                $query = "INSERT INTO `PREFIX_stats` (`type`,`value`,`count`) VALUES ('os', '$OS', 1)";
                $db->PushData($query);
            }
            else
            {
                $query = "UPDATE `PREFIX_stats` SET `count`=`count`+1 WHERE `type`='os' AND `value`='$OS' LIMIT 1";
                $db->PushData($query);
            }

            $regexe = array(
                'Firefox 3.6' => "/firefox\/3\.6/i",
                'Firefox 3.5' => "/firefox\/3\.5/i",
                'Firefox 3' => "/firefox\/3/i",
                'Firefox 2' => "/firefox\/2/i",
                'Google Chrome' => "/chrome.*?safari/i",
                'Safari' => "/safari/i",
                'Opera' => "/opera/i",
                'Internet Explorer 8' => "/msie 8/i",
                'Internet Explorer 7' => "/msie 7/i",
                'Internet Explorer 6' => "/msie 6/i",
                'Netscape' => "/netscape/i",
                'Konqueror' => "/konqueror/i"
            );

            $Browser = '';
            foreach ($regexe as $browser => $regex)
            {
                if (preg_match($regex, $_SERVER['HTTP_USER_AGENT']))
                {
                    $Browser = $browser;
                    break;
                }
            }
            if (!$Browser)
            {
                $Browser = 'Unbekannt';
                $log->write(1, 'notice', 'Browser was not recogniced! User-Agent: "' . $_SERVER['HTTP_USER_AGENT'] . '"');
            }

            $query = "SELECT `id` FROM `PREFIX_stats` WHERE `type`='browser' AND `value`='$Browser'";
            if (!$db->GetData($query))
            {
                $query = "INSERT INTO `PREFIX_stats` (`type`,`value`,`count`) VALUES ('browser', '$Browser', 1)";
                $db->PushData($query);
            }
            else
            {
                $query = "UPDATE `PREFIX_stats` SET `count`=`count`+1 WHERE `type`='browser' AND `value`='$Browser' LIMIT 1";
                $db->PushData($query);
            }
        }

        if (isset($_SERVER['HTTP_USER_AGENT']))
        {
            $regexe = array(
                'Deutsch' => "/^de/i",
                'English' => "/^en/i",
                'French' => "/^fr/i",
                'Spanish' => "/^es/i",
                'Polish' => "/^pl/i",
                'Croatian' => "/^hr/i",
                'Hungarian' => "/^hu/i",
                'Italian' => "/^it/i",
                'Japanese' => "/^jp/i",
                'Portuguese' => "/^pt/i"
            );

            $Lang = '';
            foreach ($regexe as $langName => $regex)
            {
                if (preg_match($regex, $_SERVER['HTTP_ACCEPT_LANGUAGE']))
                {
                    $Lang = $langName;
                    break;
                }
            }
            if (!$Lang)
            {
                $Lang = 'Andere';
                $log->write(1, 'notice', 'Language was not recogniced! Accept-language: "' . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . '"');
            }


            $query = "SELECT `id` FROM `PREFIX_stats` WHERE `type`='lang' AND `value`='$Lang'";
            if (!$db->GetData($query))
            {
                $query = "INSERT INTO `PREFIX_stats` (`type`,`value`,`count`) VALUES ('lang', '$Lang', 1)";
                $db->PushData($query);
            }
            else
            {
                $query = "UPDATE `PREFIX_stats` SET `count`=`count`+1 WHERE `type`='lang' AND `value`='$Lang' LIMIT 1";
                $db->PushData($query);
            }
        }


        $query = 'SELECT `id` FROM `PREFIX_stats` WHERE `type`=\'counter\'';
        if (! $db->GetData($query))
        {
            $query = "INSERT INTO `PREFIX_stats` (`type`, `value`, `count`) VALUES ('counter', 'n/a', 1)";
            $db->PushData($query);
        }
        else
        {
            $query = "UPDATE `PREFIX_stats` SET `count`=`count`+1 WHERE `type`='counter' AND `value`='n/a' LIMIT 1";
            $db->PushData($query);
        }

        IPBase::setLock('stats', 86400);
        setcookie('ci_countedin', '1', time() + 60 * 60 * 24 * 365);
    }
?>