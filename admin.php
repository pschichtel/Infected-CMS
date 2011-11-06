<?php
    $rt_s = strtok(microtime(), ' ') + strtok(' ');

//Konstanten
    define('MODE', 1);
    define('DEBUG', 5);

//Loader
    require_once('include/includes/loader.php');

//PHP-Konfigurationen
    error_reporting(-1);
    date_default_timezone_set(CI_TIME_ZONE);
    ini_set('default_charset', CI_CHARSET);
    mb_internal_encoding(CI_CHARSET);

//Error-Handler setzen
    set_error_handler(array('Debug', 'error_handler'), -1);
    set_exception_handler(array('Debug', 'exception_handler'));

//Buffer starten
    ob_start('ob_gzhandler');

//Session starten
    session_name(CI_SESSIONNAME);
    session_start();
    output_add_rewrite_var(CI_SESSIONNAME, session_id());

//KLasseninstanzierungen
    /**
     * @global Log $log
     */
    $log = new Log('admin');
    $log->write(0, 'requested', $_SERVER['REQUEST_URI']);
    /**
     * @global Database $db
     */
    $db = new Database(DB_HOST, DB_USER, DB_PASS, DB_DATABASE, DB_PREFIX, true);
    /**
     * @global Config $cfg
     */
    $cfg = new Config();
    /**
     * @global Info $info
     */
    $info = new Info();

//Modul ausführen
    require_once $info->modulFile;

//Laufzeit berechnen
    $rt_e = strtok(microtime(), ' ') + strtok(' ');
    $log->write(0, 'runtime', number_format($rt_e - $rt_s, 6));

//Buffer flushen
    ob_end_flush();
?>
