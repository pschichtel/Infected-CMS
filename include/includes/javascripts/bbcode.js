function simple_insert(input,aTag,eTag)
{
    input.focus();
    var insText;
    if(typeof document.selection != 'undefined')
    {
        var range = document.selection.createRange();
        insText = range.text;
        range.text = aTag + insText + eTag;
        range = document.selection.createRange();
        if (insText.length == 0)
        {
            range.move('character', -eTag.length);
        }
        else
        {
            range.moveStart('character', aTag.length + insText.length + eTag.length);
        }
        range.select();
    }
    else if(typeof input.selectionStart != 'undefined')
    {
        var start = input.selectionStart;
        var end = input.selectionEnd;
        insText = input.value.substring(start, end);
        input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
        var pos;
        if (insText.length == 0)
        {
            pos = start + aTag.length;
        }
        else
        {
            pos = start + aTag.length + insText.length + eTag.length;
        }
        input.selectionStart = pos;
        input.selectionEnd = pos;
    }
    else
    {
        input.value += aTag + eTag;
    }
    return false;
}
function insertBBCode(inputID, name, attrib)
{
    var input = document.getElementById(inputID);
    var startTag = '[' + name + (attrib != null ? '=' + attrib : '') + ']';
    var endTag = '[/' + name + ']';
    simple_insert(input, startTag, endTag);
    return false;
}
function insertSingleBBCode(inputID, name)
{
    var input = document.getElementById(inputID);
    var endTag = '[' + name + ']';
    simple_insert(input, '', endTag);
    return false;
}
function insertURLBBCode(inputID)
{
    var input = document.getElementById(inputID);
    var uri = window.prompt('{LANG[url_enter_link]}', '');
    if (!uri)
    {
        return false;
    }
    var title = window.prompt('{LANG[url_enter_title]}', '');
    var endTag = '';
    if (!title)
    {
        endTag = '[url]' + uri + '[/url]';
    }
    else
    {
        endTag = '[url=' + uri + ']' + title + '[/url]';
    }
    simple_insert(input, '', endTag);
    return false;
}
function insertQuoteBBCode(inputID)
{
    var input = document.getElementById(inputID);
    var name = window.prompt('{LANG[quote_enter_name]}', '');
    if (!name)
    {
        simple_insert(input, '[quote]', '[/quote]');
    }
    else
    {
        simple_insert(input, '[quote=' + name + ']', '[/quote]');
    }
    return false;
}
function insertImgBBCode(inputID)
{
    var input = document.getElementById(inputID);
    var uri = window.prompt('{LANG[img_enter_addr]}', '');
    var endTag = '';
    if (uri)
    {
        endTag = '[img]' + uri + '[/img]';
    }
    simple_insert(input, '', endTag);
    return false;
}
function insertSizeBBCode(inputID)
{
    var input = document.getElementById(inputID);
    var size = window.prompt('{LANG[size_enter_size]}', '');
    if (size && size > 0 && size < 31)
    {
        simple_insert(input, '[size=' + size + ']', '[/size]');
    }
    return false;
}
function insertColorBBCode(inputID)
{
    var input = document.getElementById(inputID);
    var color = window.prompt('{LANG[color_enter_color]}', '');
    if (color)
    {
        color = trim(color);
        if (color.match(/^[a-z]+$/i)
         || color.match(/^#([\da-f]{3}|[\da-f]{6})$/i)
         || color.match(/^rgb\(\d{1,3},\d{1,3},\d{1,3}\)$/i))
        {
            simple_insert(input, '[color=' + color + ']', '[/color]');
        }
    }
    return false;
}
function insertCodeBBCode(inputID)
{
    var input = document.getElementById(inputID);
    var type = window.prompt('{LANG[code_enter_lang]}', '');

    var start = '[code]';
    if (type)
    {
        start = '[code=' + type + ']';
    }
    simple_insert(input, start, '[/code]');

    return false;
}
function insertVideoBBCode(inputID)
{
    var input = document.getElementById(inputID);
    var mode = window.confirm('{LANG[video_decide_vlink]}');
    if (mode)
    {
        simple_insert(input, '[video]', '[/video]');
    }
    else
    {
        var provider = window.prompt('{LANG[video_enter_prov]}', 'youtube');
        if (provider)
        {
            provider = provider.toLowerCase();
            var vid = window.prompt('{LANG[video_enter_id]}', '');
            var endTag = '';
            if (vid)
            {
                endTag = '[video=' + provider + ']' + vid + '[/video]';
            }
            simple_insert(input, '', endTag);
        }
    }

    return false;
}
function insertListBBCode(inputID)
{
    var input = document.getElementById(inputID);
    var listItems = '';
    var tmp = '';
    while ((tmp = window.prompt('{LANG[list_enter_value]}', '')))
    {
        listItems += '[*]' + tmp + '\n';
    }
    var endTag = '';
    if (listItems)
    {
        endTag = '[list]\n' + listItems + '[/list]';
    }
    simple_insert(input, '', endTag);

    return false;
}
function insertFontBBCode(inputID)
{
    var input = document.getElementById(inputID);
    var font = window.prompt('{LANG[font_enter_font]}', '');
    if (font && font.match(/^[a-z\d ]+$/i))
    {
        simple_insert(input, '[font=' + font + ']', '[/font]');
    }
}
function openSmileWindow(inputID)
{
    var input = document.getElementById(inputID);
    var smileWindow = window.open('smiles.target-' + inputID + '.hhtml', '', 'dependent=yes,height=200,left=' + (getAbsolutePosition(input)[1] + input.offsetWidth) + ',location=no,menubar=no,resizable=yes,status=no,toolbar=no,top=' + (getAbsolutePosition(input)[0]) + ',width=200');
    smileWindow.focus();
    return false;
}
