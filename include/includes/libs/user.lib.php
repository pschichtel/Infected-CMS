<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    class user
    {
        /**
         * checks whether the user is not available
         *
         * @access public
         * @static
         * @return bool true if the user is not available, otherwise false
         */
        public static function isAfk()
        {
            if (time() > $_SESSION['ci_afk_since'] + 30 * 60)
            {
                $_SESSION['ci_afk_since'] = time();
                return true;
            }
            $_SESSION['ci_afk_since'] = time();
            return false;
        }

        /**
         * checks whether the user is logged in
         *
         * @access public
         * @static
         * @return bool true if the user is loggt in, otherwise false
         */
        public static function loggedIn()
        {
            $userAgent = self::useragent(true);
            $remoteAddr = IPBase::getIP();

            if (!isset($_SESSION['ci_adminlogin'], $_SESSION['ci_userip'], $_SESSION['ci_loginuseragent']))
            {
                return false;
            }
            elseif($_SESSION['ci_adminlogin'] !== true ||
                   $_SESSION['ci_userip'] !== $remoteAddr ||
                   $_SESSION['ci_loginuseragent'] !== $userAgent)
            {
                return false;
            }
            elseif (self::isAfk())
            {
                return false;
            }
            else
            {
                return true;
            }
        }

        /**
         * checks whether the users has the giiven $right
         *
         * @access public
         * @static
         * @param string $right the right to check
         * @return bool true if the user has the right, otherwise false
         */
        public static function hasRight($right)
        {
            if (!isset($_SESSION) || !isset($_SESSION['ci_adminrights']))
            {
                return false;
            }
            $rights = &$_SESSION['ci_adminrights'];
            return (in_array('ALL', $rights) || in_array($right, $rights));
        }

        /**
         * returns to the login with the refering modul
         *
         * @access public
         * @static
         * @global Info $info
         */
        public static function backToLogin()
        {
            global $info;
            $modul = &$info->modul;
            $queryString = $info->modulQueryString;
            headerTo('admin.php?modul=login&referer=' . urlencode($modul . ($queryString ? '&' . $queryString : '')));
        }

        /**
         * returns the users user-agent or if not available an alternative string containing the session id
         *
         * @access public
         * @static
         * @param bool $alt whether to return an alternative string if the user-agent is not available
         * @return mixed the user agent or flase if not available
         */
        public static function useragent($alt = false)
        {
            if (isset($_SERVER['HTTP_USER_AGENT']))
            {
                $useragent = trim($_SERVER['HTTP_USER_AGENT']);
                if ($useragent !== '')
                {
                    return $_SERVER['HTTP_USER_AGENT'];
                }
            }
            if ($alt)
            {
                return '[user-agent_' . session_id() . ']';
            }
            else
            {
                return false;
            }
        }
    }
?>
