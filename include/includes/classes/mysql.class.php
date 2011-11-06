<?php
    /**
     * 
     */
    class Database implements iSQL
    {
        /**
         * the database handle
         *
         * @access private
         * @var resource
         */
        private $dbhandle;

        /**
         * the host of the database
         *
         * @access private
         * @var string
         */
        private $host;

        /**
         * the username
         *
         * @access private
         * @var string
         */
        private $user;

        /**
         * the password
         *
         * @access private
         * @var string
         */
        private $pass;

        /**
         * the name of the database
         *
         * @access private
         * @var string
         */
        private $db;

        /**
         * the table prefix
         *
         * @access private
         * @var string
         */
        private $prefix;

        /**
         * whether to use UTF-8 or not
         *
         * @access private
         * @var bool
         */
        private $utf8;

        /**
         * the database Log object
         *
         * @access private
         * @var Log
         */
        private $log;

        /**
         * whether to class is connected to the database or not
         *
         * @access public
         * @var bool
         */
        public $connected;

        /**
         * the affected rows of the last statement
         *
         * @access public
         * @var int
         */
        public $affected_rows;

        /**
         * initiates the Database class with the given data.
         * throws a DBException on failure
         *
         * @access public
         * @param string $host MySQL serve address
         * @param string $user username
         * @param string $pass user pasword
         * @param string $db database name
         * @param string $prefix table prefix
         * @param bool $utf8 to use UTF-8 or not
         */
        public function __construct($host, $user, $pass, $db, $prefix, $utf8 = false)
        {
            $this->log = new Log('database');
            if (!is_string($host) ||
                !is_string($user) ||
                !is_string($pass) ||
                !is_string($db) ||
                !is_bool($utf8))
            {
                throw new DBException('Initialisation failed! Wrong params MySQL::MySQL(string host, string user, string pass, string db, string prefix, bool utf8)');
            }
            $this->log->write(2, 'init', "Contructing Object: host: $host,user: $user,pass:****,db: $db,prefix: $prefix,utf8: " . ($utf8 ? 'true' : 'false'));
            $this->host = $host;
            $this->user = $user;
            $this->pass = $pass;
            $this->db = $db;
            $this->prefix = $prefix;
            $this->utf8 = $utf8;
            $this->connected = false;
        }

        /**
         * destructs the object and closes the database connection if still open.
         *
         * @access public
         */
        public function __destruct()
        {
            $this->log->write(3, 'destruct', 'closing DB-connection...');
            if (is_resource($this->dbhandle))
            {
                mysql_close($this->dbhandle);
            }
            unset($this->log);
        }

        /**
         * etablishes the connection to the database.
         * throws a DBException on failure
         *
         * @access private
         */
        private function connect()
        {
            if ($this->connected)
            {
                return;
            }
            $this->log->write(2, 'info', 'connecting to DB');
            $this->dbhandle = @mysql_connect($this->host, $this->user, $this->pass);
            if (!is_resource($this->dbhandle))
            {
                throw new DBException('Database connection failed! Message: ' . mysql_error());
            }

            if (!mysql_select_db($this->db, $this->dbhandle))
            {
                throw new DBException('Database selection failed! Message: ' . mysql_error());
            }
            $this->connected = true;
            if ($this->utf8)
            {
                $this->query('SET CHARACTER SET \'utf8\'', $this->dbhandle);
            }
        }

        /**
         * prepares the $params for the query.
         * throws a DBException on failure
         *
         * @access private
         * @param string $types the data types
         * @param array $params the data / params
         * @return array
         */
        private function PrepareData($types, $params)
        {
            if (mb_strlen($types) != count($params))
            {
                throw new DBException('Database::PrepareData: the count of types and the count of params are not equal!');
            }

            for($i = 0; $i < count($params); $i++)
            {
                if (get_magic_quotes_gpc())
                {
                    $this->log->write(2, 'info', "Database::PrepareData: magic_quotes enabled, stripping slashes at offset $i!");
                    $params[$i] = stripslashes($params[$i]);
                }
                $params[$i] = mysql_real_escape_string($params[$i], $this->dbhandle);
                switch($types{$i})
                {
                    case 's':
                        $params[$i] = '\'' . $params[$i] . '\'';
                        $this->log->write(3, 'info', "Database::PrepareData: Param casted to string at offset $i!");
                        break;
                    case 'i':
                        $params[$i] = (int) $params[$i];
                        $this->log->write(3, 'info', "Database::PrepareData: Param casted to int at offset $i!");
                        break;
                    case 'd':
                        $params[$i] = (double) $params[$i];
                        $this->log->write(3, 'info', "Database::PrepareData: Param casted to double at offset $i!");
                        break;
                    default:
                        throw new DBException('Database::PrepareData: there was an incompatible type given at offset $i!');
                }
            }
            return $params;
        }

        /**
         * inserts $params in the query if available and replaces the PREFIX placeholder
         *
         * @access private
         * @param string $query the query to parse
         * @param array $params the params to be inserted into the query
         * @return string
         */
        private function ParseQuery($query, $params = null) {
            if (!is_null($params))
            {
                if (mb_substr_count($query, '?') != count($params))
                {
                    throw new DBException('Database::ParseQuery: the count of types and the count of params are not equal!');
                }

                $splitted_query = explode('?', $query);
                $query = $splitted_query[0];
                for ($i = 0; $i < count($params); $i++)
                {
                    $query .= $params[$i] . $splitted_query[$i + 1];
                }
            }
            return preg_replace('/`PREFIX_([\w\d-]+)`/Us', '`' . $this->prefix . '$1`', $query);
        }

        /**
         * checks whether the connection to the MySQL-server is possible
         *
         * @access public
         * @return bool
         */
        public function checkConnection()
        {
            try
            {
                $this->connect();
            }
            catch (DBException $e)
            {
                return false;
            }
            catch (Exception $e)
            {
                return false;
            }
            return true;
        }

        /**
         * executes $query with $types and $param_arr and returns the affected rows on success
         * throws an DBException on failure
         *
         * @access public
         * @param string $query the query to execute
         * @param string $types the data types
         * @param array $param_arr the data / params
         * @return int
         */
        public function PushData($query, $types = null, $param_arr = null)
        {
            try
            {
                $this->connect();
            }
            catch (DBException $e)
            {
                throw new DBException('Database::PushData failed to connect! Message: ' . $e->getMessage());
            }
            if ($types && $param_arr)
            {
                $param_arr = $this->PrepareData($types, $param_arr);
            }
            $query = $this->ParseQuery($query, ($param_arr ? $param_arr : null));

            if (mysql_query($query, $this->dbhandle) === false)
            {
                throw new DBException('Database::PushData: could not execute this statement! Message: ' . mysql_error());
            }
            $this->affected_rows = mysql_affected_rows($this->dbhandle);
            $this->log->write(2, 'info', "Database::PushData: affected rows: {$this->affected_rows}");
            return $this->affected_rows;
        }

        /**
         * executes $query with $types and $param_arr and returns the
         * already fetched database result as an array.
         * if $as_array is true, the rows are put into arrays instead of objects.
         * throws a DBException an failure
         *
         *
         * @access public
         * @param string $query the query
         * @param string $types the data types of the params
         * @param array $param_arr the params for the query
         * @param bool $as_array as object or array
         * @return array
         */
        public function GetData($query, $types = null, $param_arr = null, $as_array = false)
        {
            try
            {
                $this->connect();
            }
            catch (DBException $e)
            {
                throw new DBException('Database::GetData failed to connect! Message: ' . $e->getMessage());
            }
            if ($types && $param_arr)
            {
                $param_arr = $this->PrepareData($types, $param_arr);
            }
            $query = $this->ParseQuery($query, ($param_arr ? $param_arr : null));
            
            $result = mysql_unbuffered_query($query, $this->dbhandle);
            if ($result === false)
            {
                throw new DBException('Database::GetData: could not execute this statement! Message: ' . mysql_error());
            }
            $affected_rows = 0;
            $data = array();
            while ($row = mysql_fetch_assoc($result))
            {
                $data[] = ($as_array ? $row : (object) $row);
                $affected_rows++;
            }
            $this->affected_rows = $affected_rows;
            $this->log->write(1, 'info', "Database::GetData: affected rows: {$this->affected_rows}");
            if (mysql_free_result($result) === false)
            {
                throw new DBException('Database::GetData: error while freeing the result! Message: ' . mysql_error());
            }
            return $data;
        }

        /**
         * counts the given $table.
         * throws a DBException an failure
         *
         * @access public
         * @param string $table the table name
         * @return int
         */
        public function CountTable($table)
        {
            try
            {
                $this->connect();
            }
            catch (DBException $e)
            {
                throw new DBException('Database::CountData failed to connect! Message: ' . $e->getMessage());
            }
            $query = 'SELECT count(*) AS \'count\' FROM `' . $this->prefix . $table . '`';
            $result = $this->query($query);
            if ($result === false)
            {
                throw new DBException('Database::CountTable failed to count the given table "' . $table . '"! Message: ' . mysql_error());
            }
            $result = mysql_fetch_row($result);
            $this->log->write(2, 'info', "Database::CountTable: table: $table,rows: {$result[0]}");
            return $result[0];
        }

        /**
         * returns the col names of the given $table.
         * throws a DBException on failure
         *
         * @access public
         * @param string $table the table name
         * @return array
         */
        public function GetColNames($table)
        {
            try
            {
                $this->connect();
            }
            catch (DBException $e)
            {
                throw new DBException('Database::GetColNames failed to connect! Message: ' . $e->getMessage());
            }
            $query = 'DESCRIBE `' . $this->prefix . $table . '`';
            $result = $this->GetData($query);
            $colNames = array();
            foreach ($result as $row)
            {
                $colNames[] = $row->Field;
            }
            return $colNames;
        }

        /**
         * returns a string with the last error number an error message
         *
         * @access public
         * @return string
         */
        public function error()
        {
            try
            {
                $this->connect();
            }
            catch (DBException $e)
            {
                throw new DBException('Database::error failed to connect! Message: ' . $e->getMessage());
            }
            $this->log->write(2, 'info', 'Database::Error: errno: ' . mysql_errno($this->dbhandle) . ',message: ' . mysql_error($this->dbhandle));
            return mysql_errno($this->dbhandle) . ': ' . mysql_error($this->dbhandle);
        }

        /*
         * Wrapper functions
         *
         * @access public
         *
         */
        
        public function query($query)
        {
            try
            {
                $this->connect();
            }
            catch (DBException $e)
            {
                throw new DBException('Database::query failed to connect! Message: ' . $e->getMessage());
            }
            $query = preg_replace('/`PREFIX_([\w\d-]+)`/Us', '`' . $this->prefix . '$1`', $query);
            $result = mysql_query($query, $this->dbhandle);
            $this->affected_rows = mysql_affected_rows($this->dbhandle);
            return $result;
        }

        public function escape_string($string)
        {
            try
            {
                $this->connect();
            }
            catch (DBException $e)
            {
                throw new DBException('Database::escape_string failed to connect! Message: ' . $e->getMessage());
            }
            return @mysql_real_escape_string($string, $this->dbhandle);
        }

        public function fetch_array(&$result, $type = null)
        {
            if (!is_resource($result))
            {
                return false;
            }
            if ($type === null)
            {
                return @mysql_fetch_array($result);
            }
            else
            {
                return @mysql_fetch_array($result, $type);
            }
        }

        public function fetch_assoc(&$result)
        {
            if (!is_resource($result))
            {
                return false;
            }
            return @mysql_fetch_assoc($result);
        }
        
        public function fetch_field(&$result, $offset = 0)
        {
            if (!is_resource($result))
            {
                return false;
            }
            return @mysql_fetch_field($result, $offset);
        }
        
        public function fetch_lengths(&$result)
        {
            if (!is_resource($result))
            {
                return false;
            }
            return @mysql_fetch_lengths($result);
        }
        
        public function fetch_object(&$result)
        {
            if (!is_resource($result))
            {
                return false;
            }
            return @mysql_fetch_object($result);
        }
        
        public function fetch_row(&$result)
        {
            if (!is_resource($result))
            {
                return false;
            }
            return @mysql_fetch_row($result);
        }
        
        public function field_flags(&$result, $offset)
        {
            if (!is_resource($result))
            {
                return false;
            }
            return @mysql_field_flags($result, $offset);
        }
        
        public function field_len(&$result, $offset)
        {
            if (!is_resource($result))
            {
                return false;
            }
            return @mysql_field_len($result, $offset);
        }
        
        public function field_name(&$result, $offset)
        {
            if (!is_resource($result))
            {
                return false;
            }
            return @mysql_field_name($result, $offset);
        }
        
        public function field_seek(&$result, $offset)
        {
            if (!is_resource($result))
            {
                return false;
            }
            return @mysql_field_seek($result, $offset);
        }
        
        public function field_table(&$result, $offset)
        {
            if (!is_resource($result))
            {
                return false;
            }
            return @mysql_field_table($result, $offset);
        }
        
        public function field_type(&$result, $offset)
        {
            if (!is_resource($result))
            {
                return false;
            }
            return @mysql_field_type($result, $offset);
        }
        
        public function free_result(&$result)
        {
            if (!is_resource($result))
            {
                return false;
            }
            return @mysql_free_result($result);
        }
        
        public function result(&$result, $offset, $field = null)
        {
            if (!is_resource($result))
            {
                return false;
            }
            if ($field === null)
            {
                return @mysql_result($result, $offset);
            }
            else
            {
                return @mysql_result($result, $offset, $field);
            }
        }
        
        public function num_rows(&$result)
        {
            if (!is_resource($result))
            {
                return false;
            }
            return @mysql_num_rows($result);
        }
        
        public function num_fields(&$result)
        {
            if (!is_resource($result))
            {
                return false;
            }
            return @mysql_num_fields($result);
        }
    }
?>
