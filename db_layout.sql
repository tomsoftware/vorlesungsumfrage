SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `vote` (
  `vote_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vote_Name` varchar(50) NOT NULL,
  `vote_Comment` varchar(255) NOT NULL,
  `vote_Create_Date` date DEFAULT NULL,
  PRIMARY KEY (`vote_ID`),
  UNIQUE KEY `vote_Name` (`vote_Name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

CREATE TABLE IF NOT EXISTS `vote_data` (
  `vote_data_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vote_Index` int(11) unsigned NOT NULL,
  `vote_Session_Index` int(10) unsigned NOT NULL,
  `vote_data_timestamp` datetime NOT NULL,
  `vote_data_reload_suppress` int(10) unsigned NOT NULL,
  `int_01` smallint(6) DEFAULT NULL,
  `int_02` smallint(6) DEFAULT NULL,
  `int_03` smallint(6) DEFAULT NULL,
  `int_04` smallint(6) DEFAULT NULL,
  `int_05` smallint(6) DEFAULT NULL,
  `int_06` smallint(6) DEFAULT NULL,
  `int_07` smallint(6) DEFAULT NULL,
  `int_08` smallint(6) DEFAULT NULL,
  `int_09` smallint(6) DEFAULT NULL,
  `int_10` smallint(6) DEFAULT NULL,
  `int_11` smallint(6) DEFAULT NULL,
  `int_12` smallint(6) DEFAULT NULL,
  `int_13` smallint(6) DEFAULT NULL,
  `int_14` smallint(6) DEFAULT NULL,
  `int_15` smallint(6) DEFAULT NULL,
  `int_16` smallint(6) DEFAULT NULL,
  `int_17` smallint(6) DEFAULT NULL,
  `int_18` smallint(6) DEFAULT NULL,
  `int_19` smallint(6) DEFAULT NULL,
  `int_20` smallint(6) DEFAULT NULL,
  `int_21` smallint(6) DEFAULT NULL,
  `int_22` smallint(6) DEFAULT NULL,
  `int_23` smallint(6) DEFAULT NULL,
  `int_24` smallint(6) DEFAULT NULL,
  `int_25` smallint(6) DEFAULT NULL,
  `int_26` smallint(6) DEFAULT NULL,
  `int_27` smallint(6) DEFAULT NULL,
  `int_28` smallint(6) DEFAULT NULL,
  `int_29` smallint(6) DEFAULT NULL,
  `int_30` smallint(6) DEFAULT NULL,
  `int_31` smallint(6) DEFAULT NULL,
  `int_32` smallint(6) DEFAULT NULL,
  `int_33` smallint(6) DEFAULT NULL,
  `int_34` smallint(6) DEFAULT NULL,
  `int_35` smallint(6) DEFAULT NULL,
  `int_36` smallint(6) DEFAULT NULL,
  `int_37` smallint(6) DEFAULT NULL,
  `int_38` smallint(6) DEFAULT NULL,
  `int_39` smallint(6) DEFAULT NULL,
  `int_40` smallint(6) DEFAULT NULL,
  `int_41` smallint(6) DEFAULT NULL,
  `int_42` smallint(6) DEFAULT NULL,
  `int_43` smallint(6) DEFAULT NULL,
  `int_44` smallint(6) DEFAULT NULL,
  `int_45` smallint(6) DEFAULT NULL,
  `int_46` smallint(6) DEFAULT NULL,
  `int_47` smallint(6) DEFAULT NULL,
  `int_48` smallint(6) DEFAULT NULL,
  `int_49` smallint(6) DEFAULT NULL,
  `int_50` smallint(6) DEFAULT NULL,
  `int_51` smallint(6) DEFAULT NULL,
  `int_52` smallint(6) DEFAULT NULL,
  `int_53` smallint(6) DEFAULT NULL,
  `int_54` smallint(6) DEFAULT NULL,
  `int_55` smallint(6) DEFAULT NULL,
  `int_56` smallint(6) DEFAULT NULL,
  `int_57` smallint(6) DEFAULT NULL,
  `int_58` smallint(6) DEFAULT NULL,
  `int_59` smallint(6) DEFAULT NULL,
  `int_60` smallint(6) DEFAULT NULL,
  `str_01` varchar(255) DEFAULT NULL,
  `str_02` varchar(255) DEFAULT NULL,
  `str_03` varchar(255) DEFAULT NULL,
  `str_04` varchar(255) DEFAULT NULL,
  `str_05` varchar(255) DEFAULT NULL,
  `str_06` varchar(255) DEFAULT NULL,
  `str_07` varchar(255) DEFAULT NULL,
  `str_08` varchar(255) DEFAULT NULL,
  `str_09` varchar(255) DEFAULT NULL,
  `str_10` varchar(255) DEFAULT NULL,
  `str_11` varchar(255) DEFAULT NULL,
  `str_12` varchar(255) DEFAULT NULL,
  `str_13` varchar(255) DEFAULT NULL,
  `str_14` varchar(255) DEFAULT NULL,
  `str_15` varchar(255) DEFAULT NULL,
  `str_16` varchar(255) DEFAULT NULL,
  `str_17` varchar(255) DEFAULT NULL,
  `str_18` varchar(255) DEFAULT NULL,
  `str_19` varchar(255) DEFAULT NULL,
  `str_20` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`vote_data_ID`),
  KEY `vote_Index` (`vote_Index`),
  KEY `vote_Session_Index` (`vote_Session_Index`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1887 ;

CREATE TABLE IF NOT EXISTS `vote_field` (
  `vote_Field_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vote_Index` int(10) unsigned NOT NULL,
  `vote_Field_Name` varchar(50) NOT NULL,
  `vote_Field_Comment` varchar(255) DEFAULT NULL,
  `vote_Field_Type` int(11) NOT NULL,
  `vote_Field_isNecessary` tinyint(1) NOT NULL DEFAULT '0',
  `vote_Field_Auswertung_Group` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '0: global (z.B. Bücherwünsche); 1: root (Vorlesung); >1: Subgroup: (Übungsgruppe)',
  `vote_Field_Auswertung_GroupBy` tinyint(1) NOT NULL DEFAULT '0',
  `vote_Field_KeepAfterSaving` tinyint(1) NOT NULL DEFAULT '0',
  `vote_Field_Min` int(11) DEFAULT '0',
  `vote_Field_Max` int(11) DEFAULT '255',
  `vote_Field_Sortpos` int(11) NOT NULL DEFAULT '999',
  `vote_Field_Data_Fieldname` char(4) DEFAULT NULL,
  `vote_Field_List_Index` int(11) unsigned DEFAULT NULL,
  `vote_Field_Filter_Index` int(10) unsigned DEFAULT NULL,
  `vote_Field_Filter_Depending_Field_Index` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`vote_Field_ID`),
  UNIQUE KEY `vote_Index` (`vote_Index`,`vote_Field_Data_Fieldname`),
  KEY `vote_Field_Filter_Index` (`vote_Field_Filter_Index`),
  KEY `vote_Field_List_Index` (`vote_Field_List_Index`),
  KEY `vote_Field_Filter_Depending_Field_Index` (`vote_Field_Filter_Depending_Field_Index`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=407 ;

CREATE TABLE IF NOT EXISTS `vote_filter` (
  `vote_Filter_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vote_Filter_Name` varchar(50) NOT NULL,
  `vote_Filter_Destination_List_Index` int(10) unsigned NOT NULL,
  `vote_Filter_Depending_List_Index` int(10) unsigned DEFAULT NULL,
  `vote_Filter_allowEdit` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`vote_Filter_ID`),
  UNIQUE KEY `vote_Filter_Name` (`vote_Filter_Name`),
  KEY `vote_Filter_Destination_List_Index` (`vote_Filter_Destination_List_Index`),
  KEY `vote_Filter_Depending_List_Index` (`vote_Filter_Depending_List_Index`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

CREATE TABLE IF NOT EXISTS `vote_filter_item` (
  `vote_Filter_Item_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vote_Filter_Item_Name` varchar(50) NOT NULL,
  `vote_Filter_Item_Filter_Index` int(10) unsigned NOT NULL,
  `Depend_List_Item_Index` int(10) unsigned DEFAULT NULL,
  `Show_List_Item_Index` int(10) unsigned NOT NULL,
  PRIMARY KEY (`vote_Filter_Item_ID`),
  UNIQUE KEY `vote_Filter_Item_UNIQUE` (`vote_Filter_Item_Filter_Index`,`Depend_List_Item_Index`,`Show_List_Item_Index`),
  KEY `Depend_List_Item_Index` (`Depend_List_Item_Index`),
  KEY `Show_List_Item_Index` (`Show_List_Item_Index`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3862 ;

CREATE TABLE IF NOT EXISTS `vote_list` (
  `vote_List_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vote_List_Name` varchar(50) NOT NULL,
  `vote_List_Einheit` varchar(50) DEFAULT NULL,
  `vote_List_Einheit2` varchar(100) DEFAULT NULL,
  `vote_List_allowEdit` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`vote_List_ID`),
  UNIQUE KEY `vote_List_Name` (`vote_List_Name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

CREATE TABLE IF NOT EXISTS `vote_list_item` (
  `vote_List_Item_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vote_List_Index` int(11) unsigned NOT NULL,
  `vote_List_Item_Name` varchar(50) DEFAULT NULL,
  `vote_List_Item_Value` int(11) DEFAULT NULL,
  `vote_List_Item_Sortpos` int(11) NOT NULL DEFAULT '999',
  PRIMARY KEY (`vote_List_Item_ID`),
  UNIQUE KEY `vote_List_Item_Unique` (`vote_List_Index`,`vote_List_Item_Name`,`vote_List_Item_Value`),
  KEY `vote_list_item_ibfk_1` (`vote_List_Index`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=429 ;

CREATE TABLE IF NOT EXISTS `vote_session` (
  `vote_session_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vote_session_timestamp` datetime NOT NULL,
  `vote_session_username` varchar(50) NOT NULL,
  `vote_session_challenge` varchar(25) NOT NULL,
  `vote_session_isAdmin` tinyint(1) NOT NULL DEFAULT '0',
  `vote_session_isModerator` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`vote_session_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=206 ;


ALTER TABLE `vote_data`
  ADD CONSTRAINT `vote_data_ibfk_1` FOREIGN KEY (`vote_Index`) REFERENCES `vote` (`vote_ID`),
  ADD CONSTRAINT `vote_data_ibfk_2` FOREIGN KEY (`vote_Session_Index`) REFERENCES `vote_session` (`vote_session_ID`);

ALTER TABLE `vote_field`
  ADD CONSTRAINT `vote_field_ibfk_10` FOREIGN KEY (`vote_Field_Filter_Index`) REFERENCES `vote_filter` (`vote_Filter_ID`),
  ADD CONSTRAINT `vote_field_ibfk_11` FOREIGN KEY (`vote_Field_Filter_Depending_Field_Index`) REFERENCES `vote_field` (`vote_Field_ID`),
  ADD CONSTRAINT `vote_field_ibfk_8` FOREIGN KEY (`vote_Index`) REFERENCES `vote` (`vote_ID`),
  ADD CONSTRAINT `vote_field_ibfk_9` FOREIGN KEY (`vote_Field_List_Index`) REFERENCES `vote_list` (`vote_List_ID`);

ALTER TABLE `vote_filter`
  ADD CONSTRAINT `vote_filter_ibfk_1` FOREIGN KEY (`vote_Filter_Destination_List_Index`) REFERENCES `vote_list` (`vote_List_ID`),
  ADD CONSTRAINT `vote_filter_ibfk_2` FOREIGN KEY (`vote_Filter_Depending_List_Index`) REFERENCES `vote_list` (`vote_List_ID`);

ALTER TABLE `vote_filter_item`
  ADD CONSTRAINT `vote_filter_item_ibfk_1` FOREIGN KEY (`vote_Filter_Item_Filter_Index`) REFERENCES `vote_filter` (`vote_Filter_ID`),
  ADD CONSTRAINT `vote_filter_item_ibfk_2` FOREIGN KEY (`Depend_List_Item_Index`) REFERENCES `vote_list_item` (`vote_List_Item_ID`),
  ADD CONSTRAINT `vote_filter_item_ibfk_3` FOREIGN KEY (`Show_List_Item_Index`) REFERENCES `vote_list_item` (`vote_List_Item_ID`);

ALTER TABLE `vote_list_item`
  ADD CONSTRAINT `vote_list_item_ibfk_1` FOREIGN KEY (`vote_List_Index`) REFERENCES `vote_list` (`vote_List_ID`),
  ADD CONSTRAINT `vote_list_item_ibfk_2` FOREIGN KEY (`vote_List_Index`) REFERENCES `vote_list` (`vote_List_ID`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
