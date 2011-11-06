<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    class Template
    {
        /**
         * the content of the template
         *
         * @access public
         * @var string
         */
        public $content;

        /**
         * the array which contains the parts
         *
         * @access public
         * @var array
         */
        public $parts;

        /**
         * the name of the template file
         *
         * @access public
         * @var string the name of the template file
         */
        public $name;

        /**
         * contains the output placeholders
         *
         * @access private
         * @var array
         */
        private $vars;

        /**
         * contains the output replacements
         *
         * @access private
         * @var array
         */
        private $replacements;

        /**
         * the template Log object
         *
         * @access private
         * @var Log
         */
        private $log;

        /**
         * initiates the Template object
         *
         * @global Info $info
         * @param string $template
         * @param Lang $lang
         * @param int $type
         */
        public function __construct($template, Lang $lang = null, $type = 1)
        {
            global $info;
            $this->log = new Log('template');
            $this->log->write(1, 'init', "template: $template,type: $type");
            $mode = MODE;
            switch ((int) $type)
            {
                case 1:
                    $subdir = 'moduls/';
                    break;
                case 2:
                    $subdir = 'boxes/';
                    break;
                case 3:
                    $subdir = 'menus/';
                    break;
                case 4:
                    $subdir = 'other/';
                    $mode = 0;
                    break;
                default:
                    $subdir = 'moduls/';
            }
            $defaultPath = 'include/' . ($mode ? 'admin/templates' : 'templates') . '/';
            $currentDesignPath = 'include/designs/' . $info->design . '/templates/';
            
            $defaultPath .= $subdir;
            $currentDesignPath .= $subdir;
            $defaultFile = $defaultPath . $template;
            $currentDesignFile = $currentDesignPath . $template;
            if (MODE)
            {
                if ($type == 3)
                {
                    if (file_exists($defaultFile . '.tpl.html'))
                    {
                        $file = &$defaultFile;
                    }
                    elseif (file_exists(preg_replace('/menu\d+?$/i', 'menu', $defaultFile) . '.tpl.html'))
                    {
                        $file = preg_replace('/menu\d+?$/i', 'menu', $defaultFile);
                    }
                    else
                    {
                        throw new TemplateException('Mnue template "' . $template . '" was not found!');
                    }
                }
                else
                {
                    $file = &$defaultFile;
                }
            }
            else
            {
                if ($type == 3)
                {
                    if (file_exists($currentDesignFile . '.tpl.html'))
                    {
                        $file = &$currentDesignFile;
                    }
                    elseif (file_exists(preg_replace('/menu\d+?$/i', 'menu', $currentDesignFile) . '.tpl.html'))
                    {
                        $file = preg_replace('/menu\d+?$/i', 'menu', $currentDesignFile);
                    }
                    elseif (file_exists($defaultFile . '.tpl.html'))
                    {
                        $file = &$defaultFile;
                    }
                    else
                    {
                        $file = preg_replace('/menu\d+?$/i', 'menu', $defaultFile);
                    }
                }
                else
                {
                    $file = (file_exists($currentDesignFile . '.tpl.html') ? $currentDesignFile : $defaultFile);
                }
            }
            if (!file_exists($file . '.tpl.html'))
            {
                throw new TemplateException('Template');
            }
            $this->content = @file_get_contents($file . '.tpl.html');
            $this->log->write(1, 'info', "file: $file.tpl.html");
            $this->parse($this->content, $lang);
            $this->spilt();
            $this->vars = array();
            $this->replacements = array();
            $this->name = &$template;
        }

        /**
         * sets the output params for the template
         *
         * @access public
         * @param array $param_arr an associated array of the params
         * @param bool $escape if true the param values will bei escaped
         */
        public function setParams($params)
        {
            if (is_array($params))
            {
                $this->vars = array();
                $this->replacements = array();
                foreach ($params as $name => $value)
                {
                    $this->vars[] = '{' . $name . '}';
                    $this->replacements[] = $value;
                }
            }
        }

        /**
         * adds placeholders to the template
         *
         * @access public
         * @param array $param_arr an associated array with the placeholders and their values to add
         */
        public function addParams($params)
        {
            if (is_array($params))
            {
                foreach ($params as $name => $value)
                {
                    $this->vars[] = '{' . $name . '}';
                    $this->replacements[] = $value;
                }
            }
        }

        /**
         * clears the template placeholders
         *
         * @access public
         */
        public function clearParams()
        {
            $this->vars = array();
            $this->replacements = array();
        }

        /**
         * deletes placeholders from the template
         *
         * @access public
         * @param array $param_arr an indexed array with the names of the placeholdersto delete
         */
        public function deleteParams($params)
        {
            if (is_array($params))
            {
                foreach ($params as $name)
                {
                    while (is_int(($i = array_search('{' . $name . '}', $this->vars))))
                    {
                        unset($this->vars[$i]);
                        unset($this->replacements[$i]);
                    }
                }
            }
        }

        /**
         * prints the given part
         *
         * @access public
         * @param int $part the part to print
         * @param bool $parse
         */
        public function printPart($part, $parse = false)
        {
            $this->log->write(2, 'info', "Part printed: index:$part,parse:" . ($parse ? 'true' : 'false'));
            if (!isset($this->parts[(int) $part]))
            {
                throw new TemplateException('Part ' . $part . ' does not exist in template ' . $this->name . '!');
            }
            $part = &$this->parts[(int) $part];
            if ($parse)
            {
                echo trim(str_replace($this->vars, $this->replacements, $part));
            }
            else
            {
                echo trim($part);
            }
        }

        /**
         * returns the given part
         *
         * @access public
         * @param int $part the part to return
         * @param bool $parse if true, the part will be parsed befor return
         * @return string the part
         */
        public function getPart($part, $parse = false)
        {
            $this->log->write(2, 'info', "Part returned: index:$part,parse:" . ($parse ? 'true' : 'false'));
            if (!isset($this->parts[(int) $part]))
            {
                throw new TemplateException('Part ' . (int) $part . ' does not exist in template ' . $this->name . '!');
            }
            $part = &$this->parts[(int) $part];
            if ($parse)
            {
                return trim(str_replace($this->vars, $this->replacements, $part));
            }
            else
            {
                return trim($part);
            }
        }

        /**
         * returns the box with the given $name.
         * the function can be used as a callback for preg_replace_callback()
         *
         * @access public
         * @global Database $db
         * @global Config $cfg
         * @global Info $info
         * @global Log $log
         * @param mixed $name the box name
         * @return string the box content
         */
        public function getBox($name)
        {
            global $db, $cfg, $info, $log;
            if (is_array($name) && isset($name[1]))
            {
                $file = &$name[1];
            }
            else
            {
                $file = &$name;
            }
            $ext = '.box.php';
            $path = 'include/' . (MODE ? 'admin' : 'contents') . '/boxes/';
            if (!file_exists($path . $file . $ext))
            {
                Debug::log_error('error', 'Could not find the requested box "' . $file .  '"');
                return '';
            }

            ob_start();
            include($path . $file . $ext);
            return ob_get_clean();
        }

        /**
         * explodes the template by "{SPLIT}"
         *
         * @access private
         */
        private function spilt()
        {
            $this->parts = explode('{SPLIT}', $this->content);
            $this->log->write(2, 'info', 'template splitted: parts:' . count($this->parts));
        }

        /**
         * parses the language placeholders in the template
         * all parameters are passed by reference
         *
         * @access private
         * @param string& $text
         * @param Lang& $lang
         */
        protected function parseLang(&$text, &$lang)
        {
            $matches = array();
            preg_match_all('/\{LANG\[([\w\d-]+)\]\}/Us', $text, $matches);
            if (isset($this->log))
            {
                $this->log->write(2, 'info', 'Lang-placeholders found: ' . count($matches));
            }

            $langdata = array();
            foreach ($matches[1] as $match)
            {
                $langdata[$match] = $lang->$match;
            }
            foreach ($langdata as $index => $langContent)
            {
                $text = str_replace('{LANG[' . $index . ']}', $langContent, $text);
            }
        }

        /**
         * preg_replace_callback() callback function
         *
         * @access private
         * @param array $matches
         * @return string
         */
        private function Helper_parseLinks_modul($matches)
        {
            $this->log->write(3, 'info', 'Modullink parsed: ' . $matches[1]);
            return SEO::makeAddress($matches[1]);
        }

        /**
         * preg_replace_callback() callback function
         *
         * @access private
         * @param array $matches
         * @return string
         */
        private function Helper_parseLinks_self($matches)
        {
            $this->log->write(3, 'info', 'Selflink parsed: ' . $matches[1]);
            return SEO::makeAddress('self', array('cid' => $matches[1]));
        }

        /**
         * parses the link placeholders in the template
         *
         * @access protected
         * @param string& $text the text to parse in
         */
        protected function parseLinks(&$text)
        {
            $text = preg_replace('/\{LINK\[modul\:([\w\d-]+)\]\}/Us', 'Helper_parseLinks_modul', $text);
            $text = preg_replace('/\{LINK\[self\:([\d]+)\]\}/Us', 'Helper_parseLinks_self', $text);
            if (isset($this->log))
            {
                $this->log->write(3, 'info', 'Modullink parsed');
            }
        }

        /**
         * parses the box placeholders in the template
         *
         * @access protected
         * @param string& $text the text to parse in
         */
        protected function parseBoxes(&$text)
        {
            $text = preg_replace_callback('/\{BOX\[([\w\d-]+)\]\}/Us', array($this, 'getBox'), $text);
            if (isset($this->log))
            {
                $this->log->write(3, 'info', 'Box-placeholders parsed');
            }
        }

        /**
         * parse everything in the template
         *
         * @access protected
         * @param string& $text
         * @param lang& $lang
         */
        protected function parse(&$text, &$lang)
        {
            if (!is_null($lang))
            {
                $this->parseLang($text, $lang);
            }
            $this->parseLinks($text);
            $this->parseBoxes($text);
        }
    }
?>
