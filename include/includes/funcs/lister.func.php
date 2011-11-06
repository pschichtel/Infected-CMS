<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    /**
     * lists images in $path and skips files with $prefix.
     * if $recursive is true, the function reads the subdirectories, too
     *
     * @param string $path path of the images
     * @param bool $recursive whether to read subdirectories
     * @param string $skip_prefix prefix to skip
     * @return array array with all the images paths
     */
    function list_images($path, $recursive = true, $skip_prefix = 'skip_')
    {
        $paths = array();
        if (!preg_match('/\/$/i', $path))
        {
            $path .= '/';
        }
        if (is_dir($path))
        {
            if (($dhandle = opendir($path)))
            {
                while (($file = readdir($dhandle)) !== false)
                {
                    if (preg_match('/^\.\.?$/si', $file) || preg_match("/^$skip_prefix/si", $file))
                    {
                        continue;
                    }
                    if (is_dir($path . $file) && $recursive)
                    {
                        $paths = array_merge($paths, list_images($path . $file . '/', true, $skip_prefix));
                    }
                    else
                    {
                        $paths[] = $path . $file;
                    }
                }
                closedir($dhandle);
            }
        }
        return $paths;
    }

    /**
     * lists the designs and rewinds the key $rewind
     *
     * @param string $rewind the index to rewind inside the array
     * @return array an array with the design names
     */
    function list_designs($rewind = '')
    {
        $target = 'include/designs/';
        $dirs = array();
        if (is_dir($target))
        {
            if (($dh = opendir($target)))
            {
                while (($dir = readdir($dh)) !== false)
                {
                    if (is_dir($target . $dir))
                    {
                        $dirs[$dir] = $dir;
                    }
                }
                closedir($dh);
            }
        }
        if ($rewind !== '' && isset($dirs[$rewind]))
        {
            $current = $dirs[$rewind];
            unset($dirs[$rewind]);
            array_unshift_assoc($dirs, $rewind, $current);
        }

        unset($dirs['.']);
        unset($dirs['..']);
        return $dirs;
    }

    /**
     * lists the frontpage or admin moduls an rewinds $rewind in the array
     *
     * @param string $rewind the index to rewind inside the array
     * @param bool $main true for MODE 0, false for MODE 1
     * @return array array with all the moduls
     */
    function list_moduls($rewind = '', $main = true)
    {
        $target = ($main ? 'include/contents/moduls/' : 'include/admin/moduls/');
        $files = array();
        if (is_dir($target))
        {
            if (($dh = opendir($target)))
            {
                while (($file = readdir($dh)) !== false)
                {
                    if (!is_dir($target . $file))
                    {
                        if (preg_match('/^_/', $file))
                        {
                            continue;
                        }
                        $filename = mb_substr($file, 0, mb_strpos($file, '.', 0));
                        $files[$filename] = $filename;
                    }
                }
                closedir($dh);
            }
        }
        if ($rewind !== '' && isset($files[$rewind]))
        {
            $current = $files[$rewind];
            unset($files[$rewind]);
            array_unshift_assoc($files, $rewind, $current);
        }
        return $files;
    }

    /**
     * lists the boxes and rewinds $rewind in the array
     *
     * @param string $rewind the index to rewind inside the array
     * @return array array with all the boxes
     */
    function list_boxes($rewind = '')
    {
        $target = 'include/contents/boxes/';
        $files = array();
        if (is_dir($target))
        {
            if (($dh = opendir($target)))
            {
                while (($file = readdir($dh)) !== false)
                {
                    if (!is_dir($target . $file))
                    {
                        if (preg_match('/^_/', $file))
                        {
                            continue;
                        }
                        $filename = mb_substr($file, 0, mb_strpos($file, '.', 0));
                        $files[$filename] = $filename;
                    }
                }
                closedir($dh);
            }
        }
        if ($rewind !== '' && isset($files[$rewind]))
        {
            $current = $files[$rewind];
            unset($files[$rewind]);
            array_unshift_assoc($files, $rewind, $current);
        }
        return $files;
    }

    /**
     * lists the self created contents and rewinds $rewind in the array
     *
     * @global Database $db
     * @param string $rewind the index to rewind inside the array
     * @return array array with the content IDs of the self created contents
     */
    function list_selfs($rewind = '')
    {
        global $db;

        $query = 'SELECT `id`,`name` FROM `PREFIX_selfcontent`';
        $result = $db->GetData($query);

        $selfs = array();
        if (count($result) > 0)
        {
            foreach ($result as $row)
            {
                $selfs[(int) $row->id] = $row->name;
            }
        }
        if (is_numeric($rewind) && isset($selfs[$rewind]))
        {
            $current = $selfs[$rewind];
            unset($selfs[$rewind]);
            array_unshift_assoc($selfs, $rewind, $current);
        }
        return $selfs;
    }

    /**
     * lists the frontpage or admin language files and rewinds $rewind in the array
     *
     * @param string $rewind the index to rewind inside the array
     * @param bool $main true for MODE 0, false for MODE 1
     * @return array array with all the language tokens
     */
    function list_langs($rewind = '', $main = true)
    {
        $target = ($main ? 'include/contents/lang/' : 'include/admin/lang/');
        $dirs = array();
        if (is_dir($target))
        {
            if (($dh = opendir($target)))
            {
                while (($dir = readdir($dh)) !== false)
                {
                    if (is_dir($target . $dir))
                    {
                        if (preg_match('/^_/', $dir))
                        {
                            continue;
                        }
                        $dirs[$dir] = $dir;
                    }
                }
                closedir($dh);
            }
        }
        if ($rewind && isset($dirs[$rewind]))
        {
            $current = $dirs[$rewind];
            unset($dirs[$rewind]);
            array_unshift_assoc($dirs, $rewind, $current);
        }

        unset($dirs['.']);
        unset($dirs['..']);
        return $dirs;
    }
?>
