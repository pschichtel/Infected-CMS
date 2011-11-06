-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 04. September 2010 um 22:20
-- Server Version: 5.1.41
-- PHP-Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `test`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_admingroups`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_admingroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'A_I Col',
  `groupname` varchar(30) NOT NULL COMMENT 'name of the group',
  `rights` text NOT NULL COMMENT 'rights of the group',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Admingroups' AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `{PREFIX}_admingroups`
--

INSERT INTO `{PREFIX}_admingroups` (`id`, `groupname`, `rights`) VALUES
(1, 'mainadmins', '|ALL|'),
(2, 'lowadmins', '||');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_adminnavi`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_adminnavi` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `menuid` smallint(5) unsigned NOT NULL,
  `type` smallint(1) NOT NULL,
  `name` varchar(40) NOT NULL,
  `address` varchar(300) NOT NULL,
  `extern` smallint(1) NOT NULL DEFAULT '0',
  `level` smallint(2) NOT NULL DEFAULT '0',
  `position` smallint(6) unsigned NOT NULL,
  `visible` smallint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Admin navigation' AUTO_INCREMENT=20 ;

--
-- Daten für Tabelle `{PREFIX}_adminnavi`
--

INSERT INTO `{PREFIX}_adminnavi` (`id`, `menuid`, `type`, `name`, `address`, `extern`, `level`, `position`, `visible`) VALUES
(1, 1, 1, 'System', 'menu', 0, 0, 1, 1),
(2, 1, 2, 'Übersicht', 'overview', 0, 0, 2, 1),
(3, 1, 2, 'Konfiguration', 'config', 0, 0, 3, 1),
(4, 1, 2, 'Navigation', 'navi', 0, 0, 4, 1),
(5, 1, 2, 'Administratoren', 'admins', 0, 0, 5, 1),
(6, 1, 2, 'Admin-Gruppen', 'admingroups', 0, 0, 6, 1),
(7, 1, 1, 'Module', 'menu', 0, 0, 7, 1),
(8, 1, 2, 'News', 'news', 0, 0, 8, 1),
(9, 1, 2, 'Gästebuch', 'gbook', 0, 0, 9, 1),
(10, 1, 2, 'Anfahrt', 'drive', 0, 0, 10, 1),
(11, 1, 2, 'Partner', 'partners', 0, 0, 11, 1),
(12, 1, 2, 'Kontakt', 'contact', 0, 0, 12, 1),
(13, 1, 2, 'Impressum', 'imprint', 0, 0, 13, 1),
(14, 1, 2, 'Eigene Inhalte', 'self', 0, 0, 14, 1),
(15, 1, 2, 'Statistik', 'stats', 0, 0, 15, 1),
(16, 1, 1, 'Benutzer', 'menu', 0, 0, 16, 1),
(17, 1, 2, 'Logout', 'logout', 0, 0, 17, 1),
(18, 6, 1, 'System', 'menu', 0, 0, 1, 1),
(19, 6, 5, 'Zurück zur Seite', './', 0, 0, 2, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_admins`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_admins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'A_I Col',
  `name` varchar(60) NOT NULL COMMENT 'name of the admin',
  `password` varchar(161) NOT NULL COMMENT 'password of the admin',
  `dynsalt` varchar(32) NOT NULL COMMENT 'dynamic salt of the password',
  `admingroup` varchar(30) NOT NULL COMMENT 'admingroup of the admin',
  `nextlogin` int(11) NOT NULL DEFAULT '0' COMMENT 'time for the next login try',
  `loginfails` int(11) NOT NULL DEFAULT '0' COMMENT 'login fail count',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Administrators' AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `{PREFIX}_admins`
--

