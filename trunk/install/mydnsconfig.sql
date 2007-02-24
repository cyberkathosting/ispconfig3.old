-- phpMyAdmin SQL Dump
-- version 2.9.0.3
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Erstellungszeit: 24. Februar 2007 um 16:24
-- Server Version: 5.0.24
-- PHP-Version: 5.1.4
-- 
-- Datenbank: `mydnsconfig1_1`
-- 

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `rr`
-- 

CREATE TABLE `rr` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sys_userid` int(11) NOT NULL,
  `sys_groupid` int(11) NOT NULL,
  `sys_perm_user` varchar(5) NOT NULL,
  `sys_perm_group` varchar(5) NOT NULL,
  `sys_perm_other` varchar(5) NOT NULL,
  `zone` int(10) unsigned NOT NULL,
  `name` char(64) NOT NULL,
  `type` enum('A','AAAA','CNAME','HINFO','MX','NAPTR','NS','PTR','RP','SRV','TXT') default NULL,
  `data` char(128) NOT NULL,
  `aux` int(10) unsigned NOT NULL,
  `ttl` int(10) unsigned NOT NULL default '86400',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `rr` (`zone`,`name`,`type`,`data`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `rr`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `soa`
-- 

CREATE TABLE `soa` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sys_userid` int(11) NOT NULL,
  `sys_groupid` int(11) NOT NULL,
  `sys_perm_user` varchar(5) NOT NULL,
  `sys_perm_group` varchar(5) NOT NULL,
  `sys_perm_other` varchar(5) NOT NULL,
  `origin` char(255) NOT NULL,
  `ns` char(255) NOT NULL,
  `mbox` char(255) NOT NULL,
  `serial` int(10) unsigned NOT NULL default '1',
  `refresh` int(10) unsigned NOT NULL default '28800',
  `retry` int(10) unsigned NOT NULL default '7200',
  `expire` int(10) unsigned NOT NULL default '604800',
  `minimum` int(10) unsigned NOT NULL default '86400',
  `ttl` int(10) unsigned NOT NULL default '86400',
  `active` enum('Y','N') NOT NULL,
  `xfer` char(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `origin` (`origin`),
  KEY `active` (`active`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `soa`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `sys_datalog`
-- 

CREATE TABLE `sys_datalog` (
  `datalog_id` bigint(20) NOT NULL auto_increment,
  `dbtable` varchar(255) NOT NULL default '',
  `dbidx` varchar(255) NOT NULL default '',
  `action` char(1) NOT NULL default '',
  `tstamp` bigint(20) NOT NULL default '0',
  `user` varchar(255) NOT NULL default '',
  `data` text NOT NULL,
  PRIMARY KEY  (`datalog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `sys_datalog`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `sys_dbsync`
-- 

CREATE TABLE `sys_dbsync` (
  `id` bigint(20) NOT NULL auto_increment,
  `jobname` varchar(255) NOT NULL default '',
  `sync_interval_minutes` int(11) NOT NULL default '0',
  `db_type` varchar(255) NOT NULL default '',
  `db_host` varchar(255) NOT NULL default '',
  `db_name` varchar(255) NOT NULL default '',
  `db_username` varchar(255) NOT NULL default '',
  `db_password` varchar(255) NOT NULL default '',
  `db_tables` varchar(255) NOT NULL default 'admin,forms',
  `empty_datalog` int(11) NOT NULL default '0',
  `sync_datalog_external` int(11) NOT NULL default '0',
  `active` int(11) NOT NULL default '1',
  `last_datalog_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `last_datalog_id` (`last_datalog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `sys_dbsync`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `sys_filesync`
-- 

CREATE TABLE `sys_filesync` (
  `id` bigint(20) NOT NULL auto_increment,
  `jobname` varchar(255) NOT NULL default '',
  `sync_interval_minutes` int(11) NOT NULL default '0',
  `ftp_host` varchar(255) NOT NULL default '',
  `ftp_path` varchar(255) NOT NULL default '',
  `ftp_username` varchar(255) NOT NULL default '',
  `ftp_password` varchar(255) NOT NULL default '',
  `local_path` varchar(255) NOT NULL default '',
  `wput_options` varchar(255) NOT NULL default '--timestamping --reupload --dont-continue',
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Daten für Tabelle `sys_filesync`
-- 


-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `sys_group`
-- 

CREATE TABLE `sys_group` (
  `groupid` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  PRIMARY KEY  (`groupid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- 
-- Daten für Tabelle `sys_group`
-- 

INSERT INTO `sys_group` (`groupid`, `name`, `description`) VALUES 
(1, 'admin', 'Administrators group'),
(2, 'user', 'Users Group');

-- --------------------------------------------------------

-- 
-- Tabellenstruktur für Tabelle `sys_user`
-- 

CREATE TABLE `sys_user` (
  `userid` int(11) NOT NULL auto_increment,
  `sys_userid` int(11) NOT NULL default '0',
  `sys_groupid` int(11) NOT NULL default '0',
  `sys_perm_user` varchar(5) NOT NULL default '',
  `sys_perm_group` varchar(5) NOT NULL default '',
  `sys_perm_other` varchar(5) NOT NULL default '',
  `username` varchar(100) NOT NULL default '',
  `passwort` varchar(100) NOT NULL default '',
  `modules` varchar(255) NOT NULL default '',
  `startmodule` varchar(255) NOT NULL default '',
  `app_theme` varchar(100) NOT NULL default 'default',
  `typ` varchar(20) NOT NULL default 'user',
  `active` tinyint(4) NOT NULL default '1',
  `name` varchar(100) NOT NULL default '',
  `vorname` varchar(100) NOT NULL default '',
  `unternehmen` varchar(100) NOT NULL default '',
  `strasse` varchar(100) NOT NULL default '',
  `ort` varchar(100) NOT NULL default '',
  `plz` varchar(10) NOT NULL default '',
  `land` varchar(50) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `telefon` varchar(100) NOT NULL default '',
  `fax` varchar(100) NOT NULL default '',
  `language` varchar(10) NOT NULL default 'de',
  `groups` varchar(255) NOT NULL default '',
  `default_group` int(11) NOT NULL default '0',
  PRIMARY KEY  (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Daten für Tabelle `sys_user`
-- 

INSERT INTO `sys_user` (`userid`, `sys_userid`, `sys_groupid`, `sys_perm_user`, `sys_perm_group`, `sys_perm_other`, `username`, `passwort`, `modules`, `startmodule`, `app_theme`, `typ`, `active`, `name`, `vorname`, `unternehmen`, `strasse`, `ort`, `plz`, `land`, `email`, `url`, `telefon`, `fax`, `language`, `groups`, `default_group`) VALUES 
(1, 1, 0, 'riud', 'riud', '', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin,dns', 'dns', 'grey', 'admin', 1, '', 'Administrator', '', '', '', '', '', '', '', '', '', 'en', '1,2', 0);
