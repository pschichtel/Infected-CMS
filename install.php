<?php
    define('STEP', (isset($_GET['step']) ? $_GET['step'] : 1));
    define('STATUS', (isset($_GET['status']) ? htmlspecialchars(rawurldecode($_GET['status'])) : ''));
    //define('RESOURCE', 'http://quick-wango.dyndns.org/cms/');
    define('TARGET', './');
    define('MODE', 1);
    define('DEBUG', 0);
    //define('VERSION', file_get_contents(RESOURCE . 'version.txt'));
    ob_start();
    session_name('sid');
    session_start();
    @ini_set('allow_url_fopen', 1);
    @ini_set('max_execution_time', 120);
    error_reporting(E_ALL);
    if (!isset($_SESSION['last_step']))
    {
        $_SESSION['last_step'] = 0;
    }
    
    function headerTo($address)
    {
        $header = 'Location: ' . $address;
        if (preg_match('/\?/i', $address))
        {
            $header .= '&';
        }
        else
        {
            $header .= '?';
        }
        $header .= session_name() . '=' . session_id();
        header($header);
        ob_end_clean();
        exit;
    }
    function currentDir()
    {
        $dir = getcwd() . '/';
        $dir = str_replace('\\', '/', $dir);
        $dir = str_replace('//', '/', $dir);
        return $dir;
    }
    function checkNeededFiles()
    {
        if (
            !file_exists(TARGET . 'include/includes/interfaces/sql.interface.php') ||
            !file_exists(TARGET . 'include/includes/exceptions/db.exception.php') ||
            !file_exists(TARGET . 'include/includes/classes/log.class.php') ||
            !file_exists(TARGET . 'include/includes/classes/mysql.class.php') ||
            !file_exists(TARGET . 'include/includes/funcs/secure.func.php') ||
            !file_exists(TARGET . 'install.sql')
        )
        {
            return false;
        }
        return true;
    }
?><html>
<head>
    <title>Infected CMS Installer :: Code Infection :: Schritt <?php echo STEP; ?>/6</title>
    <meta http-equiv="Content-type" content="application/xhtml+xml;charset=UTF-8" />
    <style type="text/css">
    /*<![CDATA[*/
        html, body {
            padding: 0;
            margin: 0;
            font-size: 100%;
        }
        body {
            text-align: center;
        }
        #container {
            width: 80%;
            margin: 0.5cm auto;
            text-align: left;
            border: 1px outset black;
            padding: 3px;
        }
        #header {
            padding: 20px;
            background-color: rgb(230,230,230);
        }
        #content {
            padding: 5px 10%;
        }
        #cbody {
            padding: 4px;
            background-color: rgb(200,200,200);
        }
        #footer {
            padding: 15px;
            background-color: rgb(230,230,230);
        }
        #lizenzbox {
            margin: 5px 5%;
            border: 1px solid black;
            height: 400px;
            overflow: auto;
            padding: 2px;
        }
        #dateibox {
            margin: 5px 5%;
            border: 1px solid black;
            height: 200px;
            overflow: auto;
            padding: 2px;
        }
        #do_submit {
            border: 1px solid white;
            width: 100px !important;
        }
        #status_set {
            border: 2px solid red;
            padding: 5px;
            text-align: center;
        }
        #status_unset {
            display: none;
        }
        .formular {
            margin-bottom: 5px;
        }
        .formular label {
            width: 160px;
            float: left;
            text-align: right;
        }
        .formular br {
            clear: left;
        }
        .formular hr {
            border: 1px ridge silver;
            margin-left: -8px;
            margin-right: -8px;
        }
        .formular input, .mysqldaten textarea, .mysqldaten select {
            width: 300px;
            margin-left: 10px;
            font-family: arial, serif;
            font-size: 0.95em;
        }
        .formular select {
            width: 306px;
        }
    /*]]>*/
    </style>
    <script type="text/javascript">
    //<![CDATA[
        function doSubmit(formElem)
        {
            alert('submitted');
            var inputs = formElem.getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++)
            {
                var value = inputs[i].replace(/^\s+?/g, '');
                value = value.replace(/\s+?$/g, '');
                if (value == '')
                {
                    alert('Es wurden nicht alle Daten angegeben!');
                    return false;
                }
            }
            return true;
        }
    //]]>
    </script>
</head>
<body>
    <div id="container">
        <div id="header"><h2>Infected CMS Installer :: Code Infection :: Schritt <?php echo STEP; ?>/6</h2></div>
        <div id="content">
            <div id="status_<?php echo (STATUS !== '' ? 'set' : 'unset'); ?>"><?php echo STATUS; ?></div>
            <div id="cbody">

<?php
/*
 *      SCHRITT 1  [WELCOME + LIZENZ]
 */
if (STEP == 1)
{
    if (isset($_POST['do']))
    {
        if (!checkNeededFiles())
        {
            headerTo('install.php?step=1&status=' . rawurlencode('Es fehlen benötigte Dateien!'));
        }
        elseif (!isset($_POST['lizenz']))
        {
            headerTo('install.php?step=1&status=' . rawurlencode('Sie müssen die Lizenzbedingungen aktzeptieren um fortzufahren!'));
        }
        $_SESSION['last_step'] = 1;
        headerTo($_SERVER['PHP_SELF'] . '?step=2');
    
    }
    ?>
    
    <h3>Willkommen</h3>
    <div>Willkommen bei der Installation des Infected CMS!<br />Dieser Installationsassistent wird sie durch die Installation des CMS führen.</div>
    <h3>Lizenz Vereinbarung:</h3>
    Um die Installation durchzuführen müssen sie mit den folgenden Lizenzbestimmungen einverstanden sein!
    <div id="lizenzbox"><?php echo nl2br(htmlspecialchars(file_get_contents('./license.txt'))); ?></div>
    <form action="<?PHP echo $_SERVER['REQUEST_URI'] ?>" method="post">
        <input type="checkbox" name="lizenz" /> Einverstanden mit den Lizenzbedingungen<br /><br />
        <input type="submit" name="do" value="Weiter" id="do_submit" />
    </form>
    
    <?php
}
/*
 *      SCHRITT 2  [DB-DATEN + DB-TEST + DB-CONFIG]
 */