INSERT INTO `{PREFIX}_admins` (`id`, `name`, `password`, `dynsalt`, `admingroup`, `nextlogin`, `loginfails`) VALUES
(1, '{ADMINNAME}', '{ADMINPWD}', '{PWD_DYNSALT}', '{ADMINGROUP}', 0, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_bbcode`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_bbcode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `parsetype` varchar(23) NOT NULL,
  `starttag` varchar(200) NOT NULL,
  `endtag` varchar(200) NOT NULL,
  `params` varchar(200) NOT NULL,
  `contenttype` varchar(6) NOT NULL,
  `allowedin` varchar(200) NOT NULL,
  `notallowedin` varchar(200) NOT NULL,
  `strippable` smallint(1) NOT NULL DEFAULT '2' COMMENT 'defines whether the code can be stripped out ',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='contains the bbcodes to register them in the parser' AUTO_INCREMENT=30 ;

--
-- Daten für Tabelle `{PREFIX}_bbcode`
--

INSERT INTO `{PREFIX}_bbcode` (`id`, `name`, `parsetype`, `starttag`, `endtag`, `params`, `contenttype`, `allowedin`, `notallowedin`, `strippable`) VALUES
(1, 'b', 'simple_replace', '<strong>', '</strong>', '', 'inline', 'block|inline|listitem', '', 1),
(2, 'url', 'usecontent?', '', '', 'usecontent_param=default', 'inline', 'block|inline|listitem', '', 2),
(3, 'right', 'simple_replace', '<div style="text-align:right;">', '</div>', '', 'block', 'block', 'inline', 2),
(4, 'center', 'simple_replace', '<div style="text-align:center;">', '</div>', '', 'block', 'block', 'inline', 2),
(5, 'img', 'usecontent', '', '', '', 'raw', 'block|inline|listitem', '', 2),
(6, 'color', 'callback_replace', '', '', '', 'inline', 'block|inline|listitem', '', 2),
(7, 'u', 'simple_replace', '<span style="text-decoration:underline;">', '</span>', '', 'inline', 'block|inline|listitem', '', 1),
(8, 'i', 'simple_replace', '<em>', '</em>', '', 'inline', 'block|inline|listitem', '', 1),
(9, 'lt', 'simple_replace', '<span style="text-decoration:line-through;">', '</span>', '', 'inline', 'block|inline|listitem', '', 2),
(10, 'sub', 'simple_replace', '<span style="vertical-align:sub;font-size:50%;">', '</span>', '', 'inline', 'block|inline|listitem', '', 2),
(11, 'sup', 'simple_replace', '<span style="vertical-align:super;font-size:50%;">', '</span>', '', 'inline', 'block|inline|listitem', '', 2),
(12, 'justify', 'simple_replace', '<div style="text-align:justify;">', '</div>', '', 'block', 'block', 'inline', 2),
(13, 'line', 'simple_replace_single', '<hr />', '', '', 'block', 'block', 'inline', 2),
(14, 'size', 'callback_replace', '<span style="font-size:$1pt;">', '</span>', '', 'inline', 'block|inline|listitem', '', 2),
(15, 'indent', 'simple_replace', '<div style="padding-left:15px;">', '</div>', '', 'block', 'block', 'inline', 2),
(16, 'video', 'usecontent', '', '', '', 'raw', 'block', 'inline', 2),
(17, 'search', 'usecontent', '', '', 'usecontent_param=default,provider', 'inline', 'block|inline|listitem', '', 2),
(18, 'spoiler', 'callback_replace', '', '', '', 'block', 'block', 'inline', 2),
(21, 'code', 'usecontent', '', '', '', 'raw', 'block', 'inline', 2),
(20, 'quote', 'callback_replace', '', '', '', 'block', 'block', 'inline', 2),
(22, 'noparse', 'simple_replace', '', '', '', 'raw', 'block|inline|listitem', '', 0),
(23, 'email', 'usecontent?', '', '', '', 'raw', 'block|inline|listitem', '', 0),
(29, 'pre', 'simple_replace', '<pre>', '</pre>', '', 'block', 'block', 'inline', 2),
(24, 'copyright', 'simple_replace_single', '©', '', '', 'inline', 'block|inline|listitem', '', 2),
(25, 'registered', 'simple_replace_single', '®', '', '', 'inline', 'block|inline|listitem', '', 0),
(26, 'bull', 'simple_replace_single', '•', '', '', 'inline', 'block|inline|listitem', '', 0),
(27, 'tm', 'simple_replace_single', '™', '', '', 'inline', 'block|inline|listitem', '', 0),
(28, 'font', 'callback_replace', '', '', '', 'inline', 'block|inline|listitem', '', 2);


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_comments`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_comments` (
  `guid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'A_I Col',
  `modul` varchar(20) NOT NULL COMMENT 'owner modul',
  `muid` int(10) unsigned NOT NULL COMMENT 'an id which identifes the comments of the modul',
  `datetime` datetime NOT NULL COMMENT 'the timestamp the comment was posted at',
  `author` varchar(50) NOT NULL COMMENT 'author of the comment',
  `rawtext` text NOT NULL COMMENT 'raw text of the comment',
  `parsedtext` text NOT NULL COMMENT 'parsed text of the comment',
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Comments' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_config`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'A_I Col',
  `index` varchar(25) NOT NULL DEFAULT '' COMMENT 'index to access the value',
  `modul` varchar(20) NOT NULL DEFAULT '' COMMENT 'the related modul',
  `mode` varchar(100) NOT NULL DEFAULT '0' COMMENT 'input mode',
  `value` varchar(500) NOT NULL DEFAULT '' COMMENT 'value of the config entry',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Configuration' AUTO_INCREMENT=31 ;

--
-- Daten für Tabelle `{PREFIX}_config`
--

INSERT INTO `{PREFIX}_config` (`id`, `index`, `modul`, `mode`, `value`) VALUES
(1, 'gbook_pps', 'gbook', 'input|text', '5'),
(2, 'gbook_lock', 'gbook', 'input|text', '3600'),
(3, 'cms_title', 'cms', 'input|text', 'Code Infection'),
(4, 'cms_std_modul', 'cms', 'select|callback:list_moduls($cfg->cms_std_modul)', 'news'),
(5, 'news_pps', 'news', 'input|text', '5'),
(8, 'txt_split_index', 'txt', 'input|text', '50'),
(9, 'partners_pps', 'partners', 'input|text', '10'),
(18, 'contact_lock', 'contact', 'input|text', '1800'),
(19, 'gbook_email_subject', 'gbook', 'input|text', 'Antwort auf ihren Gästebucheintrag'),
(22, 'cms_std_lang', 'cms', 'input|text', 'de'),
(23, 'cms_std_design', 'cms', 'select|callback:list_designs($cfg->cms_std_design)', 'default'),
(24, 'seo_uri_rewrite', 'seo', 'select|callback:CBack_config_YesNo($cfg->seo_uri_rewrite)', '0'),
(25, 'cms_captcha_length', 'cms', 'input|text', '6'),
(26, 'gbook_textlen', 'gbook', 'input|text', '300'),
(27, 'contact_textlen', 'contact', 'input|text', '1000'),
(28, 'news_cps', 'news', 'input|text', '20'),
(29, 'news_commentlength', 'news', 'input|text', '300'),
(30, 'news_shortenedlen', 'news', 'input|text', '300');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_contact`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_contact` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'A_I Col',
  `name` varchar(70) NOT NULL COMMENT 'shown name of the contact address',
  `email` varchar(100) NOT NULL COMMENT 'email adress',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Contact adresses' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_drive`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_drive` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'A_I Col',
  `key` varchar(200) NOT NULL COMMENT 'Google-Maps-API key',
  `lang` varchar(5) NOT NULL COMMENT 'the language which the map uses',
  `zoom` smallint(6) NOT NULL COMMENT 'the default zoom of the map',
  `posl` varchar(30) NOT NULL COMMENT 'center longitude',
  `posb` varchar(30) NOT NULL COMMENT 'center latitude',
  `type` varchar(30) NOT NULL COMMENT 'default map type',
  `markertext` text NOT NULL COMMENT 'text of the marker',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Drive-data' AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `{PREFIX}_drive`
--

INSERT INTO `{PREFIX}_drive` (`id`, `key`, `lang`, `zoom`, `posl`, `posb`, `type`, `markertext`) VALUES
(1, '', 'de', 18, '0', '0', 'G_HYBRID_MAP', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_gbook`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_gbook` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'A_I Col',
  `author` varchar(40) NOT NULL COMMENT 'author of the entry',
  `email` varchar(60) DEFAULT NULL COMMENT 'author email',
  `rawtext` text NOT NULL COMMENT 'raw text of the entry',
  `parsedtext` text NOT NULL COMMENT 'parsed text of the entry',
  `date` datetime NOT NULL COMMENT 'date the entry was posted at',
  `ip` varchar(41) NOT NULL COMMENT 'author IP',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Guestbook entries' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_imprint`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_imprint` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'A_I Col',
  `rawimprint` text NOT NULL COMMENT 'the unparsed imprint content',
  `parsedimprint` text NOT NULL COMMENT 'the parsed imprint content',
  `rawliability` text NOT NULL COMMENT 'the unparsed liability content',
  `parsedliability` text NOT NULL COMMENT 'the parsed liability content',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Imprint' AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `{PREFIX}_imprint`
--

INSERT INTO `{PREFIX}_imprint` (`id`, `rawimprint`, `parsedimprint`, `rawliability`, `parsedliability`) VALUES
(1, '[b]Name:[/b] Max Mustermann\r\n[b]Straße:[/b] Musterstraße 1\r\n[b]PLZ/Ort:[/b] 12345 Musterstadt\r\n[b]Bundesland:[/b] Rheinland-Pfalz\r\n[b]Staat:[/b] Deutschland', '<strong>Name:</strong> Max Mustermann<br /><strong>Straße:</strong> Musterstraße 1<br /><strong>PLZ/Ort:</strong> 12345 Musterstadt<br /><strong>Bundesland:</strong> Rheinland-Pfalz<br /><strong>Staat:</strong> Deutschland', '[center][b]Haftungsausschluss[/b][/center]\r\n\r\n[u]1. Inhalt des Onlineangebotes[/u]\r\nDer Autor übernimmt keinerlei Gewähr für die Aktualität, Korrektheit, Vollständigkeit oder Qualität der bereitgestellten Informationen. Haftungsansprüche gegen den Autor, welche sich auf Schäden materieller oder ideeller Art beziehen, die durch die Nutzung oder Nichtnutzung der dargebotenen Informationen bzw. durch die Nutzung fehlerhafter und unvollständiger Informationen verursacht wurden, sind grundsätzlich ausgeschlossen, sofern seitens des Autors kein nachweislich vorsätzliches oder grob fahrlässiges Verschulden vorliegt.\r\nAlle Angebote sind freibleibend und unverbindlich. Der Autor behält es sich ausdrücklich vor, Teile der Seiten oder das gesamte Angebot ohne gesonderte Ankündigung zu verändern, zu ergänzen, zu löschen oder die Veröffentlichung zeitweise oder endgültig einzustellen.\r\n\r\n\r\n[u]2. Verweise und Links[/u]\r\nBei direkten oder indirekten Verweisen auf fremde Webseiten (&quot;Hyperlinks&quot;), die außerhalb des Verantwortungsbereiches des Autors liegen, würde eine Haftungsverpflichtung ausschließlich in dem Fall in Kraft treten, in dem der Autor von den Inhalten Kenntnis hat und es ihm technisch möglich und zumutbar wäre, die Nutzung im Falle rechtswidriger Inhalte zu verhindern.\r\nDer Autor erklärt hiermit ausdrücklich, dass zum Zeitpunkt der Linksetzung keine illegalen Inhalte auf den zu verlinkenden Seiten erkennbar waren. Auf die aktuelle und zukünftige Gestaltung, die Inhalte oder die Urheberschaft der verlinkten/verknüpften Seiten hat der Autor keinerlei Einfluss. Deshalb distanziert er sich hiermit ausdrücklich von allen Inhalten aller verlinkten /verknüpften Seiten, die nach der Linksetzung verändert wurden. Diese Feststellung gilt für alle innerhalb des eigenen Internetangebotes gesetzten Links und Verweise sowie für Fremdeinträge in vom Autor eingerichteten Gästebüchern, Diskussionsforen, Linkverzeichnissen, Mailinglisten und in allen anderen Formen von Datenbanken, auf deren Inhalt externe Schreibzugriffe möglich sind. Für illegale, fehlerhafte oder unvollständige Inhalte und insbesondere für Schäden, die aus der Nutzung oder Nichtnutzung solcherart dargebotener Informationen entstehen, haftet allein der Anbieter der Seite, auf welche verwiesen wurde, nicht derjenige, der über Links auf die jeweilige Veröffentlichung lediglich verweist.\r\n\r\n\r\n[u]3. Urheber- und Kennzeichenrecht[/u]\r\nDer Autor ist bestrebt, in allen Publikationen die Urheberrechte der verwendeten Bilder, Grafiken, Tondokumente, Videosequenzen und Texte zu beachten, von ihm selbst erstellte Bilder, Grafiken, Tondokumente, Videosequenzen und Texte zu nutzen oder auf lizenzfreie Grafiken, Tondokumente, Videosequenzen und Texte zurückzugreifen.\r\nAlle innerhalb des Internetangebotes genannten und ggf. durch Dritte geschützten Marken- und Warenzeichen unterliegen uneingeschränkt den Bestimmungen des jeweils gültigen Kennzeichenrechts und den Besitzrechten der jeweiligen eingetragenen Eigentümer. Allein aufgrund der bloßen Nennung ist nicht der Schluss zu ziehen, dass Markenzeichen nicht durch Rechte Dritter geschützt sind!\r\nDas Copyright für veröffentlichte, vom Autor selbst erstellte Objekte bleibt allein beim Autor der Seiten. Eine Vervielfältigung oder Verwendung solcher Grafiken, Tondokumente, Videosequenzen und Texte in anderen elektronischen oder gedruckten Publikationen ist ohne ausdrückliche Zustimmung des Autors nicht gestattet.\r\n\r\n\r\n[u]4. Datenschutz[/u]\r\nSofern innerhalb des Internetangebotes die Möglichkeit zur Eingabe persönlicher oder geschäftlicher Daten (Emailadressen, Namen, Anschriften) besteht, so erfolgt die Preisgabe dieser Daten seitens des Nutzers auf ausdrücklich freiwilliger Basis. Die Inanspruchnahme und Bezahlung aller angebotenen Dienste ist - soweit technisch möglich und zumutbar - auch ohne Angabe solcher Daten bzw. unter Angabe anonymisierter Daten oder eines Pseudonyms gestattet. Die Nutzung der im Rahmen des Impressums oder vergleichbarer Angaben veröffentlichten Kontaktdaten wie Postanschriften, Telefon- und Faxnummern sowie Emailadressen durch Dritte zur Übersendung von nicht ausdrücklich angeforderten Informationen ist nicht gestattet. Rechtliche Schritte gegen die Versender von sogenannten Spam-Mails bei Verstössen gegen dieses Verbot sind ausdrücklich vorbehalten. ', '<div style="text-align:center;"><strong>Haftungsausschluss</strong></div><br /><br /><span style="text-decoration:underline;">1. Inhalt des Onlineangebotes</span><br />Der Autor übernimmt keinerlei Gewähr für die Aktualität, Korrektheit, Vollständigkeit oder Qualität der bereitgestellten Informationen. Haftungsansprüche gegen den Autor, welche sich auf Schäden materieller oder ideeller Art beziehen, die durch die Nutzung oder Nichtnutzung der dargebotenen Informationen bzw. durch die Nutzung fehlerhafter und unvollständiger Informationen verursacht wurden, sind grundsätzlich ausgeschlossen, sofern seitens des Autors kein nachweislich vorsätzliches oder grob fahrlässiges Verschulden vorliegt.<br />Alle Angebote sind freibleibend und unverbindlich. Der Autor behält es sich ausdrücklich vor, Teile der Seiten oder das gesamte Angebot ohne gesonderte Ankündigung zu verändern, zu ergänzen, zu löschen oder die Veröffentlichung zeitweise oder endgültig einzustellen.<br /><br /><br /><span style="text-decoration:underline;">2. Verweise und Links</span><br />Bei direkten oder indirekten Verweisen auf fremde Webseiten (&quot;Hyperlinks&quot;), die außerhalb des Verantwortungsbereiches des Autors liegen, würde eine Haftungsverpflichtung ausschließlich in dem Fall in Kraft treten, in dem der Autor von den Inhalten Kenntnis hat und es ihm technisch möglich und zumutbar wäre, die Nutzung im Falle rechtswidriger Inhalte zu verhindern.<br />Der Autor erklärt hiermit ausdrücklich, dass zum Zeitpunkt der Linksetzung keine illegalen Inhalte auf den zu verlinkenden Seiten erkennbar waren. Auf die aktuelle und zukünftige Gestaltung, die Inhalte oder die Urheberschaft der verlinkten/verknüpften Seiten hat der Autor keinerlei Einfluss. Deshalb distanziert er sich hiermit ausdrücklich von allen Inhalten aller verlinkten /verknüpften Seiten, die nach der Linksetzung verändert wurden. Diese Feststellung gilt für alle innerhalb des eigenen Internetangebotes gesetzten Links und Verweise sowie für Fremdeinträge in vom Autor eingerichteten Gästebüchern, Diskussionsforen, Linkverzeichnissen, Mailinglisten und in allen anderen Formen von Datenbanken, auf deren Inhalt externe Schreibzugriffe möglich sind. Für illegale, fehlerhafte oder unvollständige Inhalte und insbesondere für Schäden, die aus der Nutzung oder Nichtnutzung solcherart dargebotener Informationen entstehen, haftet allein der Anbieter der Seite, auf welche verwiesen wurde, nicht derjenige, der über Links auf die jeweilige Veröffentlichung lediglich verweist.<br /><br /><br /><span style="text-decoration:underline;">3. Urheber- und Kennzeichenrecht</span><br />Der Autor ist bestrebt, in allen Publikationen die Urheberrechte der verwendeten Bilder, Grafiken, Tondokumente, Videosequenzen und Texte zu beachten, von ihm selbst erstellte Bilder, Grafiken, Tondokumente, Videosequenzen und Texte zu nutzen oder auf lizenzfreie Grafiken, Tondokumente, Videosequenzen und Texte zurückzugreifen.<br />Alle innerhalb des Internetangebotes genannten und ggf. durch Dritte geschützten Marken- und Warenzeichen unterliegen uneingeschränkt den Bestimmungen des jeweils gültigen Kennzeichenrechts und den Besitzrechten der jeweiligen eingetragenen Eigentümer. Allein aufgrund der bloßen Nennung ist nicht der Schluss zu ziehen, dass Markenzeichen nicht durch Rechte Dritter geschützt sind!<br />Das Copyright für veröffentlichte, vom Autor selbst erstellte Objekte bleibt allein beim Autor der Seiten. Eine Vervielfältigung oder Verwendung solcher Grafiken, Tondokumente, Videosequenzen und Texte in anderen elektronischen oder gedruckten Publikationen ist ohne ausdrückliche Zustimmung des Autors nicht gestattet.<br /><br /><br /><span style="text-decoration:underline;">4. Datenschutz</span><br />Sofern innerhalb des Internetangebotes die Möglichkeit zur Eingabe persönlicher oder geschäftlicher Daten (Emailadressen, Namen, Anschriften) besteht, so erfolgt die Preisgabe dieser Daten seitens des Nutzers auf ausdrücklich freiwilliger Basis. Die Inanspruchnahme und Bezahlung aller angebotenen Dienste ist - soweit technisch möglich und zumutbar - auch ohne Angabe solcher Daten bzw. unter Angabe anonymisierter Daten oder eines Pseudonyms gestattet. Die Nutzung der im Rahmen des Impressums oder vergleichbarer Angaben veröffentlichten Kontaktdaten wie Postanschriften, Telefon- und Faxnummern sowie Emailadressen durch Dritte zur Übersendung von nicht ausdrücklich angeforderten Informationen ist nicht gestattet. Rechtliche Schritte gegen die Versender von sogenannten Spam-Mails bei Verstössen gegen dieses Verbot sind ausdrücklich vorbehalten. ');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_ipbase`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_ipbase` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'A_I Col',
  `ip` varchar(41) NOT NULL COMMENT 'IP to lock',
  `gbook` int(11) NOT NULL DEFAULT '-1' COMMENT 'lock time for the guestbook',
  `contact` int(11) NOT NULL DEFAULT '-1' COMMENT 'lock time for the contact form',
  `stats` int(11) NOT NULL DEFAULT '-1' COMMENT 'lock time for the statistics',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='IP-table for locks' AUTO_INCREMENT=2 ;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_menus`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_menus` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'A_I Col',
  `menuid` smallint(5) unsigned NOT NULL COMMENT 'id of the owner menu',
  `type` smallint(1) NOT NULL COMMENT 'address type',
  `name` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'shown name',
  `address` varchar(300) NOT NULL COMMENT 'address',
  `extern` smallint(1) NOT NULL DEFAULT '0' COMMENT '1: open extern;0: open intern',
  `level` smallint(2) NOT NULL DEFAULT '0' COMMENT 'level of the entry',
  `position` smallint(6) unsigned NOT NULL COMMENT 'position of the entry in the owner menu',
  `visible` smallint(1) NOT NULL COMMENT '1: visible;0: invisible',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Menus' AUTO_INCREMENT=65 ;

--
-- Daten für Tabelle `{PREFIX}_menus`
--

INSERT INTO `{PREFIX}_menus` (`id`, `menuid`, `type`, `name`, `address`, `extern`, `level`, `position`, `visible`) VALUES
(2, 1, 2, 'Gästebuch', 'gbook', 0, 0, 3, 1),
(4, 1, 2, 'Partner', 'partners', 0, 0, 4, 1),
(5, 1, 2, 'Anfahrt', 'drive', 0, 0, 6, 1),
(49, 1, 1, 'Main', 'menu', 0, 0, 1, 1),
(55, 1, 2, 'Impressum', 'imprint', 0, 0, 8, 1),
(25, 1, 2, 'Neuigkeiten', 'news', 0, 0, 2, 1),
(58, 1, 1, 'Misc', 'menu', 0, 0, 9, 0),
(50, 1, 2, 'Kontakt', 'contact', 0, 0, 7, 1),
(31, 2, 4, 'Datum', 'date', 0, 0, 0, 1),
(32, 1, 1, 'Info', 'menu', 0, 0, 5, 1),
(53, 2, 4, 'Ihre Einstellungen', 'userconfig', 0, 0, 1, 1),
(64, 1, 4, 'Counter', 'counter', 0, 0, 14, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_news`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_news` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'A_I Col',
  `author` varchar(50) NOT NULL COMMENT 'author of the news',
  `title` varchar(70) NOT NULL COMMENT 'title of the news',
  `rawtext` text NOT NULL COMMENT 'raw text of the news',
  `parsedtext` text NOT NULL COMMENT 'parsed text of the news',
  `datetime` datetime NOT NULL COMMENT 'date the news was posted at',
  `comments_allowed` smallint(1) NOT NULL DEFAULT '1' COMMENT 'defines whether comments can be posted',
  `shorten` smallint(1) NOT NULL DEFAULT '0' COMMENT 'defines whether the script can shorten the news',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='News' AUTO_INCREMENT=1 ;



-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_overview`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_overview` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'A_I Col',
  `langindex` varchar(30) NOT NULL COMMENT 'language index for the label',
  `dataquery` varchar(300) NOT NULL COMMENT 'the query for the shown data',
  `modul` varchar(30) NOT NULL COMMENT 'the related modul',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Overview contents' AUTO_INCREMENT=20 ;

--
-- Daten für Tabelle `{PREFIX}_overview`
--

INSERT INTO `{PREFIX}_overview` (`id`, `langindex`, `dataquery`, `modul`) VALUES
(1, 'cms_std_modul', 'SELECT `value` FROM `{PREFIX}_config` WHERE `index`=''cms_std_modul'' LIMIT 1', 'cms'),
(2, 'gbook_last_author', 'SELECT `author` FROM `{PREFIX}_gbook` ORDER BY `id` DESC LIMIT 1', 'gbook'),
(3, 'gbook_count', 'SELECT count(*) FROM `{PREFIX}_gbook`', 'gbook'),
(4, 'gbook_last_date', 'SELECT DATE_FORMAT(`date`,''%d.%c.%Y'') FROM `{PREFIX}_gbook` ORDER BY `id` DESC LIMIT 1', 'gbook'),
(5, 'news_last_author', 'SELECT `author` FROM `{PREFIX}_news` ORDER BY `id` DESC LIMIT 1', 'news'),
(6, 'news_count', 'SELECT count(*) FROM `{PREFIX}_news`', 'news'),
(7, 'news_last_date', 'SELECT `date` FROM `{PREFIX}_news` ORDER BY `id` DESC LIMIT 1', 'news'),
(9, 'partners_count', 'SELECT count(*) FROM `{PREFIX}_partners`', 'partners'),
(10, 'self_count', 'SELECT count(*) FROM `{PREFIX}_selfcontent`', 'self'),
(11, 'google_map_posl', 'SELECT `posl` FROM `{PREFIX}_drive`  LIMIT 1', 'google_map'),
(12, 'google_map_posb', 'SELECT `posb` FROM `{PREFIX}_drive`  LIMIT 1', 'google_map'),
(13, 'cms_std_lang', 'SELECT `value` FROM `{PREFIX}_config` WHERE `index`=''cms_std_lang'' LIMIT 1', 'cms'),
(17, 'stats_browsers', 'SELECT count(*) FROM `{PREFIX}_stats` WHERE `type`=''browser''', 'stats'),
(14, 'cms_std_design', 'SELECT `value` FROM `{PREFIX}_config` WHERE `index`=''cms_std_design'' LIMIT 1', 'cms'),
(15, 'contact_addr_count', 'SELECT count(*) FROM `{PREFIX}_contact`', 'contact'),
(16, 'stats_accesses', 'SELECT `count` FROM `{PREFIX}_stats` WHERE `type`=''counter'' LIMIT 1', 'stats'),
(18, 'stats_os', 'SELECT count(*) FROM `{PREFIX}_stats` WHERE `type`=''os''', 'stats'),
(19, 'stats_langs', 'SELECT count(*) FROM `{PREFIX}_stats` WHERE `type`=''lang''', 'stats');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_partners`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_partners` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'A_I Col',
  `name` varchar(100) NOT NULL COMMENT 'name of the partner',
  `pageuri` varchar(150) DEFAULT NULL COMMENT 'uri of the partner''s website',
  `banneruri` varchar(150) DEFAULT NULL COMMENT 'uri of the partner''s banner',
  `position` int(11) NOT NULL COMMENT 'position of the partner',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Partners' AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `{PREFIX}_partners`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_selfcontent`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_selfcontent` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'A_I Col',
  `name` varchar(40) NOT NULL COMMENT 'shown name of the self created content',
  `content` text NOT NULL COMMENT 'text of the content (raw)',
  `bbcode` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '1: use bbcode;0: don''t use bbcode',
  `smiles` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '1: use smiles;0: don''t use smiles',
  `html` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '1: use html;0: don''t use html',
  `added` datetime NOT NULL COMMENT 'the date the content was added',
  `modified` datetime NOT NULL COMMENT 'the date the content was last edited',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Self created contents' AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `{PREFIX}_selfcontent`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_smiles`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_smiles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'A_I Col',
  `smile` varchar(20) NOT NULL COMMENT 'the smile to write',
  `file` varchar(50) NOT NULL COMMENT 'the related image file',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Smiles' AUTO_INCREMENT=32 ;

--
-- Daten für Tabelle `{PREFIX}_smiles`
--

INSERT INTO `{PREFIX}_smiles` (`id`, `smile`, `file`) VALUES
(1, ':)', 'grinsen.gif'),
(2, ':(', 'traurig.gif'),
(3, ':@', 'boese.gif'),
(4, ':D', 'breites_grinsen.gif'),
(5, ';(', 'weinen.gif'),
(6, ':|', 'sprachlos.gif'),
(8, ':P', 'freches_grinsen.gif'),
(9, ':O', 'ueberrrascht.gif'),
(11, ';)', 'zwinkern.gif'),
(13, '(finger)', 'finger.gif'),
(14, '(swear)', 'swear.gif'),
(15, '(tmi)', 'tmi.gif'),
(16, '(blush)', 'verlegen.gif'),
(17, ':^)', 'verwirrt.gif'),
(18, '(toivo)', 'toivo.gif'),
(19, '(puke)', 'puke.gif'),
(20, '(rock)', 'rock.gif'),
(21, '(smoking)', 'smoking.gif'),
(22, '(headbang)', 'headbang.gif'),
(23, '(chuckle)', 'hihi.gif'),
(24, '(wasntme)', 'ich_wars_nicht.gif'),
(25, '(mooning)', 'mooning.gif'),
(26, '(poolparty)', 'poolparty.gif'),
(27, '(drunk)', 'drunk.gif'),
(28, '(fubar)', 'fubar.gif'),
(29, '8-)', 'cool.gif'),
(30, '(bug)', 'bug.gif'),
(31, '(bandit)', 'bandit.gif');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `{PREFIX}_stats`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}_stats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'A_I Col',
  `type` varchar(50) NOT NULL COMMENT 'type of the counted thing',
  `value` varchar(50) NOT NULL COMMENT 'name / label of the thing',
  `count` int(11) NOT NULL COMMENT 'current count',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Page statistics' AUTO_INCREMENT=5 ;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
