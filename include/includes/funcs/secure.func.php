<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    /**
     * generates a salted hash from $text with $dynSalt and returns it together
     * with the dynamic salt delimted by a '|'
     * if $dynSalt is not given, it will by created
     *
     * @param string $text the text to hash
     * @param string $dynSalt the dynamic salt to recalculate a hash
     * @return string the dynamic salt an the password hash combined with a '|'
     */
    function password($text, $dynSalt = null)
    {
        $password = '';
        if (is_null($dynSalt))
        {
            $dynSalt = md5(microtime());
        }
        elseif (mb_strpos($dynSalt, '|') !== false)
        {
            $dynSalt = mb_substr($dynSalt, 0, mb_strpos($dynSalt, '|'));
        }
        else
        {
            return false;
        }
        $password = $dynSalt;
        $password .= '|';
        $password .= hash('SHA512', $dynSalt . $text . CI_STATIC_SALT);

        return $password;
    }
?>
