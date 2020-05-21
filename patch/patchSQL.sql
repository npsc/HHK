
ALTER TABLE `w_groups`
 ADD COLUMN `IP_Restricted` BOOLEAN NOT NULL DEFAULT 0 AFTER `Cookie_Restricted`;


UPDATE `sys_config` SET `Category`='fg' WHERE `Key`='BatchSettlementHour';
UPDATE `sys_config` SET `Type`='lu', `GenLookup`='ExcessPays' WHERE `Key`='VisitExcessPaid';

INSERT INTO `sys_config` (`Key`, `Value`, `Type`, `Category`, `Header`, `Description`, `GenLookup`) VALUES('UseDocumentUpload', 'false', 'b', 'h', '', 'Enable Document Uploads', '');
INSERT INTO `sys_config` (`Key`, `Value`, `Type`, `Category`, `Description`) VALUES ('ExtendToday', '0', 'i', 'h', 'Extend immediate Check-in by this many hours into tomorrow');
INSERT INTO `sys_config` (`Key`, `Value`, `Type`, `Category`, `Description`) VALUES ('InsistCkinPayAmt', 'true', 'b', 'h', 'Insist on the user filling in the payment amount on checkin page');
INSERT INTO `sys_config` (`Key`, `Value`, `Type`, `Category`, `Description`) VALUES ('InsistCkinDemog', 'false', 'b', 'h', 'Insist that user fill in the demographics on the check in page (see ShowDemographics)');
INSERT INTO `sys_config` (`Key`, `Type`, `Category`, `Description`) VALUES ('NotificationAddress', 'ea', 'f', 'Gets financial notifications.');

REPLACE INTO `gen_lookups` (`Table_Name`, `Code`, `Description`, `Substitute`) VALUES ('Form_Upload', 'ra', 'Registration Agreement', 'Reg_Agreement');
REPLACE INTO `gen_lookups` (`Table_Name`, `Code`, `Description`, `Substitute`) VALUES ('Form_Upload', 'c', 'Confirmation', 'Resv_Conf');
REPLACE INTO `gen_lookups` (`Table_Name`, `Code`, `Description`, `Substitute`) VALUES ('Form_Upload', 's', 'Survey Form', 'Survy_Form');

INSERT INTO `gen_lookups` (`Table_Name`, `Code`, `Description`, `Type`, `Order`) VALUES ('Incident_Status', 'a', 'Active', 'h', '1');
INSERT INTO `gen_lookups` (`Table_Name`, `Code`, `Description`, `Type`, `Order`) VALUES ('Incident_Status', 'r', 'Resolved', 'h', '7');
INSERT INTO `gen_lookups` (`Table_Name`, `Code`, `Description`, `Type`, `Order`) VALUES ('Incident_Status', 'd', 'Deleted', 'h', '10');
INSERT INTO `gen_lookups` (`Table_Name`, `Code`, `Description`, `Type`, `Order`) VALUES ('Incident_Status', 'h', 'On Hold', 'h', '4');

UPDATE `gen_lookups` SET `Type`='u' WHERE `Table_Name`='Room_Status';


ALTER TABLE `note`
ADD COLUMN `flag` BOOL default false AFTER `Note_Type`;

INSERT INTO `template_tag` VALUES (6,'c','Guest Name','${GuestName}','');
INSERT INTO `template_tag` VALUES (7,'c','Expected Arrival','${ExpectedArrival}','');
INSERT INTO `template_tag` VALUES (8,'c','Expected Departure','${ExpectedDeparture}','');
INSERT INTO `template_tag` VALUES (9,'c','Date Today','${DateToday}','');
INSERT INTO `template_tag` VALUES (10,'c','Nights','${Nites}','');
INSERT INTO `template_tag` VALUES (11,'c','Amount','${Amount}','');
INSERT INTO `template_tag` VALUES (12,'c','Notes','${Notes}','');
INSERT INTO `template_tag` VALUES (13,'c','Visit Fee Notice','${VisitFeeNotice}','');
INSERT INTO `template_tag` VALUES (14,'s','First Name','${FirstName}','');
INSERT INTO `template_tag` VALUES (15,'s','Last Name','${LastName}','');
INSERT INTO `template_tag` VALUES (16,'s','Name Suffix','${NameSuffix}','');
INSERT INTO `template_tag` VALUES (17,'s','Name Prefix','${NamePrefix}','');

ALTER TABLE `document` 
	CHANGE COLUMN `Doc` `Doc` MEDIUMBLOB NULL DEFAULT NULL ;


-- Update gen_lookups Pay_Types to index paymentId 2 instead of 4
Update `gen_lookups` set `Substitute` = '2' where `Table_Name` = 'Pay_Type' and `Code` = 'cc';

DELETE FROM `sys_config` WHERE `Key`='PmtPageLogoUrl';
DELETE FROM `sys_config` WHERE `Key`='CardSwipe';

ALTER TABLE `name_demog`
 	CHANGE COLUMN `Ethnicity` `Ethnicity` VARCHAR(5) NOT NULL DEFAULT '' ;
ALTER TABLE `name_demog`
 	ADD COLUMN `Gl_Code_Debit` VARCHAR(25) NOT NULL DEFAULT '' AFTER `Special_Needs`;
ALTER TABLE `name_demog`
 	ADD COLUMN `Gl_Code_Credit` VARCHAR(25) NOT NULL DEFAULT '' AFTER `Gl_Code_Debit`;



 