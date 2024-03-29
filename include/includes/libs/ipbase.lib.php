<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    class IPBase
    {
        /**
         * checks whether the given string is a valid IPv4
         *
         * @access public
         * @static
         * @param string $ip the IP to validate
         * @return bool true if it is a valid IPv4
         */
        public static function isIPv4($ip)
        {
            $parts = explode('.', $ip);
            if (count($parts) > 4)
            {
                return false;
            }
            foreach ($parts as $part)
            {
                if (!is_numeric($part))
                {
                    return false;
                }
                $part = (int) $part;
                if ($part > 255)
                {
                    return false;
                }
            }
            return true;
        }

        /**
         * checks whether the given string is a valid IPv6
         *
         * @access public
         * @static
         * @param string $ip the IP to validate
         * @return bool true if is is a valid IPv6
         */
        public static function isIPv6($ip)
        {
            $parts = explode(':', $ip);
            if (count($parts) > 8)
            {
                return false;
            }
            if (!preg_match('/[a-f1-9:]+/i', $ip))
            {
                return false;
            }
            return true;
        }

        /**
         * tries to get the client IP.
         * if it has succeeded, the IP will be returned, otherwise false
         *
         * @access public
         * @static
         * @return mixed false if the IP is invalid, otherwise the IP
         */
        public static function getIP()
        {
            $ip = '';
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
            {
                $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            }
            elseif(isset($_SERVER["REMOTE_ADDR"]))
            {
                $ip = $_SERVER["REMOTE_ADDR"];
            }
            else
            {
                return '127.0.0.1';
            }
            if (self::isIPv4($ip))
            {
                return $ip;
            }
            elseif (self::isIPv6($ip))
            {
                return $ip;
            }
            else
            {
                return false;
            }
        }

        /**
         * creates a new entrie in the ipbase
         *
         * @access protected
         * @static
         * @global Database $db
         * @return bool true on success, false on failure
         */
        protected static function makeEntry()
        {
            global $db;
            $query = 'INSERT INTO `PREFIX_ipbase` (`ip`) VALUES (?)';
            $types = 's';
            $ip = self::getIP();
            if ($ip === false)
            {
                return false;
            }
            $param_arr = array($ip);
            try
            {
                $db->PushData($query, $types, $param_arr);
            }
            catch (DBException $e)
            {
                return false;
            }
            return true;
        }

        /**
         * checks whether there exists an entry for the current IP
         *
         * @access protected
         * @static
         * @return bool true on success, false on failure
         */
        protected static function check4Entry()
        {
            global $db;
            $query = 'SELECT `ip` FROM `PREFIX_ipbase` WHERE `ip`=?';
            $types = 's';
            $ip = self::getIP();
            if ($ip === false)
            {
                return false;
            }
            $param_arr = array($ip);
            try
            {
                $result = $db->GetData($query, $types, $param_arr);
            }
            catch (DBException $e)
            {
                return false;
            }
            return (count($result) == 0 ? false : true);
        }

        /**
         * sets the lock time in the given col (for the given modul)
         *
         * @access public
         * @static
         * @global Database $db
         * @param string $col the col / modul
         * @param int $LockTime the time to lock in seconds
         * @return bool true on success, false an failure
         */
        public static function setLock($col, $lockTime)
        {
            global $db;
            if (!self::check4Entry())
            {
                self::makeEntry();
            }
            $query = "UPDATE `PREFIX_ipbase` SET `$col`=? WHERE `ip`=?";
            $types = 'is';
            $ip = self::getIP();
            if ($ip === false)
            {
                return false;
            }
            $param_arr = array(time() + $lockTime, $ip);
            try
            {
                $db->PushData($query, $types, $param_arr);
            }
            catch (DBException $e)
            {
                return false;
            }
            return true;
        }

        /**
         * checks whether there exists a lock for the given col/modul
         *
         * @access public
         * @static
         * @global Database $db
         * @param string $col the col / modul
         * @return bool true if there is a lock, false if not or on failure
         */
        public static function checkLock($col)
        {
            global $db;
            $query = "SELECT `$col` FROM `PREFIX_ipbase` WHERE `ip`=? AND `$col`>?";
            $types = 'si';
            $ip = self::getIP();
            if ($ip === false)
            {
                return false;
            }
            $param_arr = array($ip, time());
            try
            {
                $result = $db->GetData($query, $types, $param_arr);
            }
            catch (DBException $e)
            {
                return false;
            }
            return (count($result) == 0 ? false : true);
        }

        /**
         * deletes all entries which have no activ locks anymore
         *
         * @access public
         * @static
         * @global Database $db
         */
        public static function clearIPBase()
        {
            global $db;
            $colNames = $db->GetColNames('ipbase');
            $Cols = array();
            for ($i = 2; $i < count($colNames); $i++)
            {
                $Cols[] = $colNames[$i];
            }
            $query = 'DELETE FROM `PREFIX_ipbase` WHERE ';
            foreach ($Cols as $col)
            {
                $query .= "`$col`<=" . time() . ' AND ';
            }
            $query = preg_replace('/ AND $/i', '', $query);
            $db->PushData($query);
        }
    }
?>
