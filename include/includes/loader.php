<?php
    defined('MODE') or die('<strong>Access denied!</strong>');
    if (!defined('__DIR__'))
    {
        define('__DIR__', dirname(__FILE__));
    }

//CONFIGs
    require_once __DIR__ . '/configs/core.conf.php';
    require_once __DIR__ . '/configs/db.conf.php';
//FUNCs
    require_once __DIR__ . '/funcs/common.func.php';
    require_once __DIR__ . '/funcs/stats.func.php';
    require_once __DIR__ . '/funcs/modulcallbacks.func.php';
    require_once __DIR__ . '/funcs/lister.func.php';
    require_once __DIR__ . '/funcs/secure.func.php';
    require_once __DIR__ . '/funcs/bbcode.func.php';
//LIBs
    require_once __DIR__ . '/libs/categories.lib.php';
    require_once __DIR__ . '/libs/contents.lib.php';
    require_once __DIR__ . '/libs/comments.lib.php';
    require_once __DIR__ . '/libs/user.lib.php';
    require_once __DIR__ . '/libs/seo.lib.php';
    require_once __DIR__ . '/libs/debug.lib.php';
    require_once __DIR__ . '/libs/smtp.lib.php';
    require_once __DIR__ . '/libs/ipbase.lib.php';
    require_once __DIR__ . '/libs/text.lib.php';
//INTERFACEs
    require_once __DIR__ . '/interfaces/sql.interface.php';
//EXCEPTIONs
    require_once __DIR__ . '/exceptions/db.exception.php';
    require_once __DIR__ . '/exceptions/config.exception.php';
    require_once __DIR__ . '/exceptions/notimplemented.exception.php';
    require_once __DIR__ . '/exceptions/io.exception.php';
    require_once __DIR__ . '/exceptions/template.exception.php';
//CLASSes
    require_once __DIR__ . '/classes/' . CI_DB_CLASS . '.class.php';
    require_once __DIR__ . '/classes/config.class.php';
    require_once __DIR__ . '/classes/template.class.php';
    require_once __DIR__ . '/classes/design.class.php';
    require_once __DIR__ . '/classes/info.class.php';
    require_once __DIR__ . '/classes/lang.class.php';
    require_once __DIR__ . '/classes/log.class.php';
    require_once __DIR__ . '/classes/stringparser_bbcode.class.php';
?>