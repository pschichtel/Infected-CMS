<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    class Contents
    {
        public static function get($modul, $cid, $lang, $strictLang = false)
        {
            throw new NotImplementedException(__CLASS__ . '::' . __FUNCTION__ . ' is not yet implemented!');
        }

        public static function getAll($modul, $lang, $strictLang = false)
        {
            throw new NotImplementedException(__CLASS__ . '::' . __FUNCTION__ . ' is not yet implemented!');
        }

        public static function add($modul, $title, $rawContent, $parsedContent)
        {
            throw new NotImplementedException(__CLASS__ . '::' . __FUNCTION__ . ' is not yet implemented!');
        }

        public static function delete($modul, $cid, $lang = null)
        {
            throw new NotImplementedException(__CLASS__ . '::' . __FUNCTION__ . ' is not yet implemented!');
        }

        public static function deleteAll($modul, $lang = null)
        {
            throw new NotImplementedException(__CLASS__ . '::' . __FUNCTION__ . ' is not yet implemented!');
        }
    }
?>
