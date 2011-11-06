<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    class Design extends Template
    {
        /**
         * the beginn of the main template
         *
         * @access public
         * @var string
         */
        public $begin;

        /**
         * the end of the main template
         *
         * @var string
         */
        public $end;

        /**
         * the template Log object
         *
         * @access private
         * @var Log
         */
        private $log;

        /**
         * if true, the design will be auto-closed on script apport
         *
         * @access private
         * @var bool
         */
        private $autoClose;

        /**
         * true if the end was already printed
         *
         * @access private
         * @var bool
         */
        private $endPrinted;

        /**
         * initiates the Design object
         *
         * @access public
         * @global Info $info
         * @param string $title the title in the header
         * @param string $ctitle the title displayed on the page
         * @param bool $autoClose true to autoclose the the design on script-apport
         */
        public function __construct($title = '', $ctitle = '', $autoClose = true)
        {
            global $info;
            $this->endPrinted = false;
            $this->autoClose = $autoClose;
            $this->log = new Log('design');
            $this->log->write(1, 'init', "title: '$title', 'ctitle: $ctitle'");
            $design = $this->getMainTpl();
            $design = str_replace('{TITLE}', $title, $design);
            $design = str_replace('{CTITLE}', $ctitle, $design);
            $design = str_replace('{INDEXLINK}', 'http://' . $_SERVER['SERVER_NAME'] . '/', $design);
            $design = str_replace('{PAGELINK}', 'http://' . $_SERVER['SERVER_NAME'] . $info->modulSelf, $design);
            $design = str_replace('{DESIGN-DIR}', 'include/designs/' . $info->design . '/', $design);
            $parts = explode('{CONTENT}', $design);
            if (count($parts) < 2 && exists($parts[0]))
            {
                $this->log->write(1, 'error', "malformed index-template");
                $this->begin = $parts[0];
                $this->end = '';
            }
            elseif (count($parts) == 2)
            {
                $this->begin = $parts[0];
                $this->end = $parts[1];
            }
            elseif (count($parts) > 2)
            {
                $this->log->write(1, 'error', "malformed index-template");
                $this->begin = $parts[0];
                unset($parts[0]);
                $this->end = implode('{CONTENT}', $parts);
            }
            else
            {
                $this->log->write(1, 'error', "broken or missing index-template");
                throw new TemplateException('Could not process the design! Missing or currupted "index.tpl.html"');
            }
            $this->parseLinks($this->begin);
            $this->parseBoxes($this->begin);
            $this->parseMenus($this->begin);
            
            $this->parseLinks($this->end);
            $this->parseBoxes($this->end);
            $this->parseMenus($this->end);
        }

        /**
         * destructs the Design object and auto-closes the design if wanted and not already done
         *
         * @access public
         */
        public function __destruct()
        {
            if ($this->autoClose && !$this->endPrinted)
            {
                $this->printEnd();
            }
        }

        /**
         * prints the begin of the design
         *
         * @access public
         */
        public function printBegin()
        {
            $this->log->write(3, 'info', "Design-begin printed");
            echo $this->begin;
        }

        /**
         * prints the end of the design
         *
         * @access public
         */
        public function printEnd()
        {
            $this->log->write(3, 'info', "Design-end printed");
            echo $this->end;
            $this->endPrinted = true;
        }

        /**
         * adds $string to the HTMl head block
         *
         * @access public
         * @param string $string the string to add to the header
         */
        public function addToHead($string)
        {
            $this->log->write(2, 'info', "HTML-head extended");
            $this->begin = preg_replace('/<\/head>/i', $string . '</head>', $this->begin);
        }

        /**
         * adds $string to the end of the HTMl body block
         *
         * @param string $string the string to add to the end of the body
         */
        public function addToBody($string)
        {
            $this->log->write(2, 'info', "HTML-body extended");
            $this->end = preg_replace('/<\/body>/i', $string . '</body>', $this->end);
        }

        /**
         * loads the main-template
         *
         * @access private
         * @global Info $info
         * @return string the template content
         */
        private function getMainTpl()
        {
            global $info;
            if (MODE)
            {
                $file = 'include/admin/templates/index.tpl.html';
            }
            else
            {
                $file = 'include/designs/' . $info->design . '/templates/index.tpl.html';
                if (!file_exists($file))
                {
                    $file = 'include/templates/index.tpl.html';
                    if (!file_exists($file))
                    {
                        throw new TemplateException('The design main template [' . $file . '] was not found!');
                    }
                }
            }
            $this->log->write(1, 'info', "index-template: '$file'");
            return file_get_contents($file);
        }

        /**
         * parses the menus in $text
         *
         * @access private
         * @param string $text the string to parse
         */
        private function parseMenus(&$text)
        {
            $text = preg_replace_callback('/\{MENU\[(\d+?)\]\}/s', array($this, 'cback_loadMenu'), $text);
        }

        /**
         * a callback to parse the frontpage menus
         *
         * @access private
         * @global Database $db
         * @global Info $info
         * @param array $matches the matches of preg_replace_callback()
         * @return string the menu
         */
        private function cback_loadMenu($matches)
        {
            global $db, $cfg, $info;
            $menu = '';
            $menuid = &$matches[1];
            $this->log->write(1, 'info', "menu-id: '$menuid'");
            $tpl = new Template('menu' . $menuid, null, 3);
            $level = 0;
            $lastLevel = $level;
            $menuActiv = false;
            $firstLevelEntry = true;
            $firstMenu = true;
            $box = '';
            $menuStr = '';
            $table = (MODE ? 'adminnavi' : 'menus');

            if (MODE && ($info->modul == 'login' || $info->modul == 'logout'))
            {
                $menuid = 6;
            }
            
            $query = 'SELECT `type`,`address`,`name`,`extern`,`level`,`visible` FROM `PREFIX_' . $table . '` WHERE `menuid`=' . $menuid . ' ORDER BY `position`';
            $result = $db->GetData($query);
            
            foreach ($result as $row)
            {
                $level = (int) $row->level;
                if ($row->type == 1) // Menu
                {
                    if ($menuActiv)
                    {
                        $this->loadMenuHelper_finishMenu($level, $lastLevel, $tpl, $menuStr, $box, $menu, $menuActiv, $firstLevelEntry);
                    }
                    if (!$row->visible)
                    {
                        continue;
                    }
                    $menuActiv = true;
                    $params = array(
                        'NAME' => $row->name
                    );
                    $tpl->setParams($params);
                    $box = str_replace('{BOX}', "{$tpl->parts[1]}{$tpl->parts[4]}{BOX}{$tpl->parts[8]}{$tpl->parts[3]}", $tpl->getPart(0, true));

                    $this->log->write(4, 'info', "Menu-entry: type:'{$row->type}',label:'{$row->name}'");
                }
                elseif ($row->type == 4) // Box
                {
                    if ($menuActiv)
                    {
                        $this->loadMenuHelper_finishMenu($level, $lastLevel, $tpl, $menuStr, $box, $menu, $menuActiv, $firstLevelEntry);
                    }
                    if (!$row->visible)
                    {
                        continue;
                    }
                    $params = array(
                        'NAME' => $row->name,
                        'BOX' => $this->getBox($row->address)
                    );
                    $tpl->setParams($params);
                    $menu .= $tpl->getPart(0, true);

                    $this->log->write(4, 'info', "Menu-entry: type:'{$row->type}',label:'{$row->name},box:'{$row->address}'");
                }
                elseif ($row->type == 2 && $menuActiv) // Module
                {
                    if (!$row->visible)
                    {
                        continue;
                    }
                    $this->loadMenuHelper_levelCheck($firstLevelEntry, $level, $lastLevel, $tpl, $menuStr);
                    $params = array('NAME' => $row->name);
                    if (!MODE)
                    {
                        $params['ADDR'] = SEO::makeAddress($row->address, array(), true);
                    }
                    else
                    {
                        $params['ADDR'] = 'admin.php?modul=' . $row->address;
                    }
                    $tpl->setParams($params);
                    if ($row->address == $info->modul && $row->extern == 0)
                    {
                        $menuStr .= $tpl->getPart(7, true);
                    }
                    else
                    {
                        $menuStr .= $tpl->getPart($row->extern + 5, true);
                    }
                    $lastLevel = $level;

                    $this->log->write(4, 'info', "Menu-entry: type:'{$row->type}',label:'{$row->name},modul:'{$row->address}'");
                }
                elseif ($row->type == 5 && $menuActiv) // Link
                {
                    if (!$row->visible)
                    {
                        continue;
                    }
                    $this->loadMenuHelper_levelCheck($firstLevelEntry, $level, $lastLevel, $tpl, $menuStr);
                    $params = array(
                        'NAME' => $row->name,
                        'ADDR' => $row->address
                    );
                    $tpl->setParams($params);
                    $menuStr .= $tpl->getPart($row->extern + 5, true);
                    $lastLevel = $level;

                    $this->log->write(4, 'info', "Menu-entry: type:'{$row->type}',label:'{$row->name},address:'{$row->address}'");
                }
                elseif ($row->type == 3 && $menuActiv) // Self content
                {
                    if (!$row->visible)
                    {
                        continue;
                    }
                    $this->loadMenuHelper_levelCheck($firstLevelEntry, $level, $lastLevel, $tpl, $menuStr);
                    $params = array(
                        'NAME' => $row->name,
                        'ADDR' => SEO::makeAddress('self', array('cid' => $row->address), true)
                    );
                    $tpl->setParams($params);
                    if ($row->address == $info->modulParams('cid') && $row->extern == 0)
                    {
                        $menuStr .= $tpl->getPart(7, true);
                    }
                    else
                    {
                        $menuStr .= $tpl->getPart($row->extern + 5, true);
                    }
                    $lastLevel = $level;

                    $this->log->write(4, 'info', "Menu-entry: type:'{$row->type}',label:'{$row->name},CID:'{$row->address}'");
                }
            }
            if ($menuActiv)
            {
                $this->loadMenuHelper_finishMenu($level, $lastLevel, $tpl, $menuStr, $box, $menu, $menuActiv, $firstLevelEntry);
            }
            return $menu;
        }

        /**
         * a helper funktion für the frontpage menu parser
         * all params are passed by reference
         *
         * @access private
         * @param int $level the current level
         * @param int $lastLevel the last level
         * @param Template $tpl the menu template
         * @param string $menuStr the menu string
         * @param string $box the box content
         * @param string $menu the final menu string
         * @param bool $menuActiv trie if an menu is activ
         * @param bool $firstLevelEntry true if it is a first entry of a level
         */
        private function loadMenuHelper_finishMenu(&$level, &$lastLevel, &$tpl, &$menuStr, &$box, &$menu, &$menuActiv, &$firstLevelEntry)
        {
            $levelCloseStr = '';
            for ($i = 0; $i < $lastLevel; $i++)
            {
                $levelCloseStr .= $tpl->getPart(8) . $tpl->getPart(3);
            }
            $menu .= str_replace('{BOX}', $menuStr . $levelCloseStr, $box);
            $menuActiv = false;
            $menuStr = '';
            $level = 0;
            $lastLevel = $level;
            $firstLevelEntry = true;
        }

        /**
         * a helper for the frontpage menu parser
         * all params are passed by reference
         *
         * @param bool $firstLevelEntry true if it is a first entry of a level
         * @param int $level the current level
         * @param int $lastLevel the last level
         * @param Template $tpl the menu template
         * @param string $menuStr the menu string
         */
        private function loadMenuHelper_levelCheck(&$firstLevelEntry, &$level, &$lastLevel, &$tpl, &$menuStr)
        {
            if ($level > $lastLevel)
            {
                if (!$firstLevelEntry)
                {
                    $menuStr .= $tpl->getPart(2);
                }
                for ($i = $lastLevel; $i < $level; $i++)
                {
                    $menuStr .= $tpl->getPart(1) . $tpl->getPart(4);
                }
            }
            elseif ($level < $lastLevel)
            {
                for ($i = $level; $i < $lastLevel; $i++)
                {
                    $menuStr .= $tpl->getPart(8) . $tpl->getPart(3);
                }
                $menuStr .= $tpl->getPart(8) . $tpl->getPart(4);
            }
            else
            {
                if (!$firstLevelEntry)
                {
                    $menuStr .= $tpl->getPart(8) . $tpl->getPart(4);
                }
            }
            if ($firstLevelEntry)
            {
                $firstLevelEntry = false;
            }
        }
    }
?>
