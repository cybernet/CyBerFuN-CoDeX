--- CyBerFuN.Ro
--- By CyBerNe7
---            ---
--- www.cyberfun.ro ---
--- last update 09/06/2009 ( US datetime ) / cybernet / www.cyberfun.ro /

ALTER TABLE `sitelog` CHANGE `type` `type` ENUM( 'torrentupload', 'torrentedit', 'torrentdelete', 'promotion', 'demotion', 'addwarn', 'remwarn', 'accenabled', 'accdisabled', 'accdeleted', 'slowautoclean', 'autohitrun', 'autobackupdb', 'autooptimizedb', 'passwordreset', 'ratioedit', 'newmember', 'customsmiles', 'autoclean', 'autowarn', 'autodewarn', 'autoban', 'staffaction', 'shoutcom', 'userdelete', 'cyberfun_clean_comments_from_deleted_torrents', 'rating_table_cleaned', 'thanks_table_cleaned', 'snatched_table_cleaned' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'torrentupload' COMMENT 'last update 09/04/2009 ( US datetime ) / cybernet / www.cyberfun.ro /' ;

DELETE FROM `staffpanel` WHERE `id` = 13 LIMIT 1 ;
DELETE FROM `stylesheets` WHERE `stylesheets`.`id` = 3 LIMIT 1;
DELETE FROM `stylesheets` WHERE `stylesheets`.`id` = 2 LIMIT 1;
DELETE FROM `stylesheets` WHERE `stylesheets`.`id` = 5 LIMIT 1;
DELETE FROM `stylesheets` WHERE `stylesheets`.`id` = 6 LIMIT 1;
DELETE FROM `stylesheets` WHERE `stylesheets`.`id` = 1 LIMIT 1;
DELETE FROM `stylesheets` WHERE `stylesheets`.`id` = 7 LIMIT 1;
DELETE FROM `stylesheets` WHERE `stylesheets`.`id` = 8 LIMIT 1;
DELETE FROM `stylesheets` WHERE `stylesheets`.`id` = 9 LIMIT 1;
ALTER TABLE `users` CHANGE `title` `title` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'last update 09/04/2009 ( US datetime ) / cybernet / www.cyberfun.ro /' ;
DELETE FROM `bonus` WHERE `bonus`.`id` = 5 LIMIT 1;
ALTER TABLE `topics` CHANGE `subject` `subject` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL 

--- Have FuN on CyBerFuN / cybernet2u