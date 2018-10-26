
INSERT INTO `gen_lookups` (`Table_Name`, `Code`, `Description`, `Substitute`, `Type`, `Order`) VALUES ('Note_Category', 'ncr', 'Reservation', '', 'h', 0);
INSERT INTO `gen_lookups` (`Table_Name`, `Code`, `Description`, `Substitute`, `Type`, `Order`) VALUES ('Note_Category', 'nch', 'House', '', 'h', 0);
INSERT INTO `gen_lookups` (`Table_Name`, `Code`, `Description`, `Substitute`, `Type`, `Order`) VALUES ('Note_Category', 'ncf', 'PSG', '', 'h', 0);
INSERT INTO `gen_lookups` (`Table_Name`, `Code`, `Description`, `Substitute`, `Type`, `Order`) VALUES ('Note_Category', 'ncv', 'Visit', '', 'h', 0);
INSERT INTO `gen_lookups` (`Table_Name`, `Code`, `Description`, `Substitute`, `Type`, `Order`) VALUES ('Note_Category', 'ncg', 'Guest', '', 'h', 0);
INSERT INTO `gen_lookups` (`Table_Name`, `Code`, `Description`, `Substitute`, `Type`, `Order`) VALUES ('Note_Category', 'ncp', 'Patient', '', 'h', 0);
INSERT INTO `gen_lookups` (`Table_Name`, `Code`, `Description`, `Substitute`, `Type`, `Order`) VALUES ('Note_Category', 'ncrm', 'Room', '', 'h', 0);

DELETE FROM `gen_lookups` WHERE `Table_Name`='WL_Final_Status';
DELETE FROM `gen_lookups` WHERE `Table_Name`='WL_Status';

INSERT INTO `sys_config` (`Key`, `Value`, `Type`, `Category`, `Description`) VALUES ('PaymentGateway', '', 's', 'h', 'Payment Gateway, either vantiv, instamed or nothing.');
INSERT INTO `sys_config` (`Key`, `Value`, `Type`, `Category`, `Description`) VALUES ('CalRescColWidth', '8%', 's', 'h', 'The width of the rooms column on the calendar page as percent of the overall width.');

update sys_config set `Value` = '16', `Type` = 'i' where `Key` = 'CheckInTime';
update sys_config set `Value` = '10', `Type` = 'i' where `Key` = 'CheckOutTime';

update page set `File_Name` = 'Reserve.php' where `File_Name` = 'Referral.php';

-- Add pages, one call for each security group.
call new_webpage('ws_resv.php', 31, '', 0, 'h', '', '', 's', '', 'admin', now(), 'g', @pageId);
call new_webpage('ws_resv.php', 31, '', 0, 'h', '', '', 's', '', 'admin', now(), 'ga', @pageId);
call new_webpage('CheckingIn.php', 31, 'Checking In', 0, 'h', '', '', 'p', '', 'admin', now(), 'g', @pageId);
call new_webpage('CheckingIn.php', 31, 'Checking In', 0, 'h', '', '', 'p', '', 'admin', now(), 'ga', @pageId);


