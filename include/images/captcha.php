<?php
//Konstanten
    define('MODE', 0);
    define('DEBUG', 0);

//PHP-Konfigurationen
    @error_reporting(DEBUG ? E_ALL : 0);
    date_default_timezone_set('Europe/Paris');
    @ini_set('default_charset', 'UTF-8');

    require_once '../includes/loader.php';
    
    $db = new Database(DB_HOST, DB_USER, DB_PASS, DB_DATABASE, DB_PREFIX, true);
    $cfg = new Config();

    $availableChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
    $captchaString = '';
    for ($i = 0; $i < $cfg->cms_captcha_length; $i++)
    {
        $index = mt_rand(0, mb_strlen($availableChars) - 1);
        $captchaString .= $availableChars[$index];
    }
    
    session_name('sid');
    session_start();
    $_SESSION['ci_captcha'] = mb_strtolower($captchaString);

    define('CHAR_WIDTH', 15);
    define('CHAR_HEIGHT', 30);


    $captcha = imagecreate($cfg->cms_captcha_length * CHAR_WIDTH, CHAR_HEIGHT);
    $r = mt_rand(220, 255);
    $g = mt_rand(220, 255);
    $b = mt_rand(220, 255);
    $bgcolor = imagecolorallocate($captcha, $r, $g, $b);

    for ($i = 0; $i < mb_strlen($captchaString); $i++)
    {
        $img = imagecreatetruecolor(CHAR_WIDTH, CHAR_HEIGHT);
        imagefill($img, 0, 0, $bgcolor);
        $color = imagecolorallocate($img, mt_rand(100, 255), mt_rand(100, 255), mt_rand(100, 255));
        imagestring($img, mt_rand(2, 5), 5, mt_rand(2, CHAR_HEIGHT / 2), $captchaString[$i], $color);
        imagecopy($captcha, $img, $i * CHAR_WIDTH, 0, 0, 0, CHAR_WIDTH, CHAR_HEIGHT);
        imagecolordeallocate($img, $color);
        imageDestroy($img);
    }

    header('Content-type: image/png');
    imagepng($captcha);
    imageDestroy($captcha);
?>
