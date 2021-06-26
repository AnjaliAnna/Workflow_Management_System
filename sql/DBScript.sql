-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.3.12-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             10.1.0.5464
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for iflow
CREATE DATABASE IF NOT EXISTS `iflow` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `iflow`;

-- Dumping structure for table iflow.organisations
CREATE TABLE IF NOT EXISTS `organisations` (
  `ORG_ID` int(10) unsigned NOT NULL COMMENT 'PK',
  `ORG_NAME` varchar(64) NOT NULL,
  `ORG_DETAILS` varchar(250) DEFAULT NULL,
  `REQ_BY_EMAIL_ID` varchar(64) NOT NULL COMMENT 'Requedter''s email address.',
  `STATUS` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '0=Requested, 1= Approved, 2= Rejected',
  PRIMARY KEY (`ORG_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='List of organisations that has subscribed to iFlow';

-- Data exporting was unselected.
-- Dumping structure for table iflow.sys_config
CREATE TABLE IF NOT EXISTS `sys_config` (
  `NEXT_ID` int(11) NOT NULL DEFAULT 1 COMMENT 'Generate nextID',
  `SYSTEM_STATE` tinyint(4) NOT NULL DEFAULT 0,
  `SYSTEM_MESSAGE` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='configurations and new IDs';

-- Data exporting was unselected.
-- Dumping structure for table iflow.users
CREATE TABLE IF NOT EXISTS `users` (
  `USER_ID` int(11) NOT NULL,
  `ORG_ID` int(11) NOT NULL COMMENT 'FK',
  `USER_NAME` varchar(50) NOT NULL,
  `EMAIL_ID` varchar(64) NOT NULL,
  `PASSWORD` varchar(32) NOT NULL,
  `USER_TYPE` tinyint(4) NOT NULL COMMENT '0=Member,1=Organisation admin, 2=Organisation Owner',
  PRIMARY KEY (`USER_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='user accounts master';

-- Data exporting was unselected.
-- Dumping structure for table iflow.wf_mst
CREATE TABLE IF NOT EXISTS `wf_mst` (
  `WF_ID` int(11) NOT NULL,
  `WF_NAME` varchar(64) NOT NULL,
  `STATUS` tinyint(4) NOT NULL DEFAULT 0,
  `WF_STRING` varchar(9000) NOT NULL,
  PRIMARY KEY (`WF_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Workflow master';

-- Data exporting was unselected.
-- Dumping structure for table iflow.wf_mst_lnk
CREATE TABLE IF NOT EXISTS `wf_mst_lnk` (
  `LNK_ID` int(11) NOT NULL,
  `WF_ID` int(11) NOT NULL,
  `ORG_INDEX` tinyint(4) NOT NULL,
  `DST_INDEX` tinyint(4) NOT NULL,
  PRIMARY KEY (`LNK_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='The links between workflow stages are stored here';

-- Data exporting was unselected.
-- Dumping structure for table iflow.wf_mst_stg
CREATE TABLE IF NOT EXISTS `wf_mst_stg` (
  `STG_ID` int(11) NOT NULL,
  `WF_ID` int(11) NOT NULL,
  `STG_INDEX` tinyint(4) NOT NULL,
  `STG_TEXT` varchar(50) NOT NULL,
  `STG_LEFT` decimal(10,5) NOT NULL,
  `STG_TOP` decimal(10,5) NOT NULL,
  `STG_WIDTH` decimal(10,5) NOT NULL,
  `STG_HEIGHT` decimal(10,5) NOT NULL,
  PRIMARY KEY (`STG_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='list of stages in workflows';

-- Data exporting was unselected.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