elseif (STEP == 2 && $_SESSION['last_step'] == 1)
{
    if (isset($_POST['do']))
    {
        $errmsg = rawurlencode('Es fehlt eine oder mehere Angaben! Sie müssen alle Daten angeben damit die Installation fortgeführt werden kann.');
        if (trim($_POST['host']) === '')
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=2&status=' . $errmsg);
        }
        if (trim($_POST['name']) === '')
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=2&status=' . $errmsg);
        }
        if (trim($_POST['user']) === '')
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=2&status=' . $errmsg);
        }
        if (trim($_POST['prefix']) === '')
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=2&status=' . $errmsg);
        }
        if (!is_writeable('include/includes/configs/') || !is_writable('include/logs/'))
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=2&status=' . rawurlencode('Es werden Schreibrechte für die Verzeichnisse "include/logs" und "include/includes/configs" benötigt!'));
        }
        include 'include/includes/interfaces/sql.interface.php';
        include 'include/includes/exceptions/db.exception.php';
        include 'include/includes/classes/log.class.php';
        include 'include/includes/classes/mysql.class.php';
        $db = new Database($_POST['host'], $_POST['user'], $_POST['pass'], $_POST['name'], $_POST['prefix'] . '_', true);
        
        if (!$db->checkConnection())
        {
            $errmsg = rawurlencode('Es konnte keine Testverbindung zur Datenbank hergestellt werden! geben sie die Daten bitte erneut ein.');
            headerTo($_SERVER['PHP_SELF'] . '?step=2&status=' . $errmsg);
        }
        
        while (true)
        {
            $result = $db->query("SHOW TABLES LIKE '{$_POST['prefix']}_%'");
            if ($db->num_rows($result) > 0)
            {
                $_POST['prefix'] = 'ci-' . mt_rand(1000, 9999);
            }
            else
            {
                break;
            }
        }
        
        $config = "<?php\n";
        $config .= "    defined('MODE') or die('<strong>Access denied!</strong>');\n\n";
        $config .= "    define('DB_HOST', '{$_POST['host']}');\n";
        $config .= "    define('DB_USER', '{$_POST['user']}');\n";
        $config .= "    define('DB_PASS', '{$_POST['pass']}');\n";
        $config .= "    define('DB_DATABASE', '{$_POST['name']}');\n";
        $config .= "    define('DB_PREFIX', '{$_POST['prefix']}_');\n";
        $config .= "?>";
        
        file_put_contents('include/includes/configs/db.conf.php', $config);

        $sql = file_get_contents('install.sql');
        file_put_contents('install.sql', str_replace('{PREFIX}', $_POST['prefix'], $sql));

        $_SESSION['last_step'] = 2;
        headerTo($_SERVER['PHP_SELF'] . '?step=3');
    }
    ?>
    <form action="<?PHP echo $_SERVER['PHP_SELF']; ?>?step=2" method="post">
    <fieldset class="formular">
        <legend>MySQL Daten eingeben</legend>
        <label for="host">Datenbank-Host*</label>
        <input type="text" id="host" name="host" value="localhost" /><br />
        <label for="name">Datenbank-Name</label>
        <input type="text" id="name" name="name" /><br />
        <label for="user">Datenbank-Benutzer</label>
        <input type="text" id="user" name="user" /><br />
        <label for="pass">Datenbank-Passwort</label>
        <input type="text" id="pass" name="pass" /><br />
        <label for="prefix">Datenbank-Prefix**</label>
        <input type="text" id="prefix" name="prefix" value="ci-<?php echo mt_rand(1000, 9999); ?>" readonly="readonly" /><br />
    </fieldset>
    <input type="submit" name="do" id="do_submit" value="Weiter" />
    </form>
    <div>* Der Datenbank-Host muss in der Regel <span style="text-decoration:underline;">nicht</span> geändert werden.<br />** Es wird automatisch ein anderer Prefix gewählt falls dieser bereits verwendet wird!</div>
    <?php
}
/*
 *      SCHRITT 3  [KONFIGURATIONEN]
 */
