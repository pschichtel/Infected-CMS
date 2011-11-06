<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    interface iSQL
    {
        public function checkConnection();
        public function PushData($query, $types = null, $param_arr = null);
        public function GetData($query, $types = null, $param_arr = null, $as_array = false);
        public function query($query);
        public function CountTable($table);
        public function GetColNames($Table);
        public function escape_string($string);
        public function fetch_array(&$result, $type = null);
        public function fetch_assoc(&$result);
        public function fetch_field(&$result, $offset = 0);
        public function fetch_lengths(&$result);
        public function fetch_object(&$result);
        public function fetch_row(&$result);
        public function field_flags(&$result, $offset);
        public function field_len(&$result, $offset);
        public function field_name(&$result, $offset);
        public function field_seek(&$result, $offset);
        public function field_table(&$result, $offset);
        public function field_type(&$result, $offset);
        public function free_result(&$result);
        public function result(&$result, $offset, $field = null);
        public function num_rows(&$result);
        public function num_fields(&$result);
        public function Error();
    }
?>
