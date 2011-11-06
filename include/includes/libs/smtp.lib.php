<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    class SMTP
    {
        /**
         * Email successfully sent
         */
        const OK                =  0;
        /**
         * Remote connection failed
         */
        const CONNECTION_FAILED = -1;
        /**
         * failed at HELO
         */
        const HELO_FAILED       = -2;
        /**
         * incorrect username was given and login failed
         */
        const WRONG_USER        = -3;
        /**
         * incorrect password was given and login failed
         */
        const WRONG_PASS        = -4;
        /**
         * failed at RCPT command, this has to be a valid email address as well
         */
        const WRONG_FROM        = -5;
        /**
         * failed at DATA command, maybe there were invalid or broken characters given (charset mismatches?)
         */
        const WRONG_RCPT        = -6;
        /**
         * completation of the data block failed
         */
        const DATA_FAILED       = -7;
        /**
         * completation of the data block failed
         */
        const COMPLETE_FAILED   = -8;
        /**
         * an unknown error occurred
         */
        const UNKNOWN           = -9;
        

        /**
         * sends an email with the given data
         *
         * @param string $to address to send to
         * @param string $from the sender address
         * @param string $subject the subject of the email
         * @param string $text the email content
         * @param array $headers additional headers
         * @return int return code
         */
        public static function mail($to, $from, $subject, $text, $headers = null)
        {
            $smtp_cfg = parse_ini_file('include/includes/configs/smtp.conf.php');

            $smtp = fsockopen($smtp_cfg['smtp_host'], $smtp_cfg['smtp_port'], $errno, $errstr, 3);
            if ($smtp === false)
            {
                return self::CONNECTION_FAILED;
            }
            if (!self::parse(self::receive($smtp), 220))
            {
                return self::UNKNOWN;
            }
            if (!self::send($smtp, 'HELO ' . $_SERVER['SERVER_NAME'], 250))
            {
                return self::HELO_FAILED;
            }
            if ($smtp_cfg['smtp_user'] && $smtp_cfg['smtp_pass'])
            {
                if (!self::send($smtp, 'AUTH LOGIN', 334))
                {
                    return self::UNKNOWN;
                }
                if (!self::send($smtp, base64_encode($smtp_cfg['smtp_user']), 334))
                {
                    return self::WRONG_USER;
                }
                if (!self::send($smtp, base64_encode($smtp_cfg['smtp_pass']), 235))
                {
                    return self::WRONG_PASS;
                }
            }
            if (!self::send($smtp, 'MAIL FROM: ' . $smtp_cfg['smtp_from'] . '', 250))
            {
                return self::WRONG_FROM;
            }
            if (!self::send($smtp, 'RCPT TO: ' . $to . '', 250))
            {
                return self::WRONG_RCPT;
            }
            if (!self::send($smtp, 'DATA', 354))
            {
                return self::DATA_FAILED;
            }

            self::send($smtp, 'Subject: ' . $subject, 0);
            self::send($smtp, 'To: ' . $to, 0);
            self::send($smtp, 'From: ' . $from, 0);
            if (is_array($headers))
            {
                self::send($smtp, implode("\r\n", $headers), 0);
            }
            self::send($smtp, "\r\n", 0);
            self::send($smtp, $text, 0);

            if (!self::send($smtp, '.', 250))
            {
                return self::COMPLETE_FAILED;
            }
            self::send($smtp, 'QUIT', 0);
            fclose($smtp);
            return self::OK;
        }

        /**
         * sends $command and checks whether $successcode is equal to the returned code
         *
         * @param resource $handle the socket handle
         * @param string $command the command to send
         * @param int $sucesscode the code which the server returns on success
         * @return bool true on success, false on failure
         */
        protected static function send($handle, $command, $sucesscode)
        {
            fputs($handle, $command . "\r\n");
            if ($sucesscode)
            {
                $response = self::receive($handle);
                if (self::parse($response, $sucesscode))
                {
                    return true;
                }
                else
                {
                    fclose($handle);
                    return false;
                }
            }
        }

        /**
         * receives data from the SMTP server
         *
         * @param resource $handle the socket handle
         * @return string the received data as a trimed string
         */
        protected static function receive(&$handle)
        {
            $response = '';
            while($tmp = trim(fgets($handle, 513)))
            {
                $response .= $tmp;
                if (mb_substr($tmp, 3, 1) == ' ')
                {
                    break;
                }
            }
            return trim($response);
        }

        /**
         * parses $response and checks the $code
         *
         * @param string $response the response data
         * @param code $code the needed code
         * @return bool true if the code matches, otherwise false
         */
        protected static function parse($response, $code)
        {
            $responsecode = mb_substr($response, 0, 3);
            if (trim($responsecode) == trim($code))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * a simple wrapper of smtp_mail()
         *
         * @access public
         * @static
         * @param string $to the email target
         * @param string $from the sender
         * @param string $subject the email subject
         * @param string $text the content
         * @param string $charset the charset (default: UTF-8)
         * @return int false if $to or $from are no valid email addresses, otherwise the return code of smtp_mail()
         */
        public static function sendmail($to, $from, $subject, $text, $charset = CI_CHARSET)
        {
            if (!Text::is_email($to) || !Text::is_email($from))
            {
                return false;
            }
            list($name, $host) = explode('@', $to);
            $name = ucwords(str_replace(array('_', '.', '-'), ' ', $name));
            $to = $name . '<' . $to . '>';

            list($name, $host) = explode('@', $from);
            $name = ucwords(str_replace(array('_', '.', '-'), ' ', $name));
            $from = $name . '<' . $from . '>';

            return SMTP::mail($to, $from, $subject, $text, array('Content-type:text/plain;charset=' . $charset));
        }
    }
?>
