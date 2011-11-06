<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    class Info
    {
        /**
         * the name of the current modul
         *
         * @access public
         * @var string 
         */
        public $modul;
        
        /**
         * the absolute HTTP path to the modul file
         *
         * @access public
         * @var string 
         */
        public $modulFile;
        
        /**
         * the absolute HTTP path to the modul
         *
         * @access public
         * @var string 
         */
        public $modulSelf;

        /**
         * the current querystring
         *
         * @access public
         * @var string
         */
        public $modulQueryString;

        /**
         * the current language token
         *
         * @access public
         * @var string
         */
        public $lang;

        /**
         * the name of the current design
         *
         * @access public
         * @var string
         */
        public $design;

        /**
         * the absolute HTTP path of the CMS
         *
         * @access public
         * @var string
         */
        public $cmsPath;

        /**
         * wraps $_SERVER['REQUEST_URI']
         *
         * @access public
         * @var string the requested URI
         */
        public $requestUri;

        /**
         * the array with the params
         *
         * @access private
         * @var array
         */
        private $modulParams;

        /**
         * the global database connection
         *
         * @access private
         * @var Database
         */
        private $db;

        /**
         * the global configuration object
         *
         * @access private
         * @var Config
         */
        private $config;

        /**
         * the info Log object
         *
         * @access private
         * @var Log
         */
        private $log;

        const KEY_VALUE_DELIM = '-';

        const PAIR_DELIM = '.';

        /**
         * initiates the Config object
         *
         * @global Database $db
         * @global Config $cfg
         */
        public function __construct()
        {
            global $db;
            $this->db = $db;
            global $cfg;
            $this->config = $cfg;
            $this->log = new Log('info');
            $this->log->write(1, 'init', 'Empty signature, calling private getter-functions');

            $this->modulParams = array();
            
            $this->getCmsPath();
            $this->getModul();
            $this->getModulSelf();
            $this->getModulParams();
            $this->getModulQueryString();
            $this->getLang();
            $this->getDesign();
            $this->requestUri = &$_SERVER['REQUEST_URI'];
        }

        /**
         * destructs the object
         */
        public function __destruct()
        {
            unset($this->modulParams);
            unset($this->log);
        }

        /**
         * return a translated status message or a empty string
         *
         * @access public
         * @param Lang $lang a lang object to get the translated message
         * @return string the translated status-message
         */
        public function statusMessage(&$lang)
        {
            if (!isset($this->modulParams['status']))
            {
                return '';
            }
            return $lang->{'status_' . $this->modulParams['status']};
        }

        /**
         * returns the requested param or null if it does not exist
         *
         * @param string $index the index of the param
         * @return mixed string if the param exists, otherwise null
         */
        public function modulParams($index)
        {
            if (isset($this->modulParams[$index]))
            {
                return $this->modulParams[$index];
            }
            else
            {
                return null;
            }
        }

        /**
         * gets the absolute http path of the CMS
         *
         * @access private
         */
        private function getCmsPath()
        {
            $this->cmsPath = dirname($_SERVER['PHP_SELF']) . '/';
            $this->log->write(2, 'info', "CMS path: {$this->cmsPath}");
        }

        /**
         * gets the current modul name
         *
         * @access private
         */
        private function getModul()
        {
            $path = 'include/' . (MODE ? 'admin' : 'contents') . '/';
            $this->log->write(2, 'info', '#> ' . $path);
            if (isset($_GET['modul']))
            {
                $modul = &$_GET['modul'];
                $path .= 'moduls/';
                if (
                    substr_count($modul, '.') ||
                    !file_exists($path . $_GET['modul'] . '.modul.php')
                )
                {
                    $this->log->write(1, 'error', 'modul not loaded, redirecting to cfg::cms_std_modul');
                    headerTo(SEO::makeAddress($this->config->cms_std_modul));
                }
                $this->modul = &$modul;
                $this->modulFile = $path . $modul . '.modul.php';
            }
            elseif (isset($_GET['helper']))
            {
                $helper = &$_GET['helper'];
                $path .= 'helper/';
                if (
                    mb_substr_count('.', $helper) ||
                    !file_exists($path . $helper . '.helper.php')
                )
                {
                    die('Helper-modul not found!');
                }
                $this->modul = &$helper;
                $this->modulFile = $path . $helper . '.helper.php';
            }
            else
            {
                $path .= 'moduls/';
                if (MODE)
                {
                    $this->modul = 'overview';
                    $this->modulFile = $path . $this->modul . '.modul.php';
                }
                else
                {
                    $this->modul = $this->config->cms_std_modul;
                    $this->modulFile = $path . $this->modul . '.modul.php';
                }
            }
            $this->log->write(2, 'info', "Current modul: {$this->modul}");
        }

        /**
         * gets the absolute path to the current modul
         *
         * @access private
         */
        private function getModulSelf()
        {
            if (MODE)
            {
                $this->modulSelf = $_SERVER['PHP_SELF'] . '?modul=' . $this->modul;
            }
            else
            {
                $this->modulSelf = SEO::makeAddress($this->modul);
            }
            $this->log->write(2, 'info', "Modul path: {$this->modulSelf}");
        }

        /**
         * gets the params passed to the current modul
         *
         * @access private
         */
        private function getModulParams()
        {
            if (MODE)
            {
                $this->modulParams = &$_GET;
            }
            else
            {
                if (isset($_GET['params']))
                {
                    $paramPairs = explode(self::PAIR_DELIM, $_GET['params']);
                    $params = array();
                    foreach ($paramPairs as $paramsPair)
                    {
                        $pair = explode('-', $paramsPair);
                        if (count($pair) > 1)
                        {
                            $params[$pair[0]] = rawurldecode($pair[1]);
                        }
                        elseif (count($pair) == 1)
                        {
                            $params[$pair[0]] = null;
                        }
                    }
                    $this->modulParams = array_merge($this->modulParams, $params);
                }
            }
            $this->log->write(2, 'info', 'param count: ' . count($this->modulParams) . ', params: {' . serialize($this->modulParams) . '}');
        }

        /**
         * gets the querystring
         *
         * @access private
         */
        private function getModulQueryString()
        {
            if (MODE)
            {
                $params = $this->modulParams;
                unset($params['modul']);
                $this->modulQueryString = '';
                foreach ($params as $index => $value)
                {
                    $value = trim($value);
                    if ($value !== '')
                    {
                        $this->modulQueryString .= '&' . $index . '=' . $value;
                    }
                }
                $this->modulQueryString = mb_substr($this->modulQueryString, 1);
            }
            else
            {
                $this->modulQueryString = &$_GET['params'];
            }
            $this->log->write(2, 'info', "Querystring: {$this->modulQueryString}");
        }

        /**
         * gets the current language
         *
         * @access private
         */
        private function getLang()
        {
            $path = 'include/' . (MODE ? 'admin' : 'contents') . '/lang/';
            if (
                isset($_GET['lang']) &&
                preg_match('/[a-z]{2}/si', $_GET['lang']) &&
                file_exists($path . $_GET['lang'])
            )
            {
                $this->lang = $_GET['lang'];
                $_SESSION['ci_lang'] = $_GET['lang'];
            }
            elseif (
                isset($_POST['lang']) &&
                preg_match('/[a-z]{2}/si', $_POST['lang']) &&
                file_exists($path . $_POST['lang'])
            )
            {
                $this->lang = $_POST['lang'];
                $_SESSION['ci_lang'] = $_POST['lang'];
            }
            elseif (isset($_SESSION['ci_lang']))
            {
                $this->lang = $_SESSION['ci_lang'];
            }
            else
            {
                $this->lang = $this->config->cms_std_lang;
            }
            $this->log->write(2, 'info', "Lang: {$this->lang}");
        }

        /**
         * gets the current design
         *
         * @access private
         */
        private function getDesign()
        {
            $path = 'include/designs/';
            if (isset($_GET['design']) && file_exists($path . $_GET['design']))
            {
                $this->design = $_GET['design'];
                $_SESSION['ci_design'] = $_GET['design'];
            }
            elseif (isset($_POST['design']) && file_exists($path . $_POST['design']))
            {
                $this->design = $_POST['design'];
                $_SESSION['ci_design'] = $_POST['design'];
            }
            elseif (isset($_SESSION['ci_design']))
            {
                $this->design = $_SESSION['ci_design'];
            }
            else
            {
                $this->design = $this->config->cms_std_design;
            }
            $this->log->write(2, 'info', "Design: {$this->design}");
        }
    }
?>