elseif (STEP == 3 && $_SESSION['last_step'] == 2)
{
    if (isset($_POST['do']))
    {
        $errmsg = rawurlencode('Es fehlt eine oder mehere Angaben! Sie müssen alle Daten angeben damit die Installation fortgeführt werden kann.');
        if (trim($_POST['dbclass']) === '')
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=3&status=' . $errmsg);
        }
        if (trim($_POST['lang']) === '')
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=3&status=' . $errmsg);
        }
        if (trim($_POST['index']) === '')
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=3&status=' . $errmsg);
        }
        if (trim($_POST['salt']) === '')
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=3&status=' . $errmsg);
        }
        if (trim($_POST['host']) === '')
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=3&status=' . $errmsg);
        }
        if (trim($_POST['port']) === '')
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=3&status=' . $errmsg);
        }
        if (trim($_POST['from']) === '')
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=3&status=' . $errmsg);
        }

        $corecfg  = "<?php\n";
        $corecfg .= "    defined('MODE') or die('<strong>Access denied!</strong>');\n\n";
        $corecfg .= "    define('CI_VERSION',        '" . VERSION . "');\n";
        $corecfg .= "    define('CI_DB_CLASS',       '{$_POST['dbclass']}');\n";
        $corecfg .= "    define('CI_INDEXFILE',      '{$_POST['index']}');\n";
        $corecfg .= "    define('CI_STD_LANG',       '{$_POST['lang']}');\n";
        $corecfg .= "    define('CI_STATIC_SALT',    '{$_POST['salt']}');\n";
        $corecfg .= "    define('CI_TIME_ZONE',      '{$_POST['tzone']}');\n";
        $corecfg .= "    define('CI_CHARSET',        '{$_POST['charset']}');\n";
        $corecfg .= "    define('CI_SESSIONNAME',    '{$_POST['sess']}');\n";
        $corecfg .= "    define('CI_LOGSIZELIMIT',   '{$_POST['logsize']}');\n";
        $corecfg .= '?>';
        
        $smtpcfg  = ";<?php die('<strong>Access denied!</strong>'); ?>\n";
        $smtpcfg .= "[smtp config]\n";
        $smtpcfg .= "smtp_host = \"{$_POST['host']}\"\n";
        $smtpcfg .= "smtp_port = {$_POST['port']}\n";
        $smtpcfg .= "smtp_user = \"{$_POST['user']}\"\n";
        $smtpcfg .= "smtp_pass = \"{$_POST['pass']}\"\n";
        $smtpcfg .= "smtp_from = \"{$_POST['from']}\"\n";
        
        file_put_contents('include/includes/configs/core.conf.php', $corecfg);
        file_put_contents('include/includes/configs/smtp.conf.php', $smtpcfg);

        $sql = file_get_contents('install.sql');
        file_put_contents('install.sql', str_replace('{STDLANG}', $_POST['lang'], $sql));
        
        $_SESSION['last_step'] = 3;
        headerTo($_SERVER['PHP_SELF'] . '?step=4');
    }
    ?>
    <h3>Erweiterte Konfiguration</h3>
    <form action="<?PHP echo $_SERVER['PHP_SELF']; ?>?step=3" method="post">
    <fieldset class="formular">
        <legend>Kern-Konfiguration</legend>
        <label for="dbclass">Datenbank-Klasse*</label>
        <input type="text" id="dbclass" name="dbclass" value="mysql" readonly="readonly" /><br />
        <label for="lang">Standardsprache*</label>
        <input type="text" id="lang" name="lang" value="de" readonly="readonly" /><br />
        <label for="index">Index-Datei des CMS*</label>
        <input type="text" id="index" name="index" value="index.php" /><br />
        <label for="salt">Statisches Salt für die Passwortverschlüsselung**</label>
        <input type="text" id="salt" name="salt" value="WKobxlp9Gr1JH41VCC,@i*6I714N7@744GIbYbkUbLTO5UU4uv/E0Y(h:F;Fao*OReDK?LP66ajhDvb-3IF6FM%zEk*8ZYf>@30@sH-Gs%s4DAiO##1*4s029UX6iWUq*rO>8&5V:Ut-&Wa)v,&qVS1MVNy-IwH@Y6-Gs%s43&k6CVIELQCY5cM30XW97LY1j0l2OU<<9c5uYGHk:e3lNTe4R4LNT*f!9VoOFfO3<6CM8kfVAM<5%6Vve86+gKm*m(Bnce?E3AiWRLRZjV5yvZT98P/1!g!ZtGs%s4?;h5%" /><br />
        <label for="tzone">Zeitzone</label>
        <input type="text" id="tzone" name="tzone" value="Europe/Paris" /><br />
        <label for="charset">Zeichensatz</label>
        <input type="text" id="charset" name="charset" value="UTF-8" readonly="readonly" /><br />
        <label for="sess">Sessionname</label>
        <input type="text" id="sess" name="sess" value="sid" /><br />
        <label for="logsize">Limit der Logdateien [in Bytes]</label>
        <input type="text" id="logsize" name="logsize" value="5242880" /><br />
    </fieldset>
    <fieldset class="formular">
        <legend>SMTP-Konfiguration</legend>
        <label for="host">SMTP-Host*</label>
        <input type="text" id="host" name="host" value="localhost" /><br />
        <label for="port">SMTP-Port*</label>
        <input type="text" id="port" name="port" value="25" /><br />
        <label for="user">SMTP-Benutzer*</label>
        <input type="text" id="user" name="user" value="" /><br />
        <label for="pass">SMTP-Passwort*</label>
        <input type="text" id="pass" name="pass" value="" /><br />
        <label for="from">SMTP-Absender</label>
        <input type="text" id="from" name="from" value="" /><br />
    </fieldset>
    <input type="submit" name="do" id="do_submit" value="Weiter" />
    </form>
    <div>* Diese Einstellungen müssen normalerweise <span style="text-decoration:underline;">nicht</span> geändert werden.<br />** Dies sollte unbedingt geändert werden und eine möglichst komplexe Zeichenfolge sein!</div>
    <?php
}
/*
 *      SCHRITT 4  [ADMIN ACCOUNT]
 */
