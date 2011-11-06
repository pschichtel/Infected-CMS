<?php
    defined('MODE') or die('<strong>Access denied!</strong>');

    function bbcode_url($action, $attributes, $content, $params, &$node_object)
    {
        if ($action == 'validate')
        {
            if (!isset($attributes['default']))
            {
                $node_object->raw = $content;
                return preg_match('/^[a-z\d]+?:\/\//si', $content);
            }
            return preg_match('/^[a-z]+?:\/\//si', $attributes['default']);
        }
        else
        {
            if (isset($attributes['default']))
            {
                $name = $content;
                $url = htmlspecialchars($attributes['default']);
            }
            else
            {
                $name = $content;
                $url = htmlspecialchars($node_object->raw);
            }
            return '<a class="external" href="' . $url . '" title="' . $url . '">' . $name . '</a>';
        }
    }

    function bbcode_quote($action, $attributes, $content, $params, &$node_object)
    {
        if ($action == 'validate')
        {
            return true;
        }
        else
        {
            $lang = new Lang('other');
            $tpl = new Template('bbcode_quote', $lang, 4);
            $params['CONTENT'] = $content;
            $part = 0;
            if (isset($attributes['default']))
            {
                $params['AUTHOR'] = $attributes['default'];
                $part = 1;
            }
            $tpl->setParams($params);
            return $tpl->getPart($part, true);
        }
    }

    function bbcode_img($action, $attributes, $content, $params, &$node_object)
    {
        if ($action == 'validate')
        {
            return preg_match('/^[a-z]+?:\/\//si', $content);
        }
        else
        {
            return '<img src="' . htmlspecialchars($content) . '" class="pics" alt="" />';
        }
    }

    function bbcode_search($action, $attributes, $content, $params, &$node_object)
    {
        if ($action == 'validate')
        {
            return (isset($attributes['default']) || isset($attributes['provider']));
        }
        else
        {
            $provider;
            if (isset($attributes['default']))
            {
                $provider = &$attributes['default'];
            }
            elseif (isset($attributes['provider']))
            {
                $provider = &$attributes['provider'];
            }
            else
            {
                $provider = 'google';
            }
            $lang = new Lang('other');
            $query = urlencode($content);
            $content = htmlspecialchars($content);
            switch ($provider)
            {
                case 'google':
                    return '<a class="external" href="http://www.google.com/search?q=' . $query . '">' . $lang->search . ' @ Google: ' . $content . '</a>';
                case 'yahoo':
                    return '<a class="external" href="http://search.yahoo.com/search?p=' . $query . '">' . $lang->search . ' @ Yahoo: ' . $content . '</a>';
                case 'bing':
                    return '<a class="external" href="http://www.bing.com/search?q=' . $query . '">' . $lang->search . ' @ Bing: ' . $content . '</a>';
                case 'lmgtfy':
                    return '<a class="external" href="http://lmgtfy.com/?q=' . $query . '">' . $lang->search . ' @ LMGTFY: ' . $content . '</a>';
                default:
                    return '<a class="external" href="http://www.google.com/search?q=' . $query . '">' . $lang->search . ' @ Google: ' . $content . '</a>';
            }
        }
    }

    function bbcode_video($action, $attributes, $content, $params, &$node_object)
    {
        $vid = false;
        if (isset($attributes['default']) && preg_match('/^(google|youtube|metacafe|myvideo|vimeo)$/i', $attributes['default']))
        {
            $vid = true;
        }
        if ($action == 'validate')
        {
            if ($vid)
            {
                return (preg_match('/^[\w\d-]+$/i', $content) && preg_match('/^(google|youtube|metacafe|myvideo|vimeo)$/i', $attributes['default']));
            }
            else
            {
                $URL = parse_url($content);
                if (!is_bool($URL) && isset($URL['host']) && (isset($URL['query']) || isset($URL['path'])))
                {
                    return true;
                }
            }
        }
        else
        {
            if ($vid)
            {
                switch ($attributes['default'])
                {
                    case 'google':
                        return '<embed id="VideoPlayback" src="http://video.google.com/googleplayer.swf?docid=' . $content . '&amp;hl=de&amp;fs=true" style="width:400px;height:326px" allowfullscreen="true" allowscriptaccess="always" type="application/x-shockwave-flash"></embed>';
                    case 'youtube':
                        return '<object width="560" height="340"><param name="movie" value="http://www.youtube.com/v/' . $content . '&amp;fs=1&amp;"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/' . $content . '&amp;fs=1&amp;" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="560" height="340"></embed></object>';
                    case 'myvideo':
                        return '<object style="width:470px;height:285px;" width="470" height="285"><param name="movie" value="http://www.myvideo.de/movie/' . $content . '"></param><param name="AllowFullscreen" value="true"></param><param name="AllowScriptAccess" value="always"></param><embed src="http://www.myvideo.de/movie/' . $content . '" width="470" height="285" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"></embed></object>';
                    case 'metacafe':
                        return '<embed src="http://www.metacafe.com/fplayer/' . $content . '/video.swf" width="400" height="345" wmode="transparent" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always"></embed>';
                    case 'vimeo':
                        return '<object width="400" height="225"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=' . $content . '&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=ff9933&amp;fullscreen=1" /><embed src="http://vimeo.com/moogaloop.swf?clip_id=' . $content . '&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=ff9933&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="400" height="225"></embed></object>';
                    default:
                        $lang = new Lang('other');
                        return '<div>' . $lang->invalid_video . '</div>';
                }
            }
            else
            {
                $URL = parse_url($content);
                $host = &$URL['host'];
                if (isset($URL['query']))
                {
                    $QS;
                    mb_parse_str($URL['query'], $QS);
                    if (preg_match('/youtube/i', $host) && isset($QS['v']))
                    {
                        return '<object width="560" height="340"><param name="movie" value="http://www.youtube.com/v/' . $QS['v'] . '&amp;fs=1&amp;"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/' . $QS['v'] . '&amp;fs=1&amp;" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="560" height="340"></embed></object>';
                    }
                    elseif (preg_match('/video\.google/i', $host) && isset($QS['docid']))
                    {
                        return '<embed id="VideoPlayback" src="http://video.google.com/googleplayer.swf?docid=' . $QS['docid'] . '&amp;hl=de&amp;fs=true" style="width:400px;height:326px" allowFullScreen="true" allowScriptAccess="always" type="application/x-shockwave-flash"></embed>';
                    }
                }
                else
                {
                    $paths = explode('/', $URL['path']);
                    if (preg_match('/myvideo/i', $host))
                    {
                        return '<object style="width:470px;height:285px;" width="470" height="285"><param name="movie" value="http://www.myvideo.de/movie/' . $paths[2] . '"></param><param name="AllowFullscreen" value="true"></param><param name="AllowScriptAccess" value="always"></param><embed src="http://www.myvideo.de/movie/' . $paths[2] . '" width="470" height="285" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"></embed></object>';
                    }
                    elseif (preg_match('/metacafe/i', $host))
                    {
                        return '<embed src="http://www.metacafe.com/fplayer/' . $paths[2] . '/video.swf" width="400" height="345" wmode="transparent" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" allowFullScreen="true" allowScriptAccess="always"></embed>';
                    }
                    elseif (preg_match('/vimeo/i', $host))
                    {
                        return '<object width="400" height="225"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=' . $paths[1] . '&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=ff9933&amp;fullscreen=1" /><embed src="http://vimeo.com/moogaloop.swf?clip_id=' . $paths[1] . '&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=ff9933&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="400" height="225"></embed></object>';
                    }
                }
                $lang = new Lang('other');
                return '<div>' . $lang->invalid_video . '</div>';
            }
        }
    }

    function bbcode_spoiler($action, $attributes, $content, $params, &$node_object)
    {
        if ($action == 'validate')
        {
            return true;
        }
        else
        {
            $lang = new Lang('other');
            $tpl = new Template('bbcode_spoiler', $lang, 4);
            $tpl->setParams(array('CONTENT' => $content));
            return $tpl->getPart(0, true);
        }
    }

    function bbcode_color($action, $attributes, $content, $params, &$node_object)
    {
        if ($action == 'validate')
        {
            if (!isset($attributes['default']))
            {
                return false;
            }
            if (preg_match('/^[a-z]+$/i', $attributes['default']))
            {
                return true;
            }
            if (preg_match('/^#([\da-f]{3}|[\da-f]{6})$/i', $attributes['default']))
            {
                return true;
            }
            if (preg_match('/^rgb\(\d{1,3},\d{1,3},\d{1,3}\)$/i', $attributes['default']))
            {
                return true;
            }
            return false;
        }
        else
        {
            return '<span style="color:' . $attributes['default'] . '">' . $content . '</span>';
        }
    }

    function bbcode_size($action, $attributes, $content, $params, &$node_object)
    {
        if ($action == 'validate')
        {
            return (preg_match('/^\d\d?$/', $attributes['default']) && (31 > intval($attributes['default'])));
        }
        else
        {
            return '<span style="font-size:' . $attributes['default'] . 'pt;">' . $content . '</span>';
        }
    }

    function bbcode_code($action, $attributes, $content, $params, &$node_object)
    {
        if ($action == 'validate')
        {
            $type = &$attributes['default'];
            if (isset($attributes['default']))
            {
                if (preg_match('/php/i', $type))
                {
                    return true;
                }
                return false;
            }
            return true;
        }
        else
        {
            $content = preg_replace("/\015\012|\015|\012/", "\n", $content);
            $content = preg_replace("/^\s+?\n/si", '', $content, 1);
            $content = preg_replace("/\n\s+?$/si", '', $content, 1);

            if (isset($attributes['default']))
            {
                $type = &$attributes['default'];
                if (preg_match('/php/i', $type))
                {
                    $content = highlight_string($content, true);
                    $content = preg_replace("/^.+?\n/si", '', $content, 1);
                    $content = preg_replace("/\n.+?\n.+?$/si", '', $content, 1);
                    $content = str_replace('<br />', "\n", $content);
                }
            }
            else
            {
                $content = htmlspecialchars($content);
            }

            $content = str_replace("\n", "\n<span class=\"line\"><!LINE!>.</span>", "\n" . $content);

            $i = 1;
            while (preg_match('/<!LINE!>/', $content))
            {
                $content = preg_replace('/<!LINE!>/', $i, $content, 1);
                $i++;
            }
            $lang = new Lang('other');
            return '<div class="code"><h4>' . $lang->code_view . ':</h4><pre>' . $content . '</pre></div>';
        }
    }

    function bbcode_email($action, $attributes, $content, $params, &$node_object)
    {
        if ($action == 'validate')
        {
            return Text::is_email($content);
        }
        else
        {
           list($name, $host) = explode('@', $content);
           $name = htmlspecialchars(Text::simpleChunk(str_replace(array('_', '.', '-'), ' ', $name)));
           $host = Text::simpleChunk(str_replace('.', ' ', $host));
           $text = '[' . $name . ' et ' . $host . ']';
           $email = Text::entityEncode('mailto:' . $content);
           return '<a href="' . $email . '" class="email_address">' . $text . '</a>';
        }
    }
    
    function bbcode_list($action, $attributes, $content, $params, &$node_object)
    {
        if ($action == 'validate')
        {
            if (isset($attributes['default']))
            {
                return preg_match('/[\w-]+/i', $attributes['default']);
            }
            return true;
        }
        else
        {
            if (isset($attributes['default']))
            {
                return '<ul style="list-style-type:' . $attributes['default'] . ';">' . $content . '</ul>';
            }
            else
            {
                return '<ul>' . $content . '</ul>';
            }
        }
    }

    function bbcode_font($action, $attributes, $content, $params, &$node_object)
    {
        if ($action == 'validate')
        {
            return preg_match('/^[a-z\d ]+$/i', $attributes['default']);
        }
        else
        {
            return '<span style="font-family:\'' . $attributes['default'] . '\';">' . $content . '</span>';
        }
    }

    function bbcode_strip($action, $attributes, $content, $params, &$node_object)
    {
        if ($action == 'validate')
        {
            return true;
        }
        else
        {
            return $content;
        }
    }

?>
