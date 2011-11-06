<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    class Text
    {
        /**
         * wraps the words in $text
         *
         * @access public
         * @static
         * @global Config $cfg
         * @param string $text the text to chunk words in
         * @return string the chunked text
         */
        public static function simpleChunk($text)
        {
            global $cfg;
            return preg_replace('/([\S]{' . $cfg->txt_split_index . '})/', '$1 ', $text);
        }

        /**
         * replaces the smiles in $text
         *
         * @access public
         * @static
         * @global Database $db
         * @param string $text the text to parse smilies in
         * @return string the parsed text
         */
        public static function smilies($text)
        {
            global $db;
            $query = 'SELECT `smile`,`file` FROM `PREFIX_smiles`';
            $result = $db->GetData($query);
            foreach ($result as $row)
            {
                $replacement = '<img src="include/images/smiles/' . $row->file . '" class="smiles" alt="' . $row->smile . '" title="' . $row->smile . '" />';
                $text = preg_replace('/(\A|\s)' . preg_quote($row->smile, '/') . '(\Z|\s)/', '$1' . $replacement . '$2', $text);
            }
            return $text;
        }

        /**
         * replaces newlines (\r\n, \n and \r) with <br /> in $text
         *
         * @access public
         * @static
         * @param string $text the text to replace newline charaters in
         * @return string the parsed text
         */
        public static function nl2br($text)
        {
            return preg_replace("/(\r?\n)|(\r)/", '<br />', $text);
        }

        /**
         * encodes $uri in a simpler way like urlencode()
         *
         * @access public
         * @static
         * @param string $uri the URI to encode
         * @return string the encodes URI
         */
        public static function simpleUriEncode($uri)
        {
            $uri = str_replace(' ', '%20', $uri);
            $uri = self::escapeAmp($uri);
            return $uri;
        }

        /**
         * entityencodes just the '&' in $text
         *
         * @access public
         * @static
         * @param string $text the text to encode the & in
         * @return string the encodes text
         */
        public static function escapeAmp($text)
        {
            return preg_replace('/&(?!amp;)/i', '&amp;', $text);
        }

        /**
         * encodes all or the characers in $text as HTML entities
         *
         * @access public
         * @static
         * @param string $text the text to encode characters in
         * @param string $chars the characters to encode. if empty, all characters will be encoded
         * @return string the encoded string
         */
        public static function entityEncode($text, $chars = '')
        {
            if ($chars === '')
            {
                $encoded = '';
                for ($i = 0; $i < mb_strlen($text); $i++)
                {
                    $encoded .= '&#' . ord(mb_substr($text, $i, 1)) . ';';
                }
                return $encoded;
            }
            else
            {
                for ($i = 0; $i < mb_strlen($chars); $i++)
                {
                    $text = str_replace($chars[$i], '&#' . ord($chars[$i]) . ';', $text);
                }
                return $text;
            }
        }

        /**
         * works as the PHP function explode, but uses the array value also as the index
         *
         * @access public
         * @static
         * @param string $delim the string to explode on
         * @param string $text the text to explode
         * @return array the array with the parts of the string
         */
        public static function explode2assoc($delim, $text)
        {
            $parts = explode($delim, $text);
            $assoc = array();
            foreach ($parts as $part)
            {
                $assoc[$part] = $part;
            }
            return $assoc;
        }

        /**
         * an mb_-version of chunk_split()
         *
         * @access public
         * @static
         * @param string $string the string to chunk
         * @param int $splitIndex the maximum length
         * @param string $delim the string to put in
         * @param string $encoding the character encoding
         * @return string the chunked string
         */
        public static function chunk_split($string, $splitIndex, $delim = ' ', $encoding = CI_CHARSET)
        {
            $chunks = array();
            for ($i = 0; $i < mb_strlen($string, $encoding); $i += $splitIndex)
            {
                $chunks[] = mb_substr($string, $i, $splitIndex, $encoding);
            }
            return implode($delim, $chunks);
        }

        /**
         * fills $number with leading zeros until $length is reached
         *
         * @access public
         * @static
         * @param string $number the number to fill up
         * @param int $length the length to fill up to
         * @return string the zerofilled number as a string
         */
        public static function zerofill($number, $length = null)
        {
            if (is_null($length))
            {
                $length = mb_strlen(PHP_INT_MAX);
            }
            $cLen = mb_strlen($number);
            $dif = $length - $cLen;
            $zeros = '';
            for ($i = 0; $i < $dif; $i++)
            {
                $zeros .= '0';
            }
            return $zeros . $number;
        }

        /**
         * parses all BBCodes, smiles, newlines and HTML special chars in $text
         *
         * @access public
         * @static
         * @global Database $db
         * @param string $text the text to parse
         * @param bool $chunk true to chunk long words
         * @param bool $bbcode true to parse BBCodes
         * @param bool $smiles true to parse smiles
         * @param array $disallowedBBCodes an array of disallowed BBCode-tags
         * @param bool $html true to allow HTML
         * @return string the parsed string
         */
        public static function parse($text, $chunk = true, $bbcode = true, $smiles = true, $disallowedBBCodes = array(), $html = false)
        {
            global $db;

            $result = $db->GetData('SELECT `name`,`parsetype`,`starttag`,`endtag`,`params`,`contenttype`,`allowedin`,`notallowedin` FROM `PREFIX_bbcode`');

            $parser = new StringParser_BBCode();
            $parser->setGlobalCaseSensitive(false);
            $contenttypes = array('block', 'inline', 'listitem');
            if ($chunk && !$html)
            {
                $parser->addParser($contenttypes, array('Text', 'simpleChunk'));
            }
            if (!$html)
            {
                $parser->addParser($contenttypes, 'htmlspecialchars');
            }
            if ($smiles)
            {
                $parser->addParser($contenttypes, array('Text', 'smilies'));
            }
            if (!$html)
            {
                $parser->addParser($contenttypes, array('Text', 'nl2br'));
            }
            if ($bbcode)
            {
                if (!in_array('list', $disallowedBBCodes))
                {
                    $parser->addCode('list', 'callback_replace', 'bbcode_list', array(), 'list', array('block', 'listitem'), array('inline'));
                    $parser->addCode('*', 'simple_replace', null, array('start_tag' => '<li>', 'end_tag' => '</li>'), 'listitem', array('list'), array());
                    $parser->setCodeFlag('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
                    $parser->setCodeFlag('*', 'paragraphs', true);
                    $parser->setCodeFlag('list', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
                    $parser->setCodeFlag('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
                    $parser->setCodeFlag('list', 'closetag.before.newline', BBCODE_NEWLINE_DROP);
                }

                foreach ($result as $code)
                {
                    if (in_array($code->name, $disallowedBBCodes))
                    {
                        continue;
                    }
                    $params = array(
                        'start_tag' => $code->starttag,
                        'end_tag' => $code->endtag
                    );
                    $tmp = explode('&', $code->params);
                    foreach ($tmp as $pair)
                    {
                        $parts = explode('=', $pair);
                        if (count($parts) < 2)
                        {
                            continue;
                        }
                        $values = explode(',', $parts[1]);
                        if (count($values) == 1)
                        {
                            $values = urldecode($values[0]);
                        }
                        else
                        {
                            $values = array_map('urldecode', $values);
                        }
                        $params[urldecode($parts[0])] = $values;
                    }
                    unset($tmp);
                    $parser->addCode(
                        $code->name,
                        $code->parsetype,
                        (preg_match('/(simple_replace|simple_replace_simgle)/i', $code->parsetype) ? null : 'bbcode_' . $code->name),
                        $params,
                        $code->contenttype,
                        explode('|', $code->allowedin),
                        explode('|', $code->notallowedin)
                    );
                }
            }

            return $parser->parse($text);
        }

        /**
         * checks whether the given string is a valid email address
         *
         * @access public
         * @static
         * @param string $string the string to validate
         * @return bool true if it is a valid email address
         */
        public static function is_email($string)
        {
            return (bool) preg_match("/[(\w\d\-\.]{3,}@([a-z\d-]{2,}\.)+[a-z\d]{2,4}/Us", mb_strtolower($string));
        }

        /**
         * checks whether the two strings are equal
         *
         * @param string $haystack the first string
         * @param string $needle the second string
         * @param bool $strict case-sensitiv or not (default: false)
         * @return bool true if the haystack and needle are equal
         */
        public static function equal($haystack, $needle, $strict = false)
        {
            $delim = '/';
            $regex = $delim . preg_quote($needle, $delim) . $delim;
            if ($strict === true)
            {
                $regex .= 'i';
            }
            return preg_match($regex, $haystack);
        }

        /**
         * url encodes all or the given chars in the string
         *
         * @param string $string the string
         * @param string $chars the chars to encode
         * @return string the encoded string
         */
        public static function urlEncode($string, $chars = '')
        {
            if ($chars === '')
            {
                $encoded = '';
                for ($i = 0; $i < mb_strlen($string); $i++)
                {
                    $encoded .= '%' . mb_strtoupper(dechex(ord(mb_substr($string, $i, 1))));
                }
                return $encoded;
            }
            else
            {
                for ($i = 0; $i < mb_strlen($chars); $i++)
                {
                    $char = mb_substr($chars, $i, 1);
                    $string = str_replace($char, '%' . mb_strtoupper(dechex(ord($chars))), $string);
                }
                return $string;
            }
        }

        /**
         * checks whether the given string is numeric
         *
         * @param string $numStr the string to check
         * @return bool true if it is numeric
         */
        public static function is_numeric($numStr)
        {
            return (bool) preg_match('/^[0-9]+$/s', $numStr);
        }

        /**
         * builds a random string of the ASCII charset (32-126)
         *
         * @param int $length the length of the random string
         * @return string the random string
         */
        public static function rand($length)
        {
            $string = '';
            for ($i = 0; $i < $length; $i++)
            {
                $string .= chr(mt_rand(32, 126));
            }
            return $string;
        }

        /**
         * strips bbcodes out of a string
         *
         * @global Database $db
         * @param string $text the string
         * @param bool $strict if true codes with strippable 1 are stripped too
         * @return string the stripped string
         */
        public static function strip_bbcodes($text, $strict = false)
        {
            global $db;
            
            $result = $db->GetData('SELECT `name`,`parsetype`,`params`,`contenttype`,`allowedin`,`notallowedin`,`strippable` FROM `PREFIX_bbcode`');

            $parser = new StringParser_BBCode();
            $parser->setGlobalCaseSensitive(false);
            
            $parser->addCode('list', 'callback_replace', 'bbcode_list', array(), 'list', array('block', 'listitem'), array('inline'));
            $parser->addCode('*', 'simple_replace', null, array('start_tag' => '<li>', 'end_tag' => '</li>'), 'listitem', array('list'), array());
            $parser->setCodeFlag('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
            $parser->setCodeFlag('*', 'paragraphs', true);
            $parser->setCodeFlag('list', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
            $parser->setCodeFlag('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
            $parser->setCodeFlag('list', 'closetag.before.newline', BBCODE_NEWLINE_DROP);

            foreach ($result as $code)
            {
                if ($code->strippable === '0')
                {
                    continue;
                }
                elseif ($code->strippable === '1' && !$strict)
                {
                    continue;
                }
                $params = array(
                    'start_tag' => '',
                    'end_tag' => ''
                );
                $tmp = explode('&', $code->params);
                foreach ($tmp as $pair)
                {
                    $parts = explode('=', $pair);
                    if (count($parts) < 2)
                    {
                        continue;
                    }
                    $values = explode(',', $parts[1]);
                    if (count($values) == 1)
                    {
                        $values = urldecode($values[0]);
                    }
                    else
                    {
                        $values = array_map('urldecode', $values);
                    }
                    $params[urldecode($parts[0])] = $values;
                }
                unset($tmp);
                $parser->addCode(
                    $code->name,
                    $code->parsetype,
                    (preg_match('/(simple_replace|simple_replace_simgle)/i', $code->parsetype) ? null : 'bbcode_strip'),
                    $params,
                    $code->contenttype,
                    explode('|', $code->allowedin),
                    explode('|', $code->notallowedin)
                );
            }

            return $parser->parse($text);
        }
    }
?>