elseif (STEP == 4 && $_SESSION['last_step'] == 3)
{
    if (isset($_POST['do']))
    {
        $err_fieldmissing = rawurlencode('Es fehlt eine oder mehere Angaben! Sie müssen alle Daten angeben damit die Installation fortgeführt werden kann.');
        $err_passnotequal = rawurlencode('Das Passwort und dessen Wiederholung stimmen nicht überein!');
        if (trim($_POST['name']) === '')
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=4&status=' . $err_fieldmissing);
        }
        if ($_POST['pass'] === '')
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=4&status=' . $err_fieldmissing);
        }
        if ($_POST['repass'] === '')
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=4&status=' . $err_fieldmissing);
        }
        if (trim($_POST['pass']) === '')
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=4&status=' . $err_fieldmissing);
        }
        if ($_POST['pass'] !== $_POST['repass'])
        {
            headerTo($_SERVER['PHP_SELF'] . '?step=4&status=' . $err_passnotequal);
        }

        require_once 'include/includes/configs/core.conf.php';
        require_once 'include/includes/funcs/secure.func.php';
        list($dynsalt, $pass) = explode('|', password($_POST['pass']));
        $name = &$_POST['name'];

        $vars = array(
            '{ADMINNAME}',
            '{ADMINPWD}',
            '{PWD_DYNSALT}',
            '{ADMINGROUP}'
        );
        $replace = array(
            $name,
            $pass,
            $dynsalt,
            'mainadmins'
        );

        $sql = file_get_contents('install.sql');
        file_put_contents('install.sql', str_replace($vars, $replace, $sql));

        $_SESSION['last_step'] = 4;
        headerTo($_SERVER['PHP_SELF'] . '?step=5');
    }
    ?>
    <form action="<?PHP echo $_SERVER['PHP_SELF']; ?>?step=4" method="post">
    <fieldset class="formular">
        <legend>Adminitrator-Account anlegen</legend>
        <label for="name">Loginname</label>
        <input type="text" id="name" name="name" value="admin" /><br />
        <label for="pass">Passwort</label>
        <input type="password" id="pass" name="pass" /><br />
        <label for="repass">Wiederholung</label>
        <input type="password" id="repass" name="repass" /><br />
    </fieldset>
    <input type="submit" name="do" id="do_submit" value="Weiter" />
    </form>
    <div></div>
    <?php
}
/*
 *      SCHRITT 5  [FINALISIERUNG (INSTALL DB + INSTALLATIONSDATEIEN LÖSCHEN)]
 */
