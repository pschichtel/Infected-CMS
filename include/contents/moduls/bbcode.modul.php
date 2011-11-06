<?php
    realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME']) and die('<strong>Access denied!</strong>');

$design = new Design($cfg->cms_title . ' :: BBCode-Tests', 'BBCode-Tests');
$design->printBegin();

$txt = <<<text
[url]http://www.google.de/search?q=einganzlangerlinkderumgebrochenwerdenmussesabernichtwird[/url]
das ist [b]fetter Text[/b] und und das ist [u][lt]unterstrichener[/lt] [i]kursiver Text[/i][/u] hier ein [url=http://www.google.de][b]LI[/b]NK mit HTML Sonderzeichen & < > <br /> " ' [/url]

Und wieder ein Update! :D

Dieses Mal kommt endlich die Kommentarfunktion bei den News dazu.
Aber auch intern und im Admincenter ist einiges passiert, was ihr im ToDo-Viewer nachlesen könnt.
Insgesamt brauch das System jetzt deutlich länger im die Seiten aufzubauen (wie lange hängt hauptsächlich von dem Modul ab), was daran liegt das intern einiges anders und aufwendiger verarbeitet wird als vorher. Trotzdem werde ich versuchen das ganze wieder zu beschleunigen oder zu mindest diese Geschwindigkeit zu halten.

Über eure Meinung in den Kommentaren würde ich mich sehr freuen, also immer zu! :)

Ihr kommt zu den Kommentaren indem ihr auf den Titel der News oder bei gekürzten News auf den "Weiterlesen"-Link klickt.

Die Kommentare dieser News findet ihr [url=news.view-19.html]HIER[/url]

MfG Quick_Wango

[font=comic sans ms]hier ist eine andere schrift[/font]

[sub]das hier ist tiefer[/sub] während [sup]das hier höher ist[/sup]

[size=30]das ist sehr groß[/size] und [size=5]das sehr klein[/size]  [size=31]das ist ein test (zu großer wert)[/size]

[center]Dieser text ist zentriert[/center]

[right]Dieser wiederum rechts[/right]

[list]
[*] Das hier ist ein Listenpunkt
[*][list=upper-latin]
[*] Das hier ist ein Listenpunkt in einem Listenpunkt :D
[/list]
[*] [center]Der BBCode sollte nicht geparset werden^^[/center]
[/list]

[justify][indent]Hier ist Blocksatz und eingerückt edrgaega ergaerga ergae rgaerg aergaergae rg aergaerga erg aergaerga ergaerga ergaergaerg aergae rgaerga fdgaer ga rf gfgergaef gaergaerg aer gaergerg erg e rg erg ergaergaerg aerg aerga erg ergergerg aergergerg erg er gergerger g erg ergerg erg er ge rgergerg ergergergerg ergerg ergergerg r eg ergergergerg er g er[/indent][/justify]

[code]

hier ist
mehrzeiliger
code
    und
    noch
ein paar
mehr
zeilen
      als
   es
        grad
  eben
  der
fall
war :D
    und
    gleich
noch
mehr
und
    mehr
    und
    mehr
    und
 mehr
 wtf?
 noch
 immer
 zu
 weniger
 ?
 ?[/code]

[code=php]<?php
    phpinfo();
?>[/code]

[color=blue]Hier[/color] [color=#f00]ist[/color] [color=rgb(255,255,0)]Farbe[/color] [color=#ff00ff]![/color]

[search=google]test[/search]

[search=lmgtfy]test[/search]

[quote]blubbber
di blub
[/quote]

[quote=author]blubbber
di blub blub
[/quote]

[line]

[video]http://www.youtube.com/watch?v=96dWOEa4Djs[/video]

[spoiler]
[video=youtube]96dWOEa4Djs[/video]
[/spoiler]

[video]http://vimeo.com/11867754[/video]
[video=vimeo]11867754[/video]

[video]http://www.metacafe.com/watch/3220869/lol/[/video]
[video=metacafe]3220869[/video]

[video]http://www.myvideo.de/watch/4780973/zzirGrizz_cod4_legende[/video]
[video=myvideo]4780973[/video]

[video]http://video.google.com/videoplay?docid=822009981904229965#[/video]
[video=google]822009981904229965[/video]

[url=http://www.code-infection.de][img]http://www3.pic-upload.de/10.05.10/cwpe7evgeaxb.jpg[/img][/url]

[noparse] [center] [b] hier wird nichts geparset [/b] [/center] [/noparse]

laaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaanges Wort

[b] noch [i] ein [color=red]laaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaanges[/color] Wort [/i] in[/b] BBCode :)

[url]http://www.google.de/search?q=ein+ganz+langer+link+der+umgebrochen+werden+muss[/url]

[url]http://www.google.de/search?q=einganzlangerlinkderumgebrochenwerdenmussesabernichtwird[/url]

[url=http://www.google.de/search?q=einganzlangerlinkderumgebrochenwerdenmussesabernichtwird]LINK[/url]

[url=http://www.google.de/search?q=ein+ganz+langer+link+der+umgebrochen+werden+muss,+es+aber+nicht+wird]mit einerm sehr laaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaagen Wort :D[/url]

[email]quick_wango@web.de[/email]

[copyright] [registered] [tm] [bull]
[pre]
das hier ist
  ein pre-block
[/pre]
text;


echo nl2br(htmlspecialchars($txt));

echo '<br /><br /><hr color="red" /><br /><br />';


echo Text::parse($txt);

$design->printEnd();

?>