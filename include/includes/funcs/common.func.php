<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    /**
     * redirects to $target
     *
     * @global Log $log
     * @param string $target
     */
    function headerTo($target)
    {
        global $log;
        if (MODE == 1 && !mb_substr_count($target, session_name() . '=' . session_id()))
        {
            $target .= (mb_substr_count($target, '?') > 0 ? '&' : '?');
            $target .= session_name() . '=' . session_id();
        }
        $log->write(1, 'redirect', 'target: ' . $target);
        header('Location: ' . $target);
        @ob_end_clean();
        exit;
    }

    /**
     * loads a rights-file into an array
     *
     * @param string $target the file to load
     * @return array contains the rights
     */
    function rights2array($target)
    {
        $target = 'include/admin/rights/' . $target . '.rights';
        if (!file_exists($target))
        {
            return null;
        }
        $file = file($target);
        $rights = array();
        foreach($file as $row)
        {
            $rights[] = trim($row);
        }
        return $rights;
    }

    /**
     * unshifts $value associated with $index into $array
     *
     * @param array $array the array to work on passed by reference
     * @param mixed $index the index for the new value
     * @param mixed $value the new value
     */
    function array_unshift_assoc(&$array, $index, $value)
    {
        $array = array_merge(array($index => $value), $array);
    }

    /**
     * adds the data to a file (uses file_get_contents() and file_put_contents())
     *
     * @param string $file the file to add to
     * @param add $contents the data to add
     */
    function file_add_contents($file, $contents)
    {
        $h = @fopen($file, 'ab');
        if (!is_resource($h))
        {
            throw new IOException('Could not apend file ' . $file . '!');
        }
        fwrite($h, $contents);
        fclose($h);
    }

    /**
     * returns a timestamp
     *
     * @return int the timestamp
     */
    function timestamp()
    {
        list($micro, $sec) = explode(' ', microtime());
        return $micro + $sec;
    }
?>