elseif (STEP == 5 && $_SESSION['last_step'] == 4)
{
    if (isset($_POST['do']))
    {
        require_once 'include/includes/configs/db.conf.php';
        require_once 'include/includes/configs/core.conf.php';
        require_once 'include/includes/interfaces/sql.interface.php';
        require_once 'include/includes/classes/log.class.php';
        require_once 'include/includes/exceptions/db.exception.php';
        require_once 'include/includes/classes/' . CI_DB_CLASS . '.class.php';
        function no_statement($string)
        {
            return (!empty($string));
        }
        $sql = file_get_contents('install.sql');
        $sql = preg_replace("/(\015\012|\015|\012)/", "\n", $sql);
        
        $queries = explode(";\n", $sql);

        $queries = array_map('trim', $queries);
        $queries = array_filter($queries, 'no_statement');
        
        
        $db = new Database(DB_HOST, DB_USER, DB_PASS, DB_DATABASE, DB_PREFIX, true);
        $i = 0;
        foreach ($queries as $query)
        {
            $db->query($query) or die(($i + 1) . ' failed :/ Msg:<br />' . nl2br($db->error()) . '<hr />' . $query);
            $i++;
        }

        unlink('install.sql');
        $dir = getcwd();
        @chdir('include/includes/configs');
        $configs = glob('*');
        foreach ($configs as $file)
        {
            chmod($file, 0644);
        }
        chdir($dir);
        @chmod('include/includes/configs', 0644);
    
        $_SESSION['last_step'] = 5;
        headerTo($_SERVER['PHP_SELF'] . '?step=6');
    }
    ?>
    <h3>Fertig zur Installation:</h3>
    Es wurden nun alle Vorbereitungen für die Installation des CMS auf Ihrem Webspace abgeschlossen.<br />Nun wird die Datenbank gemäß Ihren Angaben eingerichtet, die Installationsdateien gelöscht und letzten Endes noch die Zugriffsrechte einiger Verzeichnisse normalisiert.
    <form action="<?PHP echo $_SERVER['REQUEST_URI']; ?>" method="post">
        <input type="submit" name="do" value="Starte Installation!" id="do_submit" style="width: 150px !important;margin-top: 10px;" />
    </form>
    <?php
}
/*
 *      SCHRITT 6  [DONE]
 */
elseif (STEP == 6 && $_SESSION['last_step'] == 5)
{
    if (isset($_POST['do']))
    {
        unlink(__FILE__);
        unset($_SESSION['last_step']);
        headerTo(dirname($_SERVER['PHP_SELF']));
    }
    ?>

    <h3>Installation abgeschlossen</h3>
    Die Installation wurde abgeschlossen. Falls ihnen irgendwelche Fehler angezeigt wurden, so versuchen Sie es erneut!<br /> Mit dem folgenden Button wird der Installer sich selbst löschen.<br />Je nach Serverkonfiguration kann es sein, das die Dateirechte nicht automatisch geändert werden können. Deshalb sollten die prüfen ob das Verzeichnis "include/includes/configs/" und alle enthaltenen Elemente folgende Zugriffsrechte haben:<br />-rw-r--r-- (644) bzw. drw-r--r-- (644)
    <form action="<?PHP echo $_SERVER['REQUEST_URI']; ?>" method="post">
        <input type="submit" name="do" value="Abschließen und zur Seite gehen!" id="do_submit" style="width: 220px !important;margin-top: 10px;" />
    </form>
    <?php
}
/*
 *      SCHRITT FAIL
 */
else
{
    if (isset($_POST['return']))
    {
        $_SESSION['last_step'] = (isset($_SESSION['last_step']) ? $_SESSION['last_step'] : 0);
        headerTo($_SERVER['PHP_SELF'] . '?step='. $_SESSION['last_step'] + 1);
    }
    ?>
    
    <h3>Fehler bei der Installation:</h3>
    Die Schritte der Installation müssen Schritt für Schritt durchgeführt werden, damit das System richtig installiert und eingerichtet wird!
    <form action="install.php?step=1" method="post">
        <input type="submit" name="return" value="Zurück" id="do_submit" />
    </form>
    
    <?php
}

?>
            </div>
        </div>
        <div id="footer"></div>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>