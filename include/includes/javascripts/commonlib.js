function showHideElem(node)
{
    if (node.style.display == 'none')
    {
        if (!node.lastState)
        {
            node.lastState = 'block';
        }
        node.style.display = node.lastState;
    }
    else
    {
        node.lastState = node.style.display;
        node.style.display = 'none';
    }
}
function bindEvent(elem, event, handler)
{
    if (elem.addEventListener)
    {
        elem.addEventListener(event, handler, false);
    }
    else if (elem.attachEvent)
    {
        elem.attachEvent('on' + event, handler)
    }
}
function unbindEvent(elem, event, handler)
{
    if (elem.addEventListener)
    {
        elem.removeEventListener(event, handler, false);
    }
    else if (elem.attachEvent)
    {
        elem.detachEvent('on' + event, handler)
    }
}
function getAbsolutePosition(node)
{
    if (node.offsetParent)
    {
        var top = 0;
        var left = 0;
        do
        {
            top += node.offsetTop;
            left += node.offsetLeft;
        }
        while ((node = node.offsetParent));
        return [top, left];
    }
    else
    {
        return [0, 0];
    }
}
function highlightElem(node)
{
    var div = document.createElement('div');
    var position = this.getAbsolutePosition(node);
    div.style.position = 'absolute';
    div.style.top = position[0] + 'px';
    div.style.left = position[1] + 'px';
    div.style.height = node.offsetHeight + 'px';
    div.style.width = node.offsetWidth + 'px';
    div.style.backgroundImage = "url('http://quick-wango.dyndns.org/images/redtrans.png')"
    div.setAttribute('onclick', 'this.parentNode.removeChild(this);');
    document.body.appendChild(div);
    return div;
}
function removeNode(node)
{
    node.parentNode.removeChild(node);
}
function trim(string)
{
    return string.replace(/^\s+/, '').replace (/\s+$/, '');
}
