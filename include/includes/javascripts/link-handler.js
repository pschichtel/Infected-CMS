function externalA_clickHandler(e)
{
    var sender = (e.target) ? e.target : e.srcElement;
    var href = sender.href;
    if (sender.nodeName != 'A')
    {
        href = sender.parentNode.href;
    }
    window.open(href, '_blank');
    return false;
}

function externalA_getExternalA(classname)
{
    var externalA = new Array();
    for (var i = 0, j = 0; i < document.links.length; i++)
    {
        if (document.links[i].getAttribute('class') == classname)
        {
            externalA[j] = document.links[i];
            j++;
        }
    }
    return externalA;
}

function externalA_bindEvents(classname)
{
    var links = externalA_getExternalA(classname);
    for (var i = 0; i < links.length; i++)
    {
        var img = document.createElement('img');
        img.setAttribute('src', 'include/images/extern.png');
        img.setAttribute('alt', '');
        img.setAttribute('border', '0');
        img.setAttribute('title', 'extern...')
        img.setAttribute('class', 'externalIndicater')
        links[i].onclick = externalA_clickHandler;
        links[i].innerHTML += ' ';
        links[i].appendChild(img);
    }
}