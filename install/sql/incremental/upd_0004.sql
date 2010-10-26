ALTER TABLE  `web_domain` CHANGE  `apache_directives`  `apache_directives` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE  `web_domain` CHANGE  `php_open_basedir`  `php_open_basedir` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE  `web_domain` CHANGE  `custom_php_ini`  `custom_php_ini` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE  `web_domain` DROP  `document_root_www`;