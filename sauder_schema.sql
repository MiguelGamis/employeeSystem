DROP DATABASE `sauder`;
CREATE DATABASE `sauder`;

USE `sauder`;

CREATE TABLE IF NOT EXISTS `request` (
  `requestID` int(11) NOT NULL AUTO_INCREMENT,
  `requestBy` int(11) NOT NULL,
  `requestType` enum('entry', 'exit') NOT NULL,
  `status` enum('pending', 'done') NOT NULL,
  `dateSubmitted` datetime NOT NULL,
  `comments` longtext,
  `employeeID` int(11) NOT NULL,
  PRIMARY KEY (`requestID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `employee` (
  `employeeID` int(11) NOT NULL,
  `firstName` varchar(128) NOT NULL,
  `lastName` varchar(128) NOT NULL,
  `CWLID` varchar(128) NOT NULL,
  `isNew` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`employeeID`),
  UNIQUE KEY (`CWLID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `orientationPackage`(
  `employeeID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY (`requestID`),
  KEY `employeeID` (`employeeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `businessCard`(
  `employeeID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `businessCardType` varchar(128) NOT NULL DEFAULT 'Type 1-A',
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY (`requestID`, `businessCardType`),
  KEY `employeeID` (`employeeID`, `businessCardType`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `businessCardType`(
  `businessCardType` varchar(128) NOT NULL,
  PRIMARY KEY (`businessCardType`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `businessCardType` (`businessCardType`) VALUES ('Type 1-A'), ('Type 1-A w/ Robert H. Lee Graduate School Logo'), ('Type 1-B'), ('Type 1-B w/ Robert H. Lee Graduate School Logo'), ('Type 1-C'), ('Type 1-C w/ Robert H. Lee Graduate School Logo');

CREATE TABLE IF NOT EXISTS `PATSystemAccount`(
  `employeeID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY (`requestID`),
  KEY `employeeID` (`employeeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
  `userID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `firstName` varchar(128) NOT NULL,
  `lastName` varchar(128) NOT NULL,
  `groupID` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `passwordHash` varchar(128) NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tenure` (
  `requestID` int(11) NOT NULL,
  `employeeID` int(11) NOT NULL,
  `position` varchar(128) NOT NULL,
  `locationName` varchar(128) NOT NULL,
  `startDate` datetime NOT NULL,
  `endDate` datetime,
  `ongoing` tinyint(1) NOT NULL DEFAULT '0',
  `term` enum('1', '2', 'both'),
  `tenureStatus` varchar(128) NOT NULL,
  `tenureType` enum('Full-Time', 'Part-Time'),
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY (`requestID`),
  KEY `employeeID` (`employeeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `tenureStatus` (
  `tenureStatus` varchar(128) NOT NULL,
  PRIMARY KEY (`tenureStatus`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `tenureStatus` (`tenureStatus`) VALUES ("Executive in Residence") , ("M&P Staff") , ("Visiting School"), ("Full-Time Lecturer"), ("Non-Union Staff"), ("Contractor"), ("Part-Time Lecturer"), ("Student Staff"), ("PhD Student"), ("DAP Sessional Lecturer"), ("CUPE Staff"), ("MSc Student"), ("Emiriti");
 
CREATE TABLE IF NOT EXISTS `voiceService` (
  `employeeID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `telephoneNumber` int(11) NOT NULL,
  `singleline` tinyint(1) NOT NULL DEFAULT '0',
  `multiline` tinyint(1) NOT NULL DEFAULT '0',
  `moveupdatelocation` tinyint(1) NOT NULL DEFAULT '0',
  `updatedisplayname` tinyint(1) NOT NULL DEFAULT '0',
  `voicemail` tinyint(1) NOT NULL DEFAULT '0',
  `unifiedmessaging` tinyint(1) NOT NULL DEFAULT '0',
  `resetvoicemailpassword` tinyint(1) NOT NULL DEFAULT '0',
  `requestvoicemailpassword` tinyint(1) NOT NULL DEFAULT '0',
  `requestlongdistancecode` tinyint(1) NOT NULL DEFAULT '0',
  `setupcallersmenu` tinyint(1) NOT NULL DEFAULT '0',
  `monthlyRental` varchar(4),
  `longDistanceCharges` varchar(4),
  `installation` varchar(4),
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY (`requestID`),
  UNIQUE KEY (`telephoneNumber`),
  KEY `employeeID` (`employeeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `voiceServiceBuilding` (
  `requestID` int(11) NOT NULL, 
  `buildingID` int(11) NOT NULL,
  PRIMARY KEY(`requestID`, `buildingID`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `internalPhoneListRegistry` (
  `employeeID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY(`requestID`),
  KEY `employeeID` (`employeeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `UBConlineDirectory` (
  `employeeID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY(`requestID`),
  KEY `employeeID` (`employeeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sauderWebsiteStaffDirectory` (
  `employeeID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY(`requestID`),
  KEY `employeeID` (`employeeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sauderStaffPhotoDirectory` (
  `employeeID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `imageName` varchar(128) NOT NULL,
  `image` blob NOT NULL,
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY(`requestID`),
  KEY `employeeID` (`employeeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
  
CREATE TABLE IF NOT EXISTS `building` (
  `buildingID` int(11) NOT NULL AUTO_INCREMENT,
  `building` varchar(128) NOT NULL,
  PRIMARY KEY(`buildingID`),
  UNIQUE KEY(`building`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `sauder`.`building` (`building`) VALUES ('David Lam'), ('Henry Angus'), ('Robson');

CREATE TABLE IF NOT EXISTS `locationAccess` (
  `employeeID` int(11) NOT NULL,
  `accessID` int(11) NOT NULL,
  `locationName` varchar(128) NOT NULL,
  `requestID` int(11) NOT NULL,
  `FOBNumber` int(11) NOT NULL,
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY (`requestID`, `locationName`),
  KEY `employeeID` (`employeeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `location` (
  `locationName` varchar(128) NOT NULL,
  PRIMARY KEY (`locationName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `sauder`.`location` (`locationName`) VALUES ('DL 229'), ('HA 447'), ('HA 441');

CREATE TABLE IF NOT EXISTS `mailSlot` (
  `mailSlotID` int(11) NOT NULL AUTO_INCREMENT,
  `mailSlotName` varchar(128) NOT NULL,
  PRIMARY KEY(`mailSlotID`),
  UNIQUE KEY(`mailSlotName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
  
INSERT INTO `sauder`.`mailSlot` (`mailSlotName`) VALUES ('A1'), ('A2'), ('A3'), ('A4'), ('A5'), ('B1'), ('B2'), ('B3'), ('B4'), ('B5'), ('C1'), ('C2'), ('C3'), ('C4'), ('C5'), ('D1'), ('D2'), ('D3'), ('D4'), ('D5');

CREATE TABLE IF NOT EXISTS `mailSlotAccessor` (
  `employeeID` int(11) NOT NULL,
  `mailSlotAccessID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY (`employeeID`, `mailSlotAccessID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mailSlotAccess` (
  `mailSlotAccessID` int(11) NOT NULL AUTO_INCREMENT,
  `mailSlotID` int(11),
  `shared` tinyint(1) NOT NULL,
  PRIMARY KEY (`mailSlotAccessID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `doorNamePlate` (
  `employeeID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `doorNamePlateText` varchar(128) NOT NULL,
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY(`requestID`),
  KEY `employeeID` (`employeeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sharedDriveAccess` (
  `sharedDriveID` int(11) NOT NULL,
  `employeeID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY (`requestID`, `sharedDriveID`),
  KEY (`employeeID`, `sharedDriveID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sharedDrive` (
  `sharedDriveID` int(11) NOT NULL AUTO_INCREMENT,
  `sharedDriveName` varchar(128) NOT NULL,
  PRIMARY KEY (`sharedDriveID`),
  UNIQUE KEY (`sharedDriveName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `sauder`.`sharedDrive` (`sharedDriveName`) VALUES ('sharedDrive A'), ('sharedDrive B'), ('sharedDrive C'), ('sharedDrive D'), ('sharedDrive E'); 

CREATE TABLE IF NOT EXISTS `groups` (
  `groupID` int(11) NOT NULL AUTO_INCREMENT,
  `groupName` varchar(128) NOT NULL,
  `shortName` varchar(16) NOT NULL,
  `type` enum('department', 'division') NOT NULL,
  PRIMARY KEY (`groupID`),
  UNIQUE KEY (`shortName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `sauder`.`groups` (`groupName`, `shortName`, `type`) VALUES ('All', 'ALL', 'department') ,('Administration', 'ADM', 'department'), ('Sauder IT', 'IT', 'department'), ('Human Resources', 'HRS', 'department'), ('Marketing', 'MRK','division'), ('Accounting', 'ACC', 'division');

CREATE TABLE IF NOT EXISTS `groupMembership` (
  `employeeID` int(11) NOT NULL,
  `groupID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY (`requestID`, `groupID`),
  KEY (`employeeID`, `groupID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `computer`(
  `employeeID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `status` enum('pending', 'done', 'remove') NOT NULL,
   PRIMARY KEY (`requestID`),
   KEY `employeeID` (`employeeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `printingAccess`(
  `employeeID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `speedChart` varchar(128) NOT NULL,
  `main` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY(`requestID`, `speedChart`),
  KEY `employeeID` (`employeeID`, `speedChart`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

/*MAYBE REMOVE UNIQUE KEY*/
CREATE TABLE IF NOT EXISTS `email`(
  `employeeID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `emailAddress` varchar(128) NOT NULL,
  `genericmailaccount` tinyint(1) DEFAULT '0',
  `editownbookingsaccess` tinyint(1) DEFAULT '0',
  `editallbookingsaccess` tinyint(1) DEFAULT '0',
  `readonlybookingsaccess` tinyint(1) DEFAULT '0',
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY (`requestID`),
  UNIQUE KEY (`emailAddress`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

/*WHAT IF YOU DELETE ONE - REFERENTIAL INTEGRITY WILL DELETE ALL*/
CREATE TABLE IF NOT EXISTS `emailList`(
  `emailListID` int(11) NOT NULL AUTO_INCREMENT,
  `emailListName` varchar(128) NOT NULL,
  PRIMARY KEY (`emailListID`),
  UNIQUE KEY (`emailListName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `emailList` (emailListName) VALUES ('mailingList1'), ('mailingList2'), ('mailingList3'), ('mailingList4'), ('mailingList5');

CREATE TABLE IF NOT EXISTS `emailGrouping`(
  `emailAddress` varchar(128) NOT NULL,
  `requestID` int(11) NOT NULL,
  `emailListID` int(11) NOT NULL,
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY (`requestID`, `emailListID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `siteCoreLogin`(
  `sectionName` varchar(128) NOT NULL,
  `employeeID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `userType` enum('student', 'staff') NOT NULL,
  `trainingRequired` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('pending', 'done', 'remove') NOT NULL,
  PRIMARY KEY (`requestID`),
  KEY `employeeID` (`employeeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `tenure`
  ADD CONSTRAINT `tenure_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tenure_ibfk_2` FOREIGN KEY (`locationName`) REFERENCES `location` (`locationName`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tenure_ibfk_3` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tenure_ibfk_4` FOREIGN KEY (`tenureStatus`) REFERENCES `tenureStatus` (`tenureStatus`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `locationAccess`
  ADD CONSTRAINT `locationAccess_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `locationAccess_ibfk_2` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `locationAccess_ibfk_3` FOREIGN KEY (`locationName`) REFERENCES `location` (`locationName`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `shareddriveaccess`
  ADD CONSTRAINT `shareddDriveAccess_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shareddDriveAccess_ibfk_2` FOREIGN KEY (`sharedDriveID`) REFERENCES `sharedDrive` (`sharedDriveID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shareddDriveAccess_ibfk_3` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `request`
  ADD CONSTRAINT `request_ibfk_1` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `groupMembership` 
  ADD CONSTRAINT `groupMembership_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `groupMembership_ibfk_2` FOREIGN KEY (`groupID`) REFERENCES `groups` (`groupID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `groupMembership_ibfk_3` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `mailSlotAccess`
  ADD CONSTRAINT `mailSlotAccess_ibfk_1` FOREIGN KEY (`mailSlotID`) REFERENCES `mailSlot` (`mailSlotID`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `mailSlotAccessor`
  ADD CONSTRAINT `mailAccessor_ibfk_1` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mailAccessor_ibfk_2` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mailAccessor_ibfk_3` FOREIGN KEY (`mailSlotAccessID`) REFERENCES `mailSlotAccess` (`mailSlotAccessID`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `doorNamePlate`
  ADD CONSTRAINT `doorNamePlate_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `doorNamePlate_ibfk_2` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `computer`
  ADD CONSTRAINT `computer_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `computer_ibfk_2` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `email`
  ADD CONSTRAINT `email_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `email_ibfk_2` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `emailGrouping`
  ADD CONSTRAINT `emailGrouping_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `emailGrouping_ibfk_2` FOREIGN KEY (`emailAddress`) REFERENCES `email` (`emailAddress`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `emailGrouping_ibfk_3` FOREIGN KEY (`emailListID`) REFERENCES `emailList` (`emailListID`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `siteCoreLogin`
  ADD CONSTRAINT `siteCoreLogin_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `siteCoreLogin_ibfk_2` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE; 

ALTER TABLE `voiceService`
  ADD CONSTRAINT `voiceService_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `voiceService_ibfk_2` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `voiceServiceBuilding`
  ADD CONSTRAINT `voiceServiceBulding_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `voiceService` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `voiceServiceBulding_ibfk_2` FOREIGN KEY (`buildingID`) REFERENCES `building` (`buildingID`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `printingAccess`
  ADD CONSTRAINT `printingAccess_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `printingAccess_ibfk_2` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE; 
  
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`groupID`) REFERENCES `groups` (`groupID`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `orientationPackage`
  ADD CONSTRAINT `orientationPackage_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orientationPackage_ibfk_2` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `businessCard`
  ADD CONSTRAINT `businessCard_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `businessCard_ibfk_2` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `businessCard_ibfk_3` FOREIGN KEY (`businessCardType`) REFERENCES `businessCardType` (`businessCardType`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `PATSystemAccount`
  ADD CONSTRAINT `PATSystemAccount_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `PATSystemAccount_ibfk_2` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE;
  
INSERT INTO `sauder`.`users` (`username`, `firstName`, `lastName`, `groupID`, `active`, `passwordHash`) VALUES 
('testuser', 'Tess', 'Hughes', '1', '1', '536e00d2f14fb818e9a905dd493cfa886604f2b4'),
('IT', 'Ian', 'Tee', '3', '1', '536e00d2f14fb818e9a905dd493cfa886604f2b4'),
('admin', 'Adam', 'Ministrel', '2', '1', '536e00d2f14fb818e9a905dd493cfa886604f2b4');

ALTER TABLE `internalPhoneListRegistry`
  ADD CONSTRAINT `internalPhoneListRegistry_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `internalPhoneListRegistry_ibfk_2` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `UBConlineDirectory`
  ADD CONSTRAINT `UBConlineDirectory_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `UBConlineDirectory_ibfk_2` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `sauderWebsiteStaffDirectory`
  ADD CONSTRAINT `sauderWebsiteStaffDirectory_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sauderWebsiteStaffDirectory_ibfk_2` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE;
  
ALTER TABLE `sauderStaffPhotoDirectory`
  ADD CONSTRAINT `sauderStaffPhotoDirectory_ibfk_1` FOREIGN KEY (`requestID`) REFERENCES `request` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sauderStaffPhotoDirectory_ibfk_2` FOREIGN KEY (`employeeID`) REFERENCES `employee` (`employeeID`) ON DELETE CASCADE ON UPDATE CASCADE;
  