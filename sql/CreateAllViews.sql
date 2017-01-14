-- @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
-- @copyright 2010-2017 <nonprofitsoftwarecorp.org>
-- @license   MIT
-- @link      https://github.com/NPSC/HHK

-- Must create vmember_listing first as it is used by most of the rest of hte views
-- -----------------------------------------------------
-- View `vmember_listing`
-- -----------------------------------------------------
CREATE or replace VIEW `vmember_listing` AS
select `n`.`idName` AS `Id`,
`n`.`Name_Full` AS `Fullname`,
(case when (`n`.`Record_Company` = 0) then `n`.`Name_Last` else `n`.`Company` end) AS `Name_Last`,
`n`.`Name_First` AS `Name_First`,
`n`.`Name_Middle` AS `Name_Middle`,
`n`.`Name_Nickname` AS `Name_Nickname`,
ifnull(`g1`.`Description`,'') AS `Name_Prefix`,
ifnull(`g2`.`Description`,'') AS `Name_Suffix`,
(case when (`n`.`Exclude_Phone` = 1) then '' else (case when (ifnull(`np`.`Phone_Extension`,'') = '')
  then ifnull(`np`.`Phone_Num`,'') else concat_ws('x',`np`.`Phone_Num`,`np`.`Phone_Extension`) end) end) AS `Preferred_Phone`,
(case when (`n`.`Exclude_Mail` = 1) then '' else ifnull(`na`.`Address_1`,'') end) AS `Address_1`,
(case when (`n`.`Exclude_Mail` = 1) then '' else ifnull(`na`.`Address_2`,'') end) AS `Address_2`,
(case when (`n`.`Exclude_Mail` = 1) then '' else ifnull(`na`.`City`,'') end) AS `City`,
(case when (`n`.`Exclude_Mail` = 1) then '' else ifnull(`na`.`State_Province`,'') end) AS `StateProvince`,
(case when (`n`.`Exclude_Mail` = 1) then '' else ifnull(`na`.`Postal_Code`,'') end) AS `PostalCode`,
(case when (`n`.`Exclude_Mail` = 1) then '' else ifnull(`na`.`Country`,'') end) AS `Country`,
(case when (`n`.`Exclude_Mail` = 1) then '' else ifnull(`na`.`Country_Code`,'') end) AS `Country_Code`,
ifnull(`na`.`Bad_Address`,'') AS `Bad_Address`,
(case when (`n`.`Exclude_Email` = 1) then '' else ifnull(`ne`.`Email`,'') end) AS `Preferred_Email`,
`n`.`Member_Status` AS `MemberStatus`,
`n`.`Gender` AS `Gender`,
`n`.`Birth_Month` AS `Birth_Month`,
`n`.`Member_Since` AS `Member_Since`,
(case when (ifnull(`r`.`idName`,0) = `n`.`idName`) then `r`.`Target_Id` when (ifnull(`r`.`Target_Id`,0) = `n`.`idName`)
then `r`.`idName` else 0 end) AS `SpouseId`,
case when `n`.`Record_Member` = 1 then '1' else '0' end AS `MemberRecord`,
`n`.`External_Id` AS `External_Id`,
`n`.`Company_Id` AS `Company_Id`,
`n`.`Company` AS `Company`,
case when `n`.`Exclude_Mail` = 1 then '1' else '0' end AS `Exclude_Mail`,
case when `n`.`Exclude_Email` = 1 then '1' else '0' end AS `Exclude_Email`,
case when `n`.`Exclude_Phone` = 1 then '1' else '0' end AS `Exclude_Phone`,
case when `n`.`Exclude_Directory` = 1 then '1' else '0' end AS `Exclude_Directory`,
`n`.`Last_Updated` AS `Last_Updated`,
`n`.`Member_Type` AS `Member_Type`,
`n`.`Title` AS `Title`,
`n`.`Company_CareOf` AS `Company_CareOf`,
ifnull(`n`.`Preferred_Mail_Address`,'') AS `Address_Code`,
ifnull(`n`.`Preferred_Email`,'') AS `Email_Code`,
ifnull(`n`.`Preferred_Phone`,'') AS `Phone_Code`
from ((((((`name` `n` left join `name_address` `na` on(((`n`.`idName` = `na`.`idName`)
  and (`n`.`Preferred_Mail_Address` = `na`.`Purpose`))))
left join `name_email` `ne` on(((`n`.`idName` = `ne`.`idName`) and (`n`.`Preferred_Email` = `ne`.`Purpose`))))
left join `name_phone` `np` on(((`n`.`idName` = `np`.`idName`) and (`n`.`Preferred_Phone` = `np`.`Phone_Code`))))
left join `gen_lookups` `g1` on(((`n`.`Name_Prefix` = `g1`.`Code`) and (`g1`.`Table_Name` = 'Name_Prefix'))))
left join `gen_lookups` `g2` on(((`n`.`Name_Suffix` = `g2`.`Code`) and (`g2`.`Table_Name` = 'Name_Suffix'))))
left join `relationship` `r` on(((`r`.`Relation_Type` = 'sp') and (`r`.`Status` = 'a')
  and ((`n`.`idName` = `r`.`idName`) or (`n`.`idName` = `r`.`Target_Id`)))))
where ((`n`.`idName` > 0) and (`n`.`Member_Status` in ('a','d','in')));




-- -----------------------------------------------------
-- View `vmember_listing_blackout`
-- -----------------------------------------------------
CREATE or replace VIEW `vmember_listing_blackout` AS
select vm.*, ifnull(v.idVisit,0) as idVisit, max(ifnull(v.Span_End, now() - INTERVAL 90 DAY)) as spanEnd
from vmember_listing vm
left join name_guest ng on vm.Id = ng.idName
left join registration r on ng.idPsg = r.idPsg
left join visit v on r.idRegistration = v.idRegistration and v.Status in ('co', 'a')
group by vm.Id;




-- -----------------------------------------------------
-- View `vmember_listing_noex`
-- -----------------------------------------------------
CREATE or replace VIEW `vmember_listing_noex` AS
select `n`.`idName` AS `Id`,
`n`.`Name_Full` AS `Fullname`,
(case when (`n`.`Record_Company` = 0) then `n`.`Name_Last` else `n`.`Company` end) AS `Name_Last`,
`n`.`Name_First` AS `Name_First`,
`n`.`Name_Middle` AS `Name_Middle`,
`n`.`Name_Nickname` AS `Name_Nickname`,
ifnull(`g1`.`Description`,'') AS `Name_Prefix`,
ifnull(`g2`.`Description`,'') AS `Name_Suffix`,
(case when (ifnull(`np`.`Phone_Extension`,'') = '')
  then ifnull(`np`.`Phone_Num`,'') else concat_ws('x',`np`.`Phone_Num`,`np`.`Phone_Extension`) end) AS `Preferred_Phone`,
(case when (`n`.`Exclude_Mail` = 1 && n.Company_CareOf <> 'y') then '' else ifnull(`na`.`Address_1`,'') end) AS `Address_1`,
(case when (`n`.`Exclude_Mail` = 1 && n.Company_CareOf <> 'y') then '' else ifnull(`na`.`Address_2`,'') end) AS `Address_2`,
(case when (`n`.`Exclude_Mail` = 1 && n.Company_CareOf <> 'y') then '' else ifnull(`na`.`City`,'') end) AS `City`,
(case when (`n`.`Exclude_Mail` = 1 && n.Company_CareOf <> 'y') then '' else ifnull(`na`.`State_Province`,'') end) AS `StateProvince`,
(case when (`n`.`Exclude_Mail` = 1 && n.Company_CareOf <> 'y') then '' else ifnull(`na`.`Postal_Code`,'') end) AS `PostalCode`,
(case when (`n`.`Exclude_Mail` = 1 && n.Company_CareOf <> 'y') then '' else ifnull(`na`.`Country`,'') end) AS `Country`,
(case when (`n`.`Exclude_Mail` = 1 && n.Company_CareOf <> 'y') then '' else ifnull(`na`.`Country_Code`,'') end) AS `Country_Code`,
ifnull(`na`.`Bad_Address`,'') AS `Bad_Address`,
ifnull(`ne`.`Email`,'') AS `Preferred_Email`,
`n`.`Member_Status` AS `MemberStatus`,
`n`.`Gender` AS `Gender`,
`n`.`Birth_Month` AS `Birth_Month`,
`n`.`Member_Since` AS `Member_Since`,
(case when (ifnull(`r`.`idName`,0) = `n`.`idName`) then `r`.`Target_Id` when (ifnull(`r`.`Target_Id`,0) = `n`.`idName`)
then `r`.`idName` else 0 end) AS `SpouseId`,
case when `n`.`Record_Member` = 1 then '1' else '0' end AS `MemberRecord`,
`n`.`External_Id` AS `External_Id`,
`n`.`Company_Id` AS `Company_Id`,
`n`.`Company` AS `Company`,
case when `n`.`Exclude_Mail` = 1 then '1' else '0' end AS `Exclude_Mail`,
case when `n`.`Exclude_Email` = 1 then '1' else '0' end AS `Exclude_Email`,
case when `n`.`Exclude_Phone` = 1 then '1' else '0' end AS `Exclude_Phone`,
case when `n`.`Exclude_Directory` = 1 then '1' else '0' end AS `Exclude_Directory`,
`n`.`Last_Updated` AS `Last_Updated`,
`n`.`Member_Type` AS `Member_Type`,
`n`.`Title` AS `Title`,
`n`.`Company_CareOf` AS `Company_CareOf`,
ifnull(`n`.`Preferred_Mail_Address`,'') AS `Address_Code`,
ifnull(`n`.`Preferred_Email`,'') AS `Email_Code`,
ifnull(`n`.`Preferred_Phone`,'') AS `Phone_Code`
from `name` `n` left join `name_address` `na` on `n`.`idName` = `na`.`idName`
  and `n`.`Preferred_Mail_Address` = `na`.`Purpose`
left join `name_email` `ne` on `n`.`idName` = `ne`.`idName` and `n`.`Preferred_Email` = `ne`.`Purpose`
left join `name_phone` `np` on `n`.`idName` = `np`.`idName` and `n`.`Preferred_Phone` = `np`.`Phone_Code`
left join `gen_lookups` `g1` on `n`.`Name_Prefix` = `g1`.`Code` and `g1`.`Table_Name` = 'Name_Prefix'
left join `gen_lookups` `g2` on `n`.`Name_Suffix` = `g2`.`Code` and `g2`.`Table_Name` = 'Name_Suffix'
left join `relationship` `r` on `r`.`Relation_Type` = 'sp' and `r`.`Status` = 'a'
  and (`n`.`idName` = `r`.`idName` or `n`.`idName` = `r`.`Target_Id`)
where `n`.`idName` > 0 and `n`.`Member_Status` in ('a','d','in');




-- -----------------------------------------------------
-- View `vadditional_guests`
-- -----------------------------------------------------
create or replace view `vadditional_guests` as
    select 
        s.idVisit,
        s.idName,
        s.`Status`,
        n.`Name_First`,
        n.`Name_Last`,
	r.Title,
        ifnull(g2.`Description`, '') as `Age`,
        s.Checkin_Date,
        s.Expected_Co_Date,
        ng.Relationship_Code,
        ifnull(g1.Description, '') as `Relationship`
    from
        `stays` s	
            left join
        name_guest ng ON s.idName = ng.idName
            left join
        `name` n ON s.idName = n.idName
            left join
        `name_demog` nd ON s.idName = nd.idName
	    left join 
	`room` r on s.idRoom = r.idRoom
            left join
        `gen_lookups` g1 ON g1.Table_Name = 'Patient_Rel_Type'
            and g1.Code = ng.Relationship_Code
            left join
        `gen_lookups` g2 ON g2.Table_Name = 'Age_Bracket'
            and g2.Code = nd.Age_Bracket;





-- -----------------------------------------------------
-- View `vadmin_history_records`
-- -----------------------------------------------------
CREATE or replace VIEW `vadmin_history_records` AS
select
    m.Id AS Id,
    (case
        when (m.MemberRecord = 1) then m.Fullname
        else m.Company
    end) AS Fullname,
    m.Preferred_Phone AS Preferred_Phone,
    m.Preferred_Email AS Preferred_Email,
    (case
        when (m.Bad_Address = 'true') then '*(Bad Address)*'
        else m.Address_1
    end) AS Address_1,
    (case
        when (m.Bad_Address = 'true') then ''
        else m.Address_2
    end) AS Address_2,
    (case
        when (m.Bad_Address = 'true') then ''
        else m.City
    end) AS City,
    (case
        when (m.Bad_Address = 'true') then ''
        else m.StateProvince
    end) AS StateProvince,
    (case
        when (m.Bad_Address = 'true') then ''
        else m.Country_Code
    end) AS Country_Code,
    (case
        when (m.Bad_Address = 'true') then ''
        else m.PostalCode
    end) AS PostalCode,
    (case
        when (m.MemberRecord = 1) then m.Company
        else ''
    end) AS Company
from
    (member_history g
    join vmember_listing m ON ((g.idName = m.Id)))
    order by g.Admin_Access_Date desc limit 12;



-- -----------------------------------------------------
-- View `vaudit_log`
-- -----------------------------------------------------
CREATE  OR REPLACE VIEW `vaudit_log` AS
(SELECT
    `Timestamp` as LogDate,
    Log_Type as LogType,
    Sub_Type as Subtype,
    WP_User_Id as User,
    idName,
    Log_Text as LogText
FROM
    name_log
WHERE
    1 = 1)

union

(select
    `a`.`Effective_Date` as LogDate,
    'Volunteer' as LogType,
    a.Action_Codes as Subtype,
    a.Source_Code as User,
    a.idName,
    ifnull(concat(g2.Description,
                    ' - ',
                    g.Description,
                    ', Rank = ',
                    ifnull(g3.Description, '')),
            '(category Deleted)') as LogText
from
    activity a
        left join
    gen_lookups g ON substring_index(Product_Code, '|', 1) = g.Table_Name and substring_index(Product_Code, '|', - 1) = g.Code
        left join
    gen_lookups g2 ON g2.Table_Name = 'Vol_Category' and substring_index(Product_Code, '|', 1) = g2.Code
        left join
    gen_lookups g3 ON g3.Table_Name = 'Vol_Rank' and g3.Code = a.Other_Code
where
    a.Type = 'vol');



-- -----------------------------------------------------
-- View `vcamp_vol`
-- -----------------------------------------------------
CREATE or REPLACE VIEW `vcamp_vol` AS
select
    n.idName,
    n.Member_Status,
    n.Record_Member,
    ifnull(nv.Vol_Category,'') as Vol_Category,
    ifnull(`nv`.`Vol_Code`, '') AS `Vol_Code`,
    '' as Campaign_Code
from
    name n
    left join name_volunteer2 nv ON n.idName = nv.idName and nv.Vol_Status = 'a'
where
    n.Member_Status in ('a', 'in')

union
select distinct
    n.idName,
    n.Member_Status,
    n.Record_Member,
    '' as Vol_Category,
    '' as Vol_Code,
    ifnull(d.Campaign_Code,'') as Campaign_Code
from
    name n
    join donations d on (n.idName = d.Donor_Id or n.idName = d.Assoc_Id) and d.Status = 'a'
where n.Member_Status in ('a', 'in');


-- vcategory_listing must be before vcategory_events
-- -----------------------------------------------------
-- View `vcategory_listing`
-- -----------------------------------------------------
CREATE or replace VIEW `vcategory_listing` AS
select
    g.Table_Name as `Vol_Category`,
    g.Code as `Vol_Code`,
    gc.Description as `Vol_Category_Title`,
    g.Description as `Vol_Code_Title`,
    g.Substitute as `Colors`,
    ifnull(g3.Description, 'n') as `Show_Email_Delete`,
    ifnull(g4.Description, 'y') as `Hide_Add_Members`,
    ifnull(g5.Description, 'n') as `Show_AllCategory`,
    ifnull(g2.Description, 'n') as `Cal_House`
from
    gen_lookups g
        join
    gen_lookups gc ON g.Table_Name=gc.Code and gc.Table_Name = 'Vol_Category'
        left join
    gen_lookups g3 ON g3.Table_Name = 'Cal_Show_Delete_Email' and g3.Code = concat(g.Table_Name, g.Code)
        left join
    gen_lookups g4 ON g4.Table_Name = 'Cal_Hide_Add_Members' and g4.Code = concat(g.Table_Name, g.Code)
        left join
    gen_lookups g5 ON g5.Table_Name = 'Cal_Show_AllCategory' and g5.Code = concat(g.Table_Name, g.Code)
        left join
    gen_lookups g2 on g2.Table_Name = 'Cal_House' and g2.Code = concat(g.Table_Name, g.Code);



-- -----------------------------------------------------
-- View `vcategory_events`
-- -----------------------------------------------------
CREATE OR replace VIEW `vcategory_events` AS
select
    m.idmcalendar AS idmcalendar,
    m.idName AS idName,
    m.idName2 AS idName2,
    m.idName as idVolunteer,
    m.E_Title AS E_Title,
    m.E_Start AS E_Start,
    m.E_End AS E_End,
    m.E_URL AS E_URL,
    m.E_ClassName AS E_ClassName,
    m.E_Editable AS E_Editable,
    m.E_Description AS E_Description,
    m.E_AllDay AS E_AllDay,
    m.E_Vol_Category AS E_Vol_Category,
    m.E_Vol_Code AS E_Vol_Code,
    m.E_Status AS E_Status,
    m.E_Take_Overable AS E_Take_Overable,
    m.E_Fixed_In_Time AS E_Fixed_In_Time,
    m.E_Shell AS E_Shell,
    m.E_Locked AS E_Locked,
    m.E_Shell_Id AS E_Shell_Id,
    m.E_Rpt_Id AS E_Rpt_Id,
    m.E_Show_All AS E_Show_All,
    ifnull(v.Name_First, '') AS First,
    ifnull(v.Name_Last, '') AS Last,
    ifnull(v2.Name_First, '') AS First2,
    ifnull(v2.Name_Last, '') AS Last2,
    c.Vol_Code_Title AS Vol_Description,
    c.Show_Email_Delete AS Show_Email_Delete,
    c.Hide_Add_Members AS Hide_Add_Members,
    c.Show_AllCategory AS Show_AllCategory,
    c.Cal_House
from
    mcalendar m
    left join name v ON m.idName = v.idName and v.Member_Status = 'a'
    left join name v2 ON m.idName2 = v2.idName and v2.Member_Status = 'a'
    left join vcategory_listing c ON c.Vol_Category = m.E_Vol_Category and c.Vol_Code = m.E_Vol_Code;



-- -----------------------------------------------------
-- View `vcleaning_log`
-- -----------------------------------------------------
CREATE OR REPLACE VIEW `vcleaning_log` AS
    SELECT 
        rr.idResource,
        c.`idRoom`,
        r.`Title`,
        r.`Cleaning_Cycle_Code`,
        c.`Type`,
        c.`Status`,
        c.`Last_Cleaned`,
        c.`Notes`,
        g.`Description` AS `Status_Text`,
        c.`Username`,
        c.`Timestamp`
    FROM
        cleaning_log c
            LEFT JOIN
        room r ON c.idRoom = r.idRoom
            LEFT JOIN
        resource_room rr ON c.idRoom = rr.idRoom
            LEFT JOIN
        gen_lookups g ON g.Table_Name = 'Room_Status'
            AND g.Code = c.`Status`;


-- -----------------------------------------------------
-- View `vcredit_payments`
-- -----------------------------------------------------
CREATE or replace VIEW `vcredit_payments` AS
SELECT 
    pa.idPayment_auth,
    pa.idPayment,
    pa.Approved_Amount,
    pa.Approval_Code,
    pa.Invoice_Number,
    pa.Acct_Number,
    pa.Card_Type,
    pa.AVS,
    pa.Code3 as `CVV`,
    pa.Last_Updated,
    p.idPayor,
    gt.CardHolderName
FROM
    payment p
        left join
    payment_auth pa ON p.idPayment = pa.idPayment
        left join
    guest_token gt ON p.idToken = gt.idGuest_token
where
    p.idPayment_Method = 2
        and p.Status_Code = 's';



-- -----------------------------------------------------
-- View `vcurrent_residents`
-- -----------------------------------------------------
CREATE or replace VIEW `vcurrent_residents` AS
select
    s.idName AS `Id`,
    concat(
        (case when ifnull(m.Name_Nickname,'') = '' 
            then ifnull(m.Name_First,'') 
            else ifnull(m.Name_Nickname,'') end),
         ' ', 
        ifnull(
            (case when m.Name_Suffix = '' then m.Name_Last else concat(m.Name_Last, ' ', m.Name_Suffix) end)
            ,'')) AS `Guest`,
    ifnull(m.Preferred_Phone,'') AS `Phone`,
    case when v.Ext_Phone_Installed = 1 then 'Y' else '' end as `Use House Phone`,
    r.Phone as `Room Phone`,
    ifnull(s.`Status`,'') as `Stay_Status`,
    ifnull(s.On_Leave, 0) as `On_Leave`,
    ifnull(s.Checkin_Date,'') AS `Checked-In`,
    ifnull(s.Expected_Co_Date,'') as `Expected Depart`,
    ifnull(s.Span_Start_Date, '') as `Span_Start_Date`,
    ifnull(r.`Title`,'') AS `Room`,
    ifnull(r.`Status`, '') as `Room_Status`,
    ifnull(r.idRoom, 0) as `RoomId`,
    ifnull(r.Cleaning_Cycle_Code, '') as `Cleaning_Cycle_Code`,
    concat((case when ifnull(mp.Name_Nickname,'') = '' then ifnull(mp.Name_First,'') else ifnull(mp.Name_Nickname,'') end), ' ', ifnull((case when mp.Name_Suffix = '' then mp.Name_Last else concat(mp.Name_Last, ' ', mp.Name_Suffix) end),'')) AS `Patient`,
    'hhk-curres' as `Action`,
    ifnull(re.Background_Color, 'white') as backColor,
    ifnull(re.Text_Color, 'black') as textColor,
    v.idVisit,
    v.Span,
    v.idRegistration,
    v.idReservation,
    v.idRoom_rate,
    v.Pledged_Rate,
    v.Expected_Rate,
    v.Rate_Category,
    ifnull(hs.idPsg,0) as `idPsg`,
    ifnull(hs.idAssociation,0) as idAssociation,
    ifnull(hs.idHospital,0) as idHospital
from
    stays s left join visit v ON s.idVisit = v.idVisit and s.Visit_Span = v.Span
    left join vmember_listing m ON s.idName = m.Id
    left join room r ON s.idRoom = r.idRoom
    left join hospital_stay hs on v.idHospital_stay = hs.idHospital_stay
    left join `vmember_listing` mp ON hs.idPatient = mp.Id
    left join resource re on re.idResource = v.idResource
where
    s.`Status` = 'a'
order by v.idVisit;



-- -----------------------------------------------------
-- View `vdonation_view`
-- -----------------------------------------------------
CREATE or replace VIEW `vdonation_view` AS
select `d`.`iddonations` AS `iddonations`,
`d`.`Donor_Id` AS `Donor_Id`,
`d`.`Amount` AS `Amount`,
ifnull(`c`.`Title`,'') AS `Campaign_Code`,
`d`.`Date_Entered` AS `Date_Entered`,
`d`.`Member_type` AS `Member_Type`,
`d`.`Care_Of_Id` AS `Care_Of_Id`,
`d`.`Assoc_Id` AS `Assoc_Id`,
`d`.`Date_Acknowledged` AS `Date_Acknowledged`,
case when `n`.`Record_Member` = 1 then '1' else '0' end AS `Record_Member`,
ifnull(`ns`.`Name_Last`,'') AS `Name_Last`,
ifnull(`ns`.`Name_First`,'') AS `Name_First`,
case when n.Record_Member = 1 then n.Name_Full else n.Company end as Donor_Name,
ifnull(c.Campaign_Type, '') as Campaign_Type,
d.Fund_Code
from (((`donations` `d` left join `campaign` `c` on((lcase(trim(`d`.`Campaign_Code`)) = lcase(trim(`c`.`Campaign_Code`)))))
left join `name` `n` on((`d`.`Donor_Id` = `n`.`idName`)))
left join `name` `ns` on((`d`.`Care_Of_Id` = `ns`.`idName`)))
where (`d`.`Status` = 'a');



-- -----------------------------------------------------
-- View `vdump_address`
-- -----------------------------------------------------
CREATE or replace VIEW `vdump_address` AS
select `a`.`idName` AS `Id`,
`g`.`Description` AS `Purpose`,
`a`.`Address_1` AS `Address 1`,
`a`.`Address_2` AS `Address 2`,
`a`.`City` AS `City`,
`a`.`State_Province` AS `State`,
`a`.`Postal_Code` AS `Zip`,
`a`.`Country` AS `Country`,
`a`.`Status` AS `Status`,
`a`.`Bad_Address` AS `Bad Address`,
`a`.`Last_Updated` AS `Last_Updated`,
`a`.`Timestamp` AS `Created On`
from (`name_address` `a` left join `gen_lookups` `g` on(((`a`.`Purpose` = `g`.`Code`) and (`g`.`Table_Name` = 'Address_Purpose'))))
where ((`a`.`City` <> '') or (`a`.`Address_1` <> '')) order by `a`.`idName`, a.Purpose;



-- -----------------------------------------------------
-- View `vdump_badaddress`
-- -----------------------------------------------------
CREATE or replace VIEW `vdump_badaddress` AS
select
    n.idName AS Id,
    (case
        when (n.Record_Company = 0) then n.Name_Last
        else n.Company
    end) AS `Last Name`,
    n.Name_First AS First,
    g.Description AS `Member Status`,
    ga.Description AS Purpose,
    (case
        when (n.Preferred_Mail_Address = na.Purpose) then 'Prefd'
        else ''
    end) AS Pref,
    (case
        when (na.Bad_Address <> '') then 'Bad Addr'
        else ''
    end) AS `Bad Addr`,
    (case
        when
            (ifnull(na.Address_2, '') <> '')
        then
            concat(ifnull(na.Address_1, ''),
                    ', ',
                    ifnull(na.Address_2, ''))
        else ifnull(na.Address_1, '')
    end) AS `Street Address`,
    ifnull(na.City, '') AS City,
    ifnull(na.State_Province, '') AS State,
    ifnull(na.Postal_Code, '') AS Zip,
    n.Member_Status AS `Member|Status`
from
    (((name_address na
    left join name n ON ((n.idName = na.idName)))
    left join gen_lookups g ON (((n.Member_Status = g.Code) and (g.Table_Name = 'mem_status'))))
    left join gen_lookups ga ON (((na.Purpose = ga.Code) and (ga.Table_Name = 'Address_Purpose'))))
where
    ((n.idName > 0)
        and (n.Member_Status in ('a','d', 'in'))
        and (
            (na.Bad_Address <> '')
            or (trim(na.Address_1) = '')
            or (trim(na.City) = '')
            or (trim(na.State_Province) = '')
            or (length(na.State_Province) > 2)
            or (length(trim(na.Postal_Code)) < 5)
        )
    )
order by (case
    when (n.Record_Company = 0) then n.Name_Last
    else n.Company
end);



-- -----------------------------------------------------
-- View `vdump_campaigns`
-- -----------------------------------------------------
CREATE  OR REPLACE VIEW `vdump_campaigns` AS
select c.Title,
c.Description,
c.Campaign_Code as `Code`,
gstat.Description as `Status`,
c.Start_Date as `Start`,
c.End_Date as `End`,
case when c.Target = 0 then '' else c.Target end as `Target`,
case when c.Min_Donation = 0 then '' else c.Min_Donation end as `Min`,
case when c.Max_Donation = 0 then '' else c.Max_Donation end as `Max`,
gtype.Description as `Type`,
case when c.Campaign_Type = 'pct' then c.Percent_Cut else '' end as `% Cut`,
c.Campaign_Merge_Code as `Merge Code`,
c.Category,
c.Updated_By as `Updated By`,
c.Last_Updated as `Last Updated`,
c.Timestamp
from campaign c left join gen_lookups gstat on gstat.Table_Name = 'Campaign_Status' and c.Status = gstat.Code
left join gen_lookups gtype on gtype.Table_Name = 'Campaign_Type' and gtype.Code = c.Campaign_Type;




-- -----------------------------------------------------
-- View `vdump_donations`
-- -----------------------------------------------------
CREATE or replace VIEW `vdump_donations` AS
select `d`.`Donor_Id` AS `Id`,
`d`.`Care_Of_Id` AS `Sponsor Id`,
`d`.`Assoc_Id` AS `Assoc Id`,
`d`.`Date_Entered` AS `Date`,
`d`.`Amount` AS `Amount`,
ifnull(`gpt`.`Description`,'') AS `Pay Type`,
ifnull(`gcod`.`Description`,'') AS `Salutation Code`,
ifnull(`genv`.`Description`,'') AS `Envelope Code`,
ifnull(c.Title, '') as Campaign,
`d`.`Updated_By` AS `Updated By`,
`d`.`Last_Updated` AS `Last_Updated`,
`d`.`Timestamp` AS `Created On`
from `donations` `d` left join `gen_lookups` `gpt` on `d`.`Pay_Type` = `gpt`.`Code` and `gpt`.`Table_Name` = 'Pay_Type'
left join `gen_lookups` `gcod` on `d`.`Salutation_Code` = `gcod`.`Code` and `gcod`.`Table_Name` = 'salutation'
left join `gen_lookups` `genv` on `d`.`Envelope_Code` = `genv`.`Code` and `genv`.`Table_Name` = 'salutation'
left join campaign c on d.Campaign_Code = c.Campaign_Code
where d.Status = 'a'
order by `d`.`Donor_Id`;


-- -----------------------------------------------------
-- View `vdump_email`
-- -----------------------------------------------------
CREATE or replace VIEW `vdump_email` AS
select `e`.`idName` AS `Id`,
`gc`.`Description` AS `Purpose`,
`e`.`Email` AS `Email`,
`e`.`Last_Updated` AS `Last_Updated`,
`e`.`Updated_By` AS `Updated By`,
`e`.`Timestamp` AS `Created On`
from (`name_email` `e` left join `gen_lookups` `gc` on(((`e`.`Purpose` = `gc`.`Code`) and (`gc`.`Table_Name` = 'Email_Purpose'))))
where (`e`.`idName` > 0) order by `e`.`idName`;



-- -----------------------------------------------------
-- View `vdump_events`
-- -----------------------------------------------------
CREATE or replace VIEW `vdump_events` AS
select `m`.`idName` AS `Id`,
`m`.`E_Title` AS `Title`,
`m`.`E_Start` AS `Start Time`,
`m`.`E_End` AS `End Time`,
`m`.`E_Description` AS `Description`,
ifnull(`gcat`.`Description`,'') AS `Vol Category`,
ifnull(`gcod`.`Description`,'') AS `Vol Code`,
`m`.`E_Status` AS `Status`,
`m`.`Updated_By` AS `Updated By`,
`m`.`Last_Updated` AS `Last_Updated`,
`m`.`Timestamp` AS `Created On`
from ((`mcalendar` `m` left join `gen_lookups` `gcat` on(((`m`.`E_Vol_Category` = `gcat`.`Code`) and (`gcat`.`Table_Name` = 'Vol_Category'))))
left join `gen_lookups` `gcod` on(((`m`.`E_Vol_Code` = `gcod`.`Code`) and (`gcod`.`Table_Name` = `m`.`E_Vol_Category`))))
where (`m`.`idName` > 0) order by `m`.`idName`;



-- -----------------------------------------------------
-- View `vdump_name`
-- -----------------------------------------------------
CREATE or replace VIEW `vdump_name` AS
select `n`.`idName` AS `Id`,
`gt`.`Description` AS `Member Basis`,
(case when (`n`.`Record_Member` = 1) then `n`.`Name_Full` else `n`.`Company` end) AS `Name / Organization`,
`n`.`Name_Nickname` AS `Nickname`,
`n`.`Name_Previous` AS `Previous Name`,
(case when (`n`.`Record_Member` = 1) then `n`.`Company` else '' end) AS `Company`,
n.Company_CareOf as `C/O`,
`n`.`Title` AS `Title`,
`n`.`Web_Site` AS `Web Site`,
`gs`.`Description` AS `Member Status`,
ifnull(`gpm`.`Description`,'') AS `Preferred Address`,
ifnull(`gpe`.`Description`,'') AS `Preferred Email`,
ifnull(`gpp`.`Description`,'') AS `Preferred Phone`,
(case when (`n`.`Exclude_Directory` = 1) then 'x' else '' end) AS `Excl Directory`,
(case when (`n`.`Exclude_Mail` = 1) then 'x' else '' end) AS `Excl Address`,
(case when (`n`.`Exclude_Email` = 1) then 'x' else '' end) AS `Excl Email`,
(case when (`n`.`Exclude_Phone` = 1) then 'x' else '' end) AS `Excl Phone`,
ifnull(`gg`.`Description`,'') AS `Gender`,
(case when (`n`.`Birth_Month` = 0) then '' else `n`.`Birth_Month` end) AS `Birth Month`,
`n`.`Updated_By` AS `Updated By`,
`n`.`Last_Updated` AS `Last_Updated`,
`n`.`Timestamp` AS `Created On`
from ((((((`name` `n` left join `gen_lookups` `gt` on(((`n`.`Member_Type` = `gt`.`Code`) and (`gt`.`Table_Name` = 'Member_Basis'))))
left join `gen_lookups` `gs` on(((`n`.`Member_Status` = `gs`.`Code`) and (`gs`.`Table_Name` = 'mem_status'))))
left join `gen_lookups` `gpm` on(((`n`.`Preferred_Mail_Address` = `gpm`.`Code`) and (`gpm`.`Table_Name` = 'Address_Purpose'))))
left join `gen_lookups` `gpe` on(((`n`.`Preferred_Email` = `gpe`.`Code`) and (`gpe`.`Table_Name` = 'Email_Purpose'))))
left join `gen_lookups` `gpp` on(((`n`.`Preferred_Phone` = `gpp`.`Code`) and (`gpp`.`Table_Name` = 'Phone_Type'))))
left join `gen_lookups` `gg` on(((`n`.`Gender` = `gg`.`Code`) and (`gg`.`Table_Name` = 'Gender'))))
where (`n`.`idName` > 0);


-- -----------------------------------------------------
-- View `vdump_phone`
-- -----------------------------------------------------
CREATE or replace VIEW `vdump_phone` AS
select `p`.`idName` AS `Id`,
`gc`.`Description` AS `Purpose`,
`p`.`Phone_Num` AS `Number`,
`p`.`Phone_Extension` AS `Extn`,
(case when (`p`.`Status` = 'a') then 'Active' else '' end) AS `Status`,
`p`.`Last_Updated` AS `Last_Updated`,
`p`.`Updated_By` AS `Updated By`,
`p`.`Timestamp` AS `Created On`
from (`name_phone` `p` left join `gen_lookups` `gc` on(((`p`.`Phone_Code` = `gc`.`Code`) and (`gc`.`Table_Name` = 'Phone_Type'))))
where (`p`.`idName` > 0) order by `p`.`idName`;




-- -----------------------------------------------------
-- View `vdump_volunteer`
-- -----------------------------------------------------
CREATE or replace VIEW `vdump_volunteer` AS
select `v`.`idName` AS `Id`,
`g`.`Description` AS `Category`,
`g2`.`Description` AS `Code`,
`v`.`Vol_Status` AS `Status`,
ifnull(`v`.`Vol_Notes`,'') AS `Notes`,
ifnull(`v`.`Vol_Begin`,'') AS `Begin`,
ifnull(`v`.`Vol_End`,'') AS `End`,
ifnull(`v`.`Vol_Check_Date`,'') AS `Check Date`,
ifnull(`gdor`.`Description`,'') AS `Dormancy`,
ifnull(`g3`.`Description`,'') AS `Rank`,
`v`.`Updated_By` AS `Updated By`,
`v`.`Last_Updated` AS `Last_Updated`,
`v`.`Timestamp` AS `Created on`
from ((((`name_volunteer2` `v` left join `gen_lookups` `g` on(((`v`.`Vol_Category` = `g`.`Code`) and (`g`.`Table_Name` = 'Vol_Category'))))
left join `gen_lookups` `g2` on(((`v`.`Vol_Code` = `g2`.`Code`) and (`g2`.`Table_Name` = `v`.`Vol_Category`))))
left join `gen_lookups` `g3` on(((`g3`.`Code` = `v`.`Vol_Rank`) and (`g3`.`Table_Name` = 'Vol_Rank'))))
left join `dormant_schedules` `gdor` on((`gdor`.`Code` = `v`.`Dormant_Code`)))
where (`v`.`idName` > 0) order by `v`.`idName`,`g`.`Description`,`g2`.`Description`;




-- -----------------------------------------------------
-- View `vdump_webuser`
-- -----------------------------------------------------
CREATE or replace VIEW `vdump_webuser` AS
select `u`.`idName` AS `Id`,
`u`.`User_Name` AS `Username`,
`gwr`.`Description` AS `Web Role`,
`gws`.`Description` AS `Web Status`,
`u`.`Verify_Address` AS `Verify Address`,
(case when (`a`.`Timestamp` > `u`.`Timestamp`) then `a`.`Timestamp` else `u`.`Timestamp` end) AS `Created On`,
(case when (`a`.`Last_Updated` > `u`.`Last_Updated`) then `a`.`Last_Updated` else `u`.`Last_Updated` end) AS `Last_Updated`
from (((`w_users` `u` join `w_auth` `a` on((`u`.`idName` = `a`.`idName`)))
left join `gen_lookups` `gws` on(((`u`.`Status` = `gws`.`Code`) and (`gws`.`Table_Name` = 'Web_User_Status'))))
left join `gen_lookups` `gwr` on(((`a`.`Role_Id` = `gwr`.`Code`) and (`gwr`.`Table_Name` = 'Role_Codes'))))
where (`u`.`idName` > 0);



-- -----------------------------------------------------
-- View `vemail_directory`
-- -----------------------------------------------------
CREATE or replace VIEW `vemail_directory` AS
    select
        n.idName, `ne`.`Email` AS `Email`, `n`.`Member_Type` AS `Member_Type`,
		case when n.Record_Member = 1 then concat(n.Name_First, ' ',n.Name_Last) else n.Company end as `Name`
    from
        (`name_email` `ne`
        join `name` `n` ON (((`ne`.`idName` = `n`.`idName`)
            and (`n`.`Preferred_Email` = `ne`.`Purpose`))))
    where
        ((`n`.`Member_Status` = 'a')
            and (`n`.`Exclude_Email` = 0)
            and (`ne`.`Email` <> ''));



-- -----------------------------------------------------
-- View `vguest_export`
-- -----------------------------------------------------
create or replace view `vguest_export` as
Select 
    ng.idName as `Guest_Id`,
    ifnull(rv.idReservation, '') as `Reservation_Id`,
    ifnull(l.`Title`, '') as `Status_Title`,
    ifnull(l.`Code`, '') as `Status_Code`,
    ifnull(n.Name_First, '') as `First_Name`,
    ifnull(n.Name_Last, '') as `Last_Name`,
    ifnull(g.Description, '') as `Gender`,
    'SSR' as `Building`,
    ifnull(re.Title, '') as `Room`,
    ifnull(rv.Expected_Arrival, '') as `Expected_Arrival`,
    ifnull(rv.Expected_Departure, '') as `Expected_Departure`,
    ifnull(rv.Actual_Arrival, '') as `Actual_Arrival`,
    IFNULL(rv.Actual_Departure, '') as `Actual_Departure`,
    ifnull(ne.Email, '') as `Email`,
    ifnull(np.Phone_Num, '') as `Phone`
from
    name_guest ng 
            left join
    registration rg on ng.idPsg = rg.idPsg
            left join 
    reservation rv on rg.idRegistration = rv.idRegistration
            left join
    `name` n on ng.idName = n.idName
            left join
    name_email ne on ng.idName = ne.idName and n.Preferred_Email = ne.Purpose
            left join
    name_phone np on ng.idName = np.idName and n.Preferred_Phone = np.Phone_Code
            left join
    gen_lookups g on g.`Table_Name` = 'Gender' and g.`Code` = n.Gender
            left join
    resource re on rv.idResource = re.idResource
            left join
    lookups l on l.Category = 'ReservStatus' and l.`Code` = rv.`Status`;



-- -----------------------------------------------------
-- View `vguest_history_records`
-- -----------------------------------------------------
CREATE or replace VIEW `vguest_history_records` AS
select
    m.Id AS Id,
    (case
        when (m.MemberRecord = 1) then m.Fullname
        else m.Company
    end) AS Fullname,
    m.Preferred_Phone AS Preferred_Phone,
    m.Preferred_Email AS Preferred_Email,
    (case
        when (m.Bad_Address = 'true') then '*(Bad Address)*'
        else m.Address_1
    end) AS Address_1,
    (case
        when (m.Bad_Address = 'true') then ''
        else m.Address_2
    end) AS Address_2,
    (case
        when (m.Bad_Address = 'true') then ''
        else m.City
    end) AS City,
    (case
        when (m.Bad_Address = 'true') then ''
        else m.StateProvince
    end) AS StateProvince,
    (case
        when (m.Bad_Address = 'true') then ''
        else m.Country_Code
    end) AS Country_Code,
    (case
        when (m.Bad_Address = 'true') then ''
        else m.PostalCode
    end) AS PostalCode,
    (case
        when (m.MemberRecord = 1) then m.Company
        else ''
    end) AS Company
from
    (member_history g
    join vmember_listing m ON ((g.idName = m.Id)))
    order by g.Guest_Access_Date desc limit 12;



-- -----------------------------------------------------
-- View `vguest_listing`
-- -----------------------------------------------------
create or replace view vguest_listing as
select 
    `n`.`idName` AS `Id`,
    ifnull(`g1`.`Description`, '') AS `Prefix`,
    `n`.`Name_First` AS `First`,
    `n`.`Name_Middle` AS `Middle`,
    `n`.`Name_Last` AS `Last`,
    ifnull(`g2`.`Description`, '') AS `Suffix`,
    (case when (ifnull(`np`.`Phone_Extension`, '') = '') then ifnull(`np`.`Phone_Num`, '')
        else concat_ws('x', `np`.`Phone_Num`, `np`.`Phone_Extension`)
    end) AS `Phone`,
    ifnull(`ne`.`Email`, '') AS `Email`,
    case when ifnull(`na`.`Address_2`, '') = '' then ifnull(`na`.`Address_1`, '') 
        else concat(ifnull(`na`.`Address_1`, ''), ', ', `na`.`Address_2`) end AS `Address`,
    ifnull(`na`.`City`, '') AS `City`,
    ifnull(`na`.`County`, '') AS `County`,
    ifnull(`na`.`State_Province`, '') AS `State`,
    ifnull(`na`.`Postal_Code`, '') AS `Zip`,
    ifnull(`na`.`Country`, '') AS `Country`,
    ifnull(n.BirthDate, '') as `BirthDate`,
    `g3`.`Description` as `Patient Rel.`,
    `ng`.`Relationship_Code`,
    `ng`.`idPsg`,
    `nd`.`No_Return`as `ngStatus`,
    `n`.`External_Id`
from `name_guest` `ng` 
	left join `name` `n` on `ng`.`idName` = `n`.`idName`
    left join `name_address` `na` ON `ng`.`idName` = `na`.`idName`
        and `n`.`Preferred_Mail_Address` = `na`.`Purpose`
    left join `name_email` `ne` ON `ng`.`idName` = `ne`.`idName`
        and `n`.`Preferred_Email` = `ne`.`Purpose`
    left join `name_phone` `np` ON `ng`.`idName` = `np`.`idName`
        and `n`.`Preferred_Phone` = `np`.`Phone_Code`
    left join `name_demog` nd on ng.idName = nd.idName
    left join `gen_lookups` `g1` ON `n`.`Name_Prefix` = `g1`.`Code`
        and `g1`.`Table_Name` = 'Name_Prefix'
    left join `gen_lookups` `g2` ON `n`.`Name_Suffix` = `g2`.`Code`
        and `g2`.`Table_Name` = 'Name_Suffix'
    left join `gen_lookups` `g3` on `g3`.`Table_Name` = 'Patient_Rel_Type'
        and `g3`.`Code` = `ng`.`Relationship_Code`
where
    ((`ng`.`idName` > 0) and n.`Record_Member` = 1
        and (`n`.`Member_Status` in ('a' , 'd', 'in')));



-- -----------------------------------------------------
-- View `vguest_search_neon`
-- -----------------------------------------------------
CREATE  OR REPLACE VIEW `vguest_search_neon` AS
    SELECT 
        `n`.`idName` AS `HHK_ID`,
        `n`.`External_Id` AS `Account ID`,
        IFNULL(`g1`.`Description`, '') AS `Prefix`,
        `n`.`Name_First` AS `First Name`,
        `n`.`Name_Middle` AS `Middle Name`,
        `n`.`Name_Last` AS `Last Name`,
        IFNULL(`g2`.`Description`, '') AS `Suffix`,
        IFNULL(`ne`.`Email`, '') AS `Email`,
        IFNULL(`na`.`City`, '') AS `City`,
        IFNULL(`na`.`Postal_Code`, '') AS `Zip Code`
    FROM
        `name` `n` 
        LEFT JOIN `name_address` `na` ON `n`.`idName` = `na`.`idName`
            AND (`n`.`Preferred_Mail_Address` = `na`.`Purpose`)
        LEFT JOIN `name_email` `ne` ON `n`.`idName` = `ne`.`idName`
            AND (`n`.`Preferred_Email` = `ne`.`Purpose`)
        LEFT JOIN `name_phone` `np` ON `n`.`idName` = `np`.`idName`
            AND (`n`.`Preferred_Phone` = `np`.`Phone_Code`)
        LEFT JOIN `gen_lookups` `g1` ON `n`.`Name_Prefix` = `g1`.`Code`
            AND (`g1`.`Table_Name` = 'Name_Prefix')
        LEFT JOIN `gen_lookups` `g2` ON `n`.`Name_Suffix` = `g2`.`Code`
            AND (`g2`.`Table_Name` = 'Name_Suffix')
    WHERE
        `n`.`idName` > 0
        AND n.idName in (select idName from name_guest)
            AND (`n`.`Record_Member` = 1)
            AND (`n`.`Member_Status` IN ('a' , 'd', 'in'));



-- -----------------------------------------------------
-- View `vguest_data_neon`
-- -----------------------------------------------------
CREATE OR REPLACE VIEW `vguest_data_neon` AS
    SELECT 
        `n`.`idName` AS `HHK_ID`,
        n.`External_Id` AS `accountId`,
        IFNULL(`g1`.`Description`, '') AS `prefix`,
        `n`.`Name_First` AS `firstName`,
        `n`.`Name_Middle` AS `middleName`,
        `n`.`Name_Last` AS `lastName`,
        `n`.`Name_Nickname` AS `preferredName`,
        IFNULL(DATE_FORMAT(n.`BirthDate`, '%Y-%m-%d'), '') AS `dob`,
        IFNULL(DATE_FORMAT(n.`Date_Deceased`, '%Y-%m-%d'), '') AS `Deceased_Date`,
        (CASE
            WHEN `n`.`Member_Status` = 'd' THEN 'true'
            ELSE ''
        END) AS `deceased`,
        IFNULL(`g2`.`Description`, '') AS `suffix`,
        IFNULL(`g5`.`Description`, '') AS `gender.name`,
        IFNULL(`np`.`Phone_Search`, '') AS `phone1`,
        (CASE
            WHEN `np`.`Phone_Code` = 'mc' THEN 'Mobile'
            WHEN `np`.`Phone_Code` = 'dh' THEN 'Home'
            WHEN `np`.`Phone_Code` = 'gw' THEN 'Business'
            WHEN IFNULL(`np`.`Phone_Code`, '') = '' THEN ''
            ELSE 'Home'
        END) AS `phone1Type`,
        (CASE
            WHEN `na`.`Purpose` = '1' THEN 'Home'
            WHEN `na`.`Purpose` = '2' THEN 'Business'
            WHEN IFNULL(`na`.`Purpose`, '') = '' THEN ''
            ELSE 'Home'
        END) AS `addressType.name`,
        IFNULL(`ne`.`Email`, '') AS `email1`,
        IFNULL(`na`.`Address_1`, '') AS `addressLine1`,
        IFNULL(`na`.`Address_2`, '') AS `addressLine2`,
        IFNULL(`na`.`City`, '') AS `city`,
        IFNULL(`na`.`County`, '') AS `county`,
        (CASE
            WHEN `cc`.`External_Id` > 2 THEN `na`.`State_Province`
            ELSE ''
        END) AS `province`,
        (CASE
            WHEN
                `cc`.`External_Id` = 1
                    OR `cc`.`External_Id` = 2
            THEN
                `na`.`State_Province`
            ELSE ''
        END) AS `state.code`,
        IFNULL(`cc`.`External_Id`, '') AS `country.id`,
        IFNULL(`na`.`Postal_Code`, '') AS `zipCode`,
        (CASE WHEN `ng`.`Relationship_Code` = 'slf' THEN 'Patient' ELSE 'Guest' END) AS `individualTypes.individualType.name`,
        IFNULL(`g3`.`Description`, '') AS `PSG_Relationship`,
        IFNULL(`ng`.`idPsg`, '') AS `PSG_ID`,
        IFNULL(`g4`.`Description`, '') AS `No_Return`,
        'HHK' as `source.name`
    FROM
        `name_guest` `ng`
            LEFT JOIN
        `name` `n` ON `ng`.`idName` = `n`.`idName`
            LEFT JOIN
        `name_address` `na` ON `ng`.`idName` = `na`.`idName`
            AND (`n`.`Preferred_Mail_Address` = `na`.`Purpose`)
            LEFT JOIN
        `name_email` `ne` ON `ng`.`idName` = `ne`.`idName`
            AND (`n`.`Preferred_Email` = `ne`.`Purpose`)
            LEFT JOIN
        `name_phone` `np` ON `ng`.`idName` = `np`.`idName`
            AND (`n`.`Preferred_Phone` = `np`.`Phone_Code`)
            LEFT JOIN
        `name_demog` `nd` ON `n`.`idName` = `nd`.`idName`
            LEFT JOIN
        `country_code` `cc` ON `na`.`Country_Code` = `cc`.`ISO_3166-1-alpha-2`
            LEFT JOIN
        `gen_lookups` `g1` ON `n`.`Name_Prefix` = `g1`.`Code`
            AND (`g1`.`Table_Name` = 'Name_Prefix')
            LEFT JOIN
        `gen_lookups` `g2` ON `n`.`Name_Suffix` = `g2`.`Code`
            AND (`g2`.`Table_Name` = 'Name_Suffix')
            LEFT JOIN
        `gen_lookups` `g3` ON `ng`.`Relationship_Code` = `g3`.`Code`
            AND (`g3`.`Table_Name` = 'Patient_Rel_Type')
            LEFT JOIN
        `gen_lookups` `g4` ON `nd`.`No_Return` = `g4`.`Code`
            AND (`g4`.`Table_Name` = 'NoReturnReason')
            LEFT JOIN
        `gen_lookups` `g5` ON `n`.`Gender` = `g5`.`Code`
            AND (`g5`.`Table_Name` = 'Gender')
    WHERE
        ((`ng`.`idName` > 0)
            AND (`n`.`Record_Member` = 1)
            AND (`n`.`Member_Status` IN ('a' , 'd', 'in')))
    GROUP BY n.idName;



-- -----------------------------------------------------
-- View `vguest_view`
-- -----------------------------------------------------
CREATE or replace VIEW `vguest_view` AS
Select 
    ifnull((case when n.Name_Suffix = '' then n.Name_Last else concat(n.Name_Last, ' ', g.`Description`) end), '') as `Last Name`,
    ifnull(n.Name_First, '') as `First Name`,
    ifnull(rm.Title, '')as `Room`,
    ifnull(np.Phone_Num, '') as `Phone`,
    s.Checkin_Date as `Arrival`,
    case when s.Expected_Co_Date < now() then now() else s.Expected_Co_Date end as `Expected Departure`,
    s.On_Leave,
    ifnull(v.Make, '') as `Make`,
    ifnull(v.Model, '') as `Model`,
    ifnull(v.Color, '') as `Color`,
    ifnull(v.State_Reg, '') as `State Reg.`,
    ifnull(v.License_Number, '') as `License Plate`
from
    stays s
        left join
    `name` n ON n.idName = s.idName
        left join
    name_phone np ON n.idName = np.idName
        and n.Preferred_Phone = np.Phone_Code
        left join
    visit vs ON s.idVisit = vs.idVisit
        and s.Visit_Span = vs.Span
        left join
    vehicle v ON vs.idRegistration = v.idRegistration
        left join
    room rm ON s.idRoom = rm.idRoom
        left join
    gen_lookups g on g.`Table_Name` = 'Name_Suffix' and g.`Code` = n.Name_Suffix
where
    s.`Status` = 'a';



-- -----------------------------------------------------
-- View `vhospitalstay_log`
-- -----------------------------------------------------
create or replace view vhospitalstay_log as
select
	l.Sub_Type,
	l.User_Name,
	l.idName,
	l.idReservation,
	l.idPsg,
	l.idAgent,
	l.idHospital_stay,
	l.Log_Text,
	l.Timestamp,
	n.Name_First,
	n.Name_Last,
        na.Name_First as Agent_First,
        na.Name_Last as Agent_Last
from reservation_log l left join name n on l.idName = n.idName
    left join name na on l.idAgent = na.idName
where l.log_Type = 'hospitalStay';



-- -----------------------------------------------------
-- View `vindividual_donations`
-- -----------------------------------------------------
CREATE or replace VIEW `vindividual_donations` AS
    select
        `vm`.`Id` AS `id`,
        `vm`.`Name_Last` AS `Donor_Last`,
        `vm`.`Name_First` AS `Donor_First`,
        `vm`.`Name_Nickname` AS `Donor_Nickname`,
        `vm`.`Name_Prefix` AS `Donor_Prefix`,
        `vm`.`Name_Suffix` AS `Donor_Suffix`,
        `vm`.`Name_Middle` AS `Donor_Middle`,
        `vm`.`Title` AS `Donor_Title`,
        `vm`.`Gender` AS `Donor_Gender`,
        `vm`.`MemberStatus` AS `Donor_Status`,
        `vm`.`SpouseId` AS `Donor_Partner_Id`,
        `vm`.`Company` AS `Donor_Company`,
        `vm`.`Address_Code` AS `Donor_Preferred_Addr_Code`,
        (case
            when `vm`.`MemberRecord` then ifnull(`vp`.`Name_First`, '')
            else ifnull(`ve`.`Name_First`, '')
        end) AS `Assoc_First`,
        (case
            when `vm`.`MemberRecord` then ifnull(`vp`.`Name_Last`, '')
            else ifnull(`ve`.`Name_Last`, '')
        end) AS `Assoc_Last`,
        (case
            when `vm`.`MemberRecord` then ifnull(`vp`.`Name_Nickname`, '')
            else ifnull(`ve`.`Name_Nickname`, '')
        end) AS `Assoc_Nickname`,
        (case
            when `vm`.`MemberRecord` then ifnull(`vp`.`Name_Prefix`, '')
            else ifnull(`ve`.`Name_Prefix`, '')
        end) AS `Assoc_Prefix`,
        (case
            when `vm`.`MemberRecord` then ifnull(`vp`.`Name_Suffix`, '')
            else ifnull(`ve`.`Name_Suffix`, '')
        end) AS `Assoc_Suffix`,
        (case
            when `vm`.`MemberRecord` then ifnull(`vp`.`Name_Middle`, '')
            else ifnull(`ve`.`Name_Middle`, '')
        end) AS `Assoc_Middle`,
        (case
            when `vm`.`MemberRecord` then ''
            else ifnull(`ve`.`Title`, '')
        end) AS `Assoc_Title`,
        (case
            when `vm`.`MemberRecord` then ''
            else ifnull(`ve`.`Company`, '')
        end) AS `Assoc_Company`,
        (case
            when `vm`.`MemberRecord` then ifnull(`vp`.`Gender`, '')
            else ifnull(`ve`.`Gender`, '')
        end) AS `Assoc_Gender`,
        (case
            when `vm`.`MemberRecord` then ifnull(`vp`.`MemberStatus`, '')
            else ifnull(`ve`.`MemberStatus`, '')
        end) AS `Assoc_Status`,
        (case
            when `vm`.`MemberRecord` then ifnull(`vp`.`SpouseId`, 0)
            else ifnull(`ve`.`Company_Id`, 0)
        end) AS `Assoc_Partner_Id`,
        (case
            when `vm`.`MemberRecord` then ifnull(`vp`.`Address_Code`, '')
            else ifnull(`ve`.`Address_Code`, '')
        end) AS `Assoc_Preferred_Addr_Code`,
        (case
            when (lcase(`vm`.`Bad_Address`) = 'true') then '*(Bad Address)'
            else ifnull(`vm`.`Address_1`, '')
        end) AS `Address_1`,
        (case
            when (lcase(`vm`.`Bad_Address`) = 'true') then ''
            else ifnull(`vm`.`Address_2`, '')
        end) AS `Address_2`,
        (case
            when (lcase(`vm`.`Bad_Address`) = 'true') then ''
            else ifnull(`vm`.`City`, '')
        end) AS `City`,
        (case
            when (lcase(`vm`.`Bad_Address`) = 'true') then ''
            else ifnull(`vm`.`StateProvince`,  '')
        end) AS `State`,
        (case
            when (lcase(`vm`.`Bad_Address`) = 'true') then ''
            else ifnull(`vm`.`PostalCode`,  '')
        end) AS `Zipcode`,
        (case
            when (lcase(`vp`.`Bad_Address`) = 'true') then '*(Bad Address)'
            else ifnull(`vp`.`Address_1`, '')
        end) AS `Assoc_Address_1`,
        (case
            when (lcase(`vp`.`Bad_Address`) = 'true') then ''
            else ifnull(`vp`.`Address_2`, '')
        end) AS `Assoc_Address_2`,
        (case
            when (lcase(`vp`.`Bad_Address`) = 'true') then ''
            else ifnull(`vp`.`City`, '')
        end) AS `Assoc_City`,
        (case
            when (lcase(`vp`.`Bad_Address`) = 'true') then ''
            else ifnull(`vp`.`StateProvince`, '')
        end) AS `Assoc_State`,
        (case
            when (lcase(`vp`.`Bad_Address`) = 'true') then ''
            else ifnull( `vp`.`PostalCode`, '')
        end) AS `Assoc_Zipcode`,
        `d`.`Amount` AS `Amount`,
        (case
            when
                (ifnull(`c`.`Campaign_Type`, '') = 'pct')
            then
                round((`d`.`Amount` - ((`d`.`Amount` * `c`.`Percent_Cut`) / 100)),
                        2)
            else `d`.`Amount`
        end) AS `Tax_Free`,
        (case
            when (ifnull(`c`.`Campaign_Type`, '') = 'pct') then `c`.`Percent_Cut`
            else 0
        end) AS `Percent_Cut`,
        ifnull(`c`.`Title`, '') AS `Campaign_Title`,
        ifnull(`c`.`Campaign_Merge_Code`, '') AS `Mail_Merge_Code`,
        `d`.`Date_Entered` AS `Effective_Date`,
        `d`.`Salutation_Code` AS `Salutation_Code`,
        `d`.`Campaign_Code` AS `Campaign_Code`,
        `d`.`Envelope_Code` AS `Envelope_Name_Code`,
        (case
            when `vm`.`MemberRecord` then 0
            else 1
        end) AS `isCompany`,
        `d`.`Care_Of_Id` AS `Care_Of_Id`,
        `d`.`Assoc_Id` AS `Assoc_Id`,
        `d`.`Member_type` AS `Member_Type`,
        c.Campaign_Type,
        d.Fund_Code,
        d.Note
    from
        ((((`donations` `d`
        left join `campaign` `c` ON ((lcase(trim(`d`.`Campaign_Code`)) = lcase(trim(`c`.`Campaign_Code`)))))
        left join `vmember_listing_noex` `vm` ON ((`vm`.`Id` = `d`.`Donor_Id`)))
        left join `vmember_listing_noex` `vp` ON ((`vp`.`Id` = `d`.`Assoc_Id`)))
        left join `vmember_listing_noex` `ve` ON ((`ve`.`Id` = `d`.`Care_Of_Id`)))
    where
        (`d`.`Status` = 'a');




-- -----------------------------------------------------
-- View `vitem_list`
-- -----------------------------------------------------
create or replace view vitem_list as
select 
    i.*, it.*, ip.*
from
    item_type_map itm
        left join
    item i ON itm.Item_Id = i.idItem
        left join
    item_type it ON itm.Type_Id = it.idItem_Type
        left join
    item_price ip ON itm.Item_Id = ip.Item_Id;




-- -----------------------------------------------------
-- View `vlist_inv_pments`
-- -----------------------------------------------------
create or replace view `vlist_inv_pments` as
select 
    `i`.`idInvoice` AS `idInvoice`,
    `i`.`Invoice_Number` AS `Invoice_Number`,
    `i`.`Amount` AS `Invoice_Amount`,
    `i`.`Sold_To_Id` AS `Sold_To_Id`,
    ifnull(nv.Vol_Status, '') as `Bill_Agent`,
    `i`.`idGroup` AS `idGroup`,
    `i`.`Order_Number` AS `Order_Number`,
    `i`.`Suborder_Number` AS `Suborder_Number`,
    `i`.`Invoice_Date` AS `Invoice_Date`,
    `i`.`Status` AS `Invoice_Status`,
    `i`.`Carried_Amount` AS `Carried_Amount`,
    `i`.`Balance` AS `Invoice_Balance`,
    `i`.`Delegated_Invoice_Id` AS `Delegated_Invoice_Id`,
    i.Description,
    i.Deleted,
    `i`.`Updated_By` as `Invoice_Updated_By`,
    g1.Description as `Invoice_Status_Title`,
    ifnull(p.idPayment, 0) as `idPayment`,
    ifnull(`p`.`Amount`, 0) AS `Payment_Amount`,
    ifnull(`p`.`Balance`, 0) AS `Payment_Balance`,
    ifnull(`p`.`idPayment_Method`, 0) AS `idPayment_Method`,
    ifnull(`pm`.`Method_Name`, '') as `Payment_Method_Title`,
    ifnull(`p`.`Status_Code`, 0) AS `Payment_Status`,
    ifnull(`g2`.`Description`, '') AS `Payment_Status_Title`,
    ifnull(`p`.`Payment_Date`, 0) AS `Payment_Date`,
    ifnull(`p`.`Is_Refund`, 0) as `Is_Refund`,
    ifnull(`p`.`idPayor`, 0) AS `Payment_idPayor`,
    ifnull(`p`.`Updated_By`, '') as `Payment_Updated_By`,
    ifnull(`p`.`Created_By`, '') as `Payment_Created_By`,
    ifnull(`p`.`Notes`, '') as `Payment_Note`,
    ifnull(`pa`.`idPayment_auth`, 0) AS `idPayment_auth`,
    ifnull(`pa`.`Customer_Id`, 0) AS `Charge_Customer_Id`,
    ifnull(`pa`.`Acct_Number`, `p`.`Data2`) AS `Masked_Account`,
    ifnull(`pa`.`Card_Type`, '') AS `Card_Type`,
    ifnull(`pa`.`Approved_Amount`, '') AS `Approved_Amount`,
    ifnull(`pa`.`Approval_Code`, '') AS `Approval_Code`,
    ifnull(`pa`.`Status_Code`, '') as `PA_Status_Code`,
    ifnull(`pc`.`Check_Number`, '') AS `Check_Number`
from
    invoice i 
        left join
    payment_invoice pi ON i.idInvoice = pi.Invoice_Id
        left join
    payment p ON pi.Payment_Id = p.idPayment
        left join
    payment_auth pa ON pi.Payment_Id = pa.idPayment
        left join
    payment_info_check pc ON pi.Payment_Id = pc.idPayment
        left join
    payment_method pm on p.idPayment_Method = pm.idPayment_method
        left join
    name_volunteer2 nv on i.Sold_To_Id = nv.idName and nv.Vol_Category = 'Vol_Type' and nv.Vol_Code = 'ba'
        left join
    gen_lookups g2 on p.Status_Code = g2.`Code` and g2.`Table_Name` = 'Payment_Status'
        left join
    gen_lookups g1 ON i.Status = g1.`Code`
        and g1.`Table_Name` = 'Invoice_Status';






-- -----------------------------------------------------
-- View `vlocation_listing`
-- -----------------------------------------------------
CREATE or REPLACE VIEW `vlocation_listing` AS
Select
    l.idLocation as Id,
    l.Title,
    l.Description,
    l.Status,
    l.Address,
    l.Phone,
    l.Owner_Id as `Owner Id`,
    ifnull(v.Name_Last, '') AS Owner,
    l.Updated_By as `Updated By`,
    ifnull(l.Last_Updated, '') AS `Last Updated`,
    concat('<a target="blank" href="',
            l.Map,
            '">View</a>') as Link,
    l.Map as `Map URL`
FROM
    location l
        left join
    vmember_listing v ON l.Owner_Id = v.Id;



-- -----------------------------------------------------
-- View `vmailing_list`
-- -----------------------------------------------------
CREATE or replace VIEW `vmailing_list` AS
select `ml`.`id` AS `idName`,
vm.`Address_1` as Street,
`vm`.`City` AS `City`,
`vm`.`StateProvince` AS `State`,
`vm`.`PostalCode` AS `Zip`,
(case when (`vm`.`MemberRecord` = 0) then ifnull(`ve`.`Name_Last`,'') else `vm`.`Name_Last` end) AS `Name_Last`,
(case when (`vm`.`MemberRecord` = 0) then ifnull(`ve`.`Name_First`,'') else `vm`.`Name_First` end) AS `Name_First`,
(case when (`vm`.`MemberRecord` = 0) then ifnull(`ve`.`Name_Nickname`,'') else `vm`.`Name_Nickname` end) AS `Name_Nickname`,
(case when (`vm`.`MemberRecord` = 0) then ifnull(`ve`.`Name_Prefix`,'') else `vm`.`Name_Prefix` end) AS `Name_Prefix`,
(case when (`vm`.`MemberRecord` = 0) then ifnull(`ve`.`Name_Suffix`,'') else `vm`.`Name_Suffix` end) AS `Name_Suffix`,
(case when (`vm`.`MemberRecord` = 0) then ifnull(`ve`.`Name_Middle`,'') else `vm`.`Name_Middle` end) AS `Name_Middle`,
(case when (`vm`.`MemberRecord` = 0) then ifnull(`ve`.`Title`,'') else `vm`.`Title` end) AS `Name_Title`,
(case when (`vm`.`MemberRecord` = 0) then ifnull(`ve`.`Gender`,'') else `vm`.`Gender` end) AS `Name_Gender`,
ifnull(`vp`.`Name_First`,'') AS `Partner_First`,
ifnull(`vp`.`Name_Last`,'') AS `Partner_Last`,
ifnull(`vp`.`Name_Nickname`,'') AS `Partner_Nickname`,
ifnull(`vp`.`Name_Prefix`,'') AS `Partner_Prefix`,
ifnull(`vp`.`Name_Suffix`,'') AS `Partner_Suffix`,
ifnull(`vp`.`Name_Middle`,'') AS `Partner_Middle`,
ifnull(`vp`.`Gender`,'') AS `Partner_Gender`,
vm.Address_Code,
`vm`.`Company` AS `Company`,
`vm`.`Company_Id` AS `Company_Id`,
case when sp.Relation_Type is null then ifnull(ve.Id,vm.Id) else '' end as Family_Mem,
count(`ml`.`adr_frag`) AS `Address_Count`
from `mail_listing` `ml` left join `vmember_listing` `vm` on `ml`.`id` = `vm`.`Id`
left join `vmember_listing` `vp` on `vp`.`Id` = `vm`.`SpouseId` and `vp`.`MemberStatus` = 'a'
left join `vmember_listing` `ve` on `ve`.`Company_Id` = `vm`.`Id` and `ve`.`Company_CareOf` = 'y' and `ve`.`MemberStatus` = 'a'
left join relationship sp on  (vm.Id = sp.idName or vm.Id = sp.Target_Id) and sp.Relation_Type = 'sp' and sp.Status = 'a'
group by concat(`ml`.`adr_frag`, `fm`);


-- -----------------------------------------------------
-- View `vmember_categories`
-- -----------------------------------------------------
CREATE or replace VIEW `vmember_categories` AS
select
    nv.idName AS `idName`,
    nv.Vol_Code AS `Vol_Code`,
    nv.Vol_Category AS `Vol_Category`,
    c.Vol_Category_Title AS `Vol_Category_Title`,
    c.Vol_Code_Title AS `Vol_Code_Title`,
    c.Colors AS `Colors`,
    nv.Vol_Status AS `Vol_Status`,
    d.Begin_Active AS `Dormant_Begin_Active`,
    d.End_Active AS `Dormant_End_Active`,
    ifnull(d.Title,'') as `Dormant_Title`,
    ifnull(nv.Vol_Notes, '') AS `Vol_Notes`,
    nv.Vol_Begin AS `Vol_Begin`,
    nv.Vol_End AS `Vol_End`,
    nv.Vol_Check_Date AS `Vol_Check_Date`,
    nv.Vol_Rank AS `Vol_Rank`,
    ifnull(gr.Description, '') as `Vol_Rank_Title`,
    ifnull(gstat.Description, '') as `Vol_Status_Title`
from
    name_volunteer2 nv
    left join vcategory_listing c ON nv.Vol_Category = c.Vol_Category and nv.Vol_Code = c.Vol_Code
    left join dormant_schedules d ON nv.Dormant_Code = d.Code and d.Status = 'a'
    left join gen_lookups gr ON gr.Table_Name = 'Vol_Rank' and gr.Code = nv.Vol_Rank
    left join gen_lookups gstat ON gstat.Table_Name = 'Vol_Status' and gstat.Code = nv.Vol_Status
    left join name on nv.idName = name.idName
    where name.Member_Status = 'a';



-- -----------------------------------------------------
-- View `vmember_directory`
-- -----------------------------------------------------
CREATE or replace VIEW `vmember_directory` AS
    select
        `vm`.`Id` AS `Id`,
        `vm`.`Fullname` AS `Fullname`,
        `vm`.`Name_Last` AS `Name_Last`,
        `vm`.`Name_First` AS `Name_First`,
        `vm`.`Name_Middle` AS `Name_Middle`,
        `vm`.`Name_Nickname` AS `Name_Nickname`,
        `vm`.`Name_Prefix` AS `Name_Prefix`,
        `vm`.`Name_Suffix` AS `Name_Suffix`,
        `vm`.`Preferred_Phone` AS `Preferred_Phone`,
        `vm`.`Address_1` AS `Address_1`,
        `vm`.`Address_2` AS `Address_2`,
        `vm`.`City` AS `City`,
        `vm`.`StateProvince` AS `StateProvince`,
        `vm`.`PostalCode` AS `PostalCode`,
        `vm`.`Bad_Address` AS `Bad_Address`,
        `vm`.`Preferred_Email` AS `Preferred_Email`,
        `vm`.`MemberStatus` AS `MemberStatus`,
        `vm`.`Gender` AS `Gender`,
        `vm`.`SpouseId` AS `SpouseId`,
--		(select count(Target_Id)  from relationship where idName = vm.Id and Relation_Type = 'par') as `Parents`,
--		(select count(idName)  from relationship where Target_Id = vm.Id and Relation_Type = 'par') as `Children`,
--		(select count(idName)  from relationship where Relation_Type = 'sib' and Group_Code = (Select Group_Code from relationship where Relation_Type='sib' and Status='a' and idName = vm.Id)) as `Siblings`,
--		(select count(idName)  from relationship where Relation_Type = 'rltv' and Group_Code = (Select Group_Code from relationship where Relation_Type='rltv' and Status='a' and idName = vm.Id)) as `Relatives`,
        `vm`.`MemberRecord` AS `MemberRecord`,
        `vm`.`Company_Id` AS `Company_Id`,
        `vm`.`Company` AS `Company`,
        `vm`.`Exclude_Mail` AS `Exclude_Mail`,
        `vm`.`Exclude_Email` AS `Exclude_Email`,
        `vm`.`Exclude_Phone` AS `Exclude_Phone`,
        `vm`.`Member_Type` AS `Member_Type`,
        `vm`.`Title` AS `Title`,
        `vm`.`Company_CareOf` AS `Company_CareOf`,
        `vm`.`Address_Code` AS `Address_Code`,
        `vm`.`Email_Code` AS `Email_Code`,
        `vm`.`Phone_Code` AS `Phone_Code`
    from
        `vmember_listing` `vm`
    where `vm`.`MemberStatus` = 'a' and `vm`.`Exclude_Directory` = 0;



-- -----------------------------------------------------
-- View `vmy_events`
-- -----------------------------------------------------
CREATE OR replace VIEW `vmy_events` AS
select
    m.idmcalendar AS idmcalendar,
    m.idName AS idName,
    m.idName2 AS idName2,

    m.E_Title AS E_Title,
    m.E_Start AS E_Start,
    m.E_End AS E_End,
    m.E_URL AS E_URL,
    m.E_ClassName AS E_ClassName,
    m.E_Editable AS E_Editable,
    m.E_Description AS E_Description,
    m.E_AllDay AS E_AllDay,
    m.E_Vol_Category AS E_Vol_Category,
    m.E_Vol_Code AS E_Vol_Code,
    m.E_Status AS E_Status,
    m.E_Take_Overable AS E_Take_Overable,
    m.E_Fixed_In_Time AS E_Fixed_In_Time,
    m.E_Shell AS E_Shell,
    m.E_Locked AS E_Locked,
    m.E_Shell_Id AS E_Shell_Id,
    m.E_Rpt_Id AS E_Rpt_Id,
    m.E_Show_All AS E_Show_All,
    ifnull(v.Name_First, '') AS First,
    ifnull(v.Name_Last, '') AS Last,
    ifnull(v2.Name_First, '') AS First2,
    ifnull(v2.Name_Last, '') AS Last2,
    c.Vol_Code_Title AS Vol_Description,
    c.Show_Email_Delete AS Show_Email_Delete,
    c.Hide_Add_Members AS Hide_Add_Members,
    c.Show_AllCategory AS Show_AllCategory,
    c.Cal_House
from
    mcalendar m

    left join name v ON m.idName = v.idName and v.Member_Status = 'a'
    left join name v2 ON m.idName2 = v2.idName and v2.Member_Status = 'a'
    left join vcategory_listing c ON c.Vol_Category = m.E_Vol_Category and c.Vol_Code = m.E_Vol_Code;




-- -----------------------------------------------------
-- View `vnon_reporting_list`
-- -----------------------------------------------------
create or replace view vnon_reporting_list as
Select n.idName as Id,
case when n.Record_Company = 1 then 'T' else '' end as `Is Org`,
case when n.Record_Member = 1 then n.Name_Last_First else n.Company end as `Name`,
case when n.Exclude_Directory = 1 then 'T' else '' end as `Directory`,
case when n.Exclude_Mail = 1 then 'T' else '' end as `Mail`,
case when n.Exclude_Phone = 1 then 'T' else '' end as `Phone`,
case when n.Exclude_Email = 1 then 'T' else '' end as `E-mail`,
case when n.Member_Status <> 'a' then ifnull(g.Description,'') else '' end as `Status`,
case when na.Bad_Address = 'true' then 'T' else '' end as `Bad Address`,
case when w.Status <> 'a' then ifnull(gw.Description,'') else '' end as `Web Status`
from name n left join gen_lookups g on g.Table_Name = 'mem_status' and g.Code = n.Member_Status
left join name_address na on n.idName = na.idName and n.Preferred_Mail_Address = na.Purpose
left join w_users w on n.idName = w.idName
left join gen_lookups gw on gw.Table_Name = 'Web_User_Status' and gw.Code = w.Status
where (n.Exclude_Directory = 1 or n.Exclude_Mail = 1 or n.Exclude_Phone = 1 or n.Exclude_Email = 1
    or n.Member_Status <> 'a' or na.Bad_Address = 'true' or w.Status <> 'a')
and n.idName > 0
order by n.Name_Last_First;


-- -----------------------------------------------------
-- View `vpsg_guest`
-- -----------------------------------------------------
CREATE or replace VIEW `vpsg_guest` AS
Select
    p.idPsg,
    ng.Relationship_Code,
    ng.idName as `idGuest`,
    ng.Legal_Custody,
    g.Description as `Relationship_Title`,
    (case when n.Name_Nickname = '' then n.Name_First else n.Name_Nickname end) as Name_First,
    (case when n.Name_Suffix = '' then n.Name_Last else concat(n.Name_Last, ' ', n.Name_Suffix) end) as Name_Last,
    gs.Description as Member_Status,
    n.Preferred_Phone,
    case when p.idPatient = ng.idName then 1 else 0 end as isPatient
from
    `psg` p
        left join
    `name_guest` ng ON p.idPsg = ng.idPsg
        left join
    `vmember_listing` `n` ON ng.idName = n.Id
        left join
    gen_lookups g ON g.Table_Name = 'Patient_Rel_Type' and g.Code = ng.Relationship_Code
        left join
    gen_lookups gs ON gs.Table_Name = 'mem_status' and gs.Code = n.MemberStatus;



-- -----------------------------------------------------
-- View `vrecent_calevents`
-- -----------------------------------------------------
CREATE or replace VIEW `vrecent_calevents` AS
    select
	m.Last_Updated as `Last Updated`,
        `m`.`E_Title` AS `Title`,
        `c`.`Vol_Code_Title` AS `Category`,
	ifnull(g.Description, '') as `Status`,
        `m`.`E_Start` AS `Start`,
        `m`.`E_End` AS `End`,
        ifnull(`v`.`Name_First`, '') AS `First`,
        ifnull((case when v.Name_Suffix = '' then v.Name_Last else concat(v.Name_Last, ' ', gs.Description) end), '') AS `Last`,
	m.Updated_By as `Updated By`
    from
        `mcalendar` `m`
        left join `name` `v` ON `m`.`idName` = `v`.`idName`
            and `v`.`Member_Status` = 'a'
        left join `vcategory_listing` `c` ON `c`.`Vol_Category` = `m`.`E_Vol_Category`
            and `c`.`Vol_Code` = `m`.`E_Vol_Code`
	left join gen_lookups g on g.Table_Name = 'Cal_Event_Status' and g.Code = m.E_Status
	left join gen_lookups gs on gs.Table_Name = 'Name_Suffix' and gs.Code = v.Name_Suffix;




-- -----------------------------------------------------
-- View `vregister`
-- -----------------------------------------------------
CREATE or replace VIEW `vregister` AS
    select
        concat(`v`.`idVisit`, v.Span) AS `id`,
        v.idVisit as idVisit,
        v.Span as `Span`,
        `v`.`idRegistration` AS `idRegistration`,
        `v`.`idResource` AS `idResource`,
        `v`.`Status` AS `Visit_Status`,
        `v`.`Span_Start` AS `Arrival_Date`,
        `v`.`Expected_Departure` AS `Expected_Departure`,
        `v`.`Span_End` AS `Actual_Departure`,
        ifnull(`hs`.`idHospital`, 0) AS `idHospital`,
        case when ifnull(hs.idAssociation, 0) > 0 and h.Title = '(None)' then 0 else ifnull(hs.idAssociation, 0) end as `idAssociation`,
        ifnull((case when n.Name_Suffix = '' then n.Name_Last else concat(n.Name_Last, ' ', gs.Description) end), '') as `Guest Last`
    from
        `visit` `v`
            left join
        hospital_stay hs on v.idHospital_stay = hs.idHospital_stay
            left join
        `hospital` `h` on hs.idAssociation = h.idHospital
            left join
        `name` n on v.idPrimaryGuest = n.idName
            left join 
        gen_lookups gs on gs.Table_Name = 'Name_Suffix' and gs.Code = n.Name_Suffix;



-- -----------------------------------------------------
-- View `vregistration`
-- -----------------------------------------------------
create or replace view vregistration as
select
    r.idPsg,
    r.idRegistration,
    r.Date_Registered,
    r.Status as Reg_Status,
    r.Notes as Reg_Notes,
    r.Sig_Card,
    r.Pamphlet,
    r.Referral,
    ifnull(c.idVehicle,0) as idVehicle,
    ifnull(c.Make,'') as Make,
    ifnull(c.Model,'')as Model,
    ifnull(c.Color,'') as Color,
    ifnull(c.State_Reg,'') as State_Reg
from
    registration r
        left join
    vehicle c ON r.idRegistration = c.idRegistration;




-- -----------------------------------------------------
-- View `vreservation_events`
-- -----------------------------------------------------
CREATE or Replace VIEW `vreservation_events` AS
    select 
        `r`.`idReservation` AS `idReservation`,
        `r`.`idResource` AS `idResource`,
        `r`.`Status` AS `Status`,
        `r`.`Expected_Arrival` AS `Arrival_Date`,
        `r`.`Expected_Departure` AS `Expected_Departure`,
        `r`.`idGuest` AS `idGuest`,
        `r`.`Number_Guests` AS `Number_Guests`,
        `r`.`Room_Rate_Category` AS `Rate`,
        `r`.`idRoom_rate`,
        ifnull(`n`.`Name_Full`, '') AS `Guest Name`,
        ifnull(`n`.`Name_First`, '') AS `Guest First`,
        ifnull((case when n.Name_Suffix = '' then n.Name_Last else concat(n.Name_Last, ' ', gs.Description) end), '') AS `Guest Last`,
        ifnull(`n2`.`Name_Full`, '') AS `Patient Name`,
        ifnull(`hs`.`idHospital`, 0) AS `idHospital`,
        case when ifnull(hs.idAssociation, 0) > 0 and h.Title = '(None)' then 0 else ifnull(hs.idAssociation, 0) end
         as `idAssociation`,
	ifnull(gl.Description, '') as `Location`,
        ifnull(`re`.`Title`, '') AS `Room Title`,
        r.idRegistration,
        r.Confirmation,
        r.Expected_Pay_Type,
        ifnull(rg.idPsg, 0) as `idPsg`
    from
        `reservation` `r`
            left join
        `hospital_stay` `hs` ON `r`.`idHospital_Stay` = `hs`.`idHospital_stay`
            left join
        `hospital` `h` on hs.idAssociation = h.idHospital
            left join
        resource re ON r.idResource = re.idResource
            left join
        `name` `n` ON `r`.`idGuest` = `n`.`idName`
            left join
        registration rg ON r.idRegistration = rg.idRegistration
            left join 
        `name` n2 ON hs.idPatient = n2.idName
            left join
        gen_lookups gs on gs.`Table_Name` = 'Name_Suffix' and gs.`Code` = n.Name_Suffix
            left join
        gen_lookups gl on gl.`Table_Name` = 'Location' and gl.`Code` = hs.Location;




-- -----------------------------------------------------
-- View `vresv_guest`
-- -----------------------------------------------------
CREATE or replace VIEW `vresv_guest` AS
select 
    ng.idPsg,
    rg.idGuest as `idGuest`,
    rg.idReservation,
    r.Expected_Arrival,
    r.Expected_Departure,
    r.Actual_Arrival,
    r.`Status`
from reservation_guest rg
	left join
    `name_guest` ng ON rg.idGuest = ng.idName
        left join
    reservation r on rg.idReservation = r.idReservation
where r.`Status` in ('a', 'uc', 's', 'w');



-- -----------------------------------------------------
-- View `vreservation_guests`
-- -----------------------------------------------------
create or replace view `vreservation_guests` as
    select 
        r.idReservation, r.idGuest, ng.idPsg, n.Name_Full, np.Phone_Num, r.`Primary_Guest`, g.Description as `Relationship_Code`
    from
        reservation_guest r
            left join
        `name` n ON r.idGuest = n.idName
            left join
        `name_phone` np ON r.idGuest = np.idName and n.Preferred_Phone = np.Phone_Code
            left join
        name_guest ng on r.idGuest = ng.idName
            left join
        gen_lookups g on g.Table_Name = 'Patient_Rel_Type' and g.Code = ng.Relationship_Code;



-- -----------------------------------------------------
-- View `vreservation_log`
-- -----------------------------------------------------
create or replace view vreservation_log as
select
	l.Sub_Type,
	l.User_Name,
	l.idName,
	l.idReservation,
	l.idRoom_Rate,
	l.idAgent,
	l.idResource,
	l.Log_Text,
	l.Timestamp,
	n.Name_First,
	n.Name_Last,
	n2.Name_First as `Agent_First`,
	n2.Name_Last as `Agent_Last`,
	r.Title
from reservation_log l left join name n on l.idName = n.idName
	left join name n2 on l.idAgent = n2.idname
	left join resource r on l.idResource = r.idResource
where l.log_Type = 'reservation'
order by l.idReservation, l.Timestamp;




-- -----------------------------------------------------
-- View `vresources_listing`
-- -----------------------------------------------------
Create or replace view vresources_listing as
select
    re.idResource as `idRes`,
    re.`Title`,
    sum(g.Substitute) as `Rate`,
    sum(g2.Substitute) as `Key_Deposit`,
    re.`Type` as `Resource_Type`,
    re.`Category`,
    re.Util_Priority,
    re.Status,
    re.Background_Color,
    re.Text_Color,
    re.Border_Color,
    case
        when re.Type = 'part' then re.Partition_Size
        else sum(r.Max_Occupants)
    end as Max_Occupants,
    count(r.idRoom) as Rooms
from
    room r
        join
    resource_room rr ON r.idRoom = rr.idRoom
        join
    resource re ON rr.idResource = re.idResource
        left join
    gen_lookups g ON g.Table_Name = 'Static_Room_Rate' and g.Code = r.Rate_Code
        left join
    gen_lookups g2 ON g2.Table_Name = 'Key_Deposit_Code' and g2.Code = r.Key_Deposit_Code
group by re.idResource
order by re.Util_Priority;





-- -----------------------------------------------------
-- View `vresources_ready`
-- -----------------------------------------------------
CREATE or replace VIEW `vresources_ready` AS
    select
        `re`.`idResource` AS `idRes`,
        `re`.`Title` AS `Title`,
    sum(g.Substitute) as `Rate`,
    sum(g2.Substitute) as `Key_Deposit`,
        `re`.`Type` AS `Resource_Type`,
        (case
            when (`re`.`Type` = 'part') then `re`.`Partition_Size`
            else sum(`r`.`Max_Occupants`)
        end) AS `Max_Occupants`,
        count(`r`.`idRoom`) AS `Rooms`
    from
        ((`room` `r`
        join `resource_room` `rr` ON ((`r`.`idRoom` = `rr`.`idRoom` and r.`Availability` = 'a' and r.`State` = 'a')))
        join `resource` `re` ON ((`rr`.`idResource` = `re`.`idResource`)))
        left join
    gen_lookups g ON g.Table_Name = 'Static_Room_Rate' and g.Code = r.Rate_Code
        left join
    gen_lookups g2 ON g2.Table_Name = 'Key_Deposit_Code' and g2.`Code` = r.`Key_Deposit_Code`
    where re.`Status` = 'a'  and
        (case
            when
                (`re`.`Type` = 'part')
            then
                (`re`.`Partition_Size` <= (`r`.`Max_Occupants` - (select
                        count(`stays`.`idRoom`)
                    from
                        `stays`
                    where
                        ((`stays`.`idRoom` = `rr`.`idRoom`)
                            and (`stays`.`Status` = 'a')))))
            else (not (`rr`.`idRoom` in (select
                    `stays`.`idRoom`
                from
                    `stays`
                where
                    (`stays`.`Status` = 'a'))))
        end)
    group by `re`.`idResource`
    having (case
        when
            (`Resource_Type` = 'block')
        then
            (`Rooms` = (select
                    count(`resource_room`.`idRoom`)
                from
                    `resource_room`
                where
                    (`resource_room`.`idResource` = `idRes`)))
        else (`Rooms` > 0)
    end)
    order by `re`.`Type` desc , `re`.`Util_Priority`;




-- -----------------------------------------------------
-- View `vresv_patient`
-- -----------------------------------------------------
CREATE or replace VIEW `vresv_patient` AS
        select 
        `r`.`idReservation` AS `idReservation`,
        `r`.`idRegistration` AS `idRegistration`,
        `r`.`idGuest` AS `idGuest`,
        `r`.`idHospital_Stay` AS `idHospital_Stay`,
        `r`.`idResource` AS `idResource`,
        `r`.`Resource_Suitable` AS `Resource_Suitable`,
        `r`.`Room_Rate_Category` AS `Room_Rate_Category`,
        `r`.`Fixed_Room_Rate` AS `Fixed_Room_Rate`,
        `r`.`Rate_Adjust` AS `Rate_Adjust`,
        `r`.`Visit_Fee` AS `Visit_Fee`,
        `r`.`idRoom_rate` AS `idRoom_rate`,
        `r`.`Confirmation` AS `Confirmation`,
        ifnull(`re`.`Title`, '') AS `Title`,
        `r`.`Type` AS `Type`,
        `r`.`Expected_Pay_Type` AS `Expected_Pay_Type`,
        `r`.`Expected_Arrival` AS `Expected_Arrival`,
        `r`.`Expected_Departure` AS `Expected_Departure`,
        `r`.`Actual_Arrival` AS `Actual_Arrival`,
        `r`.`Actual_Departure` AS `Actual_Departure`,
        `r`.`Number_Guests` AS `Number_Guests`,
        `r`.`Add_Room` AS `Add_Room`,
        `r`.`Notes` AS `Notes`,
        `r`.`Checkin_Notes` AS `Checkin_Notes`,
        `r`.`Status` AS `Status`,
        `r`.`Updated_By` AS `Updated_By`,
        `r`.`Last_Updated` AS `Last_Updated`,
        `r`.`Timestamp` AS `Timestamp`,
        ifnull(`n`.`Name_Full`, '') AS `Patient_Name`,
        ifnull(`n`.`idName`, 0) AS `idPatient`,
        ifnull(`h`.`idPsg`, 0) AS `idPsg`
    from
        `reservation` `r`
        left join `hospital_stay` `h` ON `r`.`idHospital_Stay` = `h`.`idHospital_stay`
        left join resource re on r.idResource = re.idResource
        left join `name` `n` ON `h`.`idPatient` = `n`.`idName`;




-- -----------------------------------------------------
-- View `vroom_listing`
-- -----------------------------------------------------
create or replace view vroom_listing as
SELECT
    r.idRoom as `Room Id`,
    r.Title,
    ifnull(r.Description, '') as Description,
    r.Type,
    r.Util_Priority as Priority,
    r.Status,
    ifnull(l.Title, '') as Location,
    ifnull(v.Name_Last, '') as Owner,
    g.Description as Rate,
    ifnull(r.Last_Updated, '') as `Last Updated`,
    r.Updated_By as `Updated By`
FROM
    room r
        left join
    location l ON r.idLocation = l.idLocation
        left join
    vmember_listing v ON r.Owner_id = v.Id
        left join
    gen_lookups g ON g.Table_Name = 'Rate_Code' and g.Code = r.Rate_Code
order by r.Util_Priority,r.Title;




-- -----------------------------------------------------
-- View `vshells`
-- -----------------------------------------------------
CREATE or replace VIEW `vshells` AS
select
    s.Title AS Title,
    s.Description AS Description,
    gs.Description AS Status,
    gcat.Description AS Category,
    gcod.Description AS Type,
    s.Shell_Color AS `Shell Color`,
    ifnull(DATE_FORMAT(s.Time_Start, '%h:%i:%s %p'),
            '') AS `Time Start`,
    ifnull(DATE_FORMAT(s.Time_End, '%h:%i:%s %p'),
            '') AS `Time End`,
    ifnull(DATE_FORMAT(s.Date_Start, '%m/%d/%Y'),
            '') AS `Date start`,
    s.Sun AS Sun,
    s.Mon AS Mon,
    s.Tue AS Tue,
    s.Wed AS Wed,
    s.Thu AS Thu,
    s.Fri AS Fri,
    s.Sat AS Sat,
    s.Skip_Holidays AS `Skip Holidays`,
    s.AllDay AS AllDay,
    s.Fixed_In_Time AS `Time Fixed`,
    s.Take_Overable AS `Take Over`,
    s.Locked AS Locked
from
    (((shell_events s
    left join gen_lookups gcat ON (((s.Vol_Cat = gcat.Code) and (gcat.Table_Name = 'vol_Category'))))
    left join gen_lookups gcod ON (((s.Vol_Code = gcod.Code) and (gcod.Table_Name = s.Vol_Cat))))
    left join gen_lookups gs ON (((s.Status = gs.Code) and (gs.Table_Name = 'E_Shell_Status'))))
order by gcat.Description, gcod.Description;


-- -----------------------------------------------------
-- View `vspan_listing`
-- -----------------------------------------------------
CREATE or replace VIEW `vspan_listing` AS
    select 
        `v`.`idVisit`,
        `v`.`Span`,
        `r`.`idPsg` AS `idPsg`,
        `r`.`Pref_Token_Id`,
        `v`.`idRegistration`,
        `v`.`idReservation`,
        `v`.`idResource`,
        `v`.`idHospital_stay`,
        `v`.`idPrimaryGuest`,
        `v`.`Arrival_Date`,
        `v`.`Key_Dep_Disposition`,
        `v`.`DepositPayType`,
        `v`.`Expected_Departure`,
        `v`.`Actual_Departure`,
        `v`.`Span_Start`,
        `v`.`Span_End`,
        (to_days(ifnull(`v`.`Actual_Departure`, now())) - to_days(`v`.`Arrival_Date`)) AS `Actual_Nights`,
        (to_days(`v`.`Expected_Departure`) - to_days(`v`.`Arrival_Date`)) AS `Expected_Nights`,
        (to_days(ifnull(`v`.`Span_End`, now())) - to_days(`v`.`Span_Start`)) AS `Actual_Span_Nights`,
        (to_days(`v`.`Expected_Departure`) - to_days(`v`.`Span_Start`)) AS `Expected_Span_Nights`,
        `v`.`Return_Date`,
        `v`.`Ext_Phone_Installed`,
        `v`.`OverRideMaxOcc`,
        v.Notes as `Visit_Notes`,
        ifnull(`p`.`Notes`, '') AS `Notes`,
        `v`.`Status`,
        ifnull(`g2`.`Description`, '') AS `Status_Title`,
        `v`.`Updated_By`,
        `v`.`Last_Updated`,
        ifnull(`re`.`Title`, '') AS `Title`,
        v.Expected_Rate,
        `v`.`Pledged_Rate`,
        `v`.`Rate_Category`,
        v.idRoom_rate,
        v.Rate_Glide_Credit,
        (case
            when
                (`v`.`Status` = 'a')
            then
                (select 
                        count(*)
                    from
                        `stays`
                    where
                        ((`stays`.`idVisit` = `v`.`idVisit`)
                            and (`stays`.`Status` = 'a')))
            else 0
        end) AS `Current_Guests`,
        (case
            when
                (`v`.`Status` = 'a')
            then
                (select 
                        sum(On_Leave)
                    from
                        `stays`
                    where
                        ((`stays`.`idVisit` = `v`.`idVisit`)
                            and (`stays`.`Status` = 'a')))
            else 0
        end) AS `On_Leave`,
        ifnull(`g1`.`Description`, '') AS `Key_Disposition_Title`,
        ifnull(n.Name_Full, '') as `Patient_Name`,
	ifnull(hs.idHospital, 0) as `idHospital`,
	ifnull(hs.idAssociation, 0) as `idAssociation`
    from
        `visit` `v`
        left join `resource` `re` ON `v`.`idResource` = `re`.`idResource`
        left join `registration` `r` ON `v`.`idRegistration` = `r`.`idRegistration`
        left join `hospital_stay` `hs` ON `v`.`idHospital_stay` = `hs`.`idHospital_stay`
	left join psg p on hs.idPsg = p.idPsg
        left join `name` n on hs.idPatient = n.idName
        left join `gen_lookups` `g1` ON `g1`.`Table_Name` = 'Key_Disposition'
            and `g1`.`Code` = `v`.`Key_Dep_Disposition`
        left join `gen_lookups` `g2` ON `g2`.`Table_Name` = 'Visit_Status'
            and `g2`.`Code` = `v`.`Status`;



-- -----------------------------------------------------
-- View `vstays_listing`
-- -----------------------------------------------------
create or replace view vstays_listing as
select
    s.idName,
    s.idStays,
    s.idVisit,
    s.Visit_Span,
    s.idRoom,
    ifnull(r.Title,'') as `Room`,
    (case when m.Name_Nickname = '' then m.Name_First else m.Name_Nickname end) as Name_First,
    (case when m.Name_Suffix = '' then m.Name_Last else concat(m.Name_Last, ' ', m.Name_Suffix) end) as `Name_Last`,
    s.Status,
    v.Status as `Visit_Status`,
    v.idRegistration as `idRegistration`,
    v.Key_Dep_Disposition as `Key_Dep_Disposition`,
    v.DepositPayType as DepositPayType,
    g.Description as `Status_Title`,
    s.Checkin_Date,
    s.Checkout_Date,
    s.Expected_Co_Date,
    s.Span_Start_Date,
    s.Span_End_Date,
    s.On_Leave,
    case when s.Status = 'co' then 'transparant' else ifnull(re.Background_Color, 'white') end as backgroundColor,
    case when s.Status = 'co' then 'black' else ifnull(re.Text_Color, 'black') end as textColor,
    'black' as borderColor,
    rg.idPsg,
    ng.Relationship_Code
from
    stays s left join vmember_listing m ON s.idName = m.Id
            left join visit v on s.idVisit = v.idVisit and s.Visit_Span = v.Span
            left join room r on s.idRoom = r.idRoom
            left join resource re on re.idResource = v.idResource
            left join registration rg on v.idRegistration = rg.idRegistration
            left join name_guest ng on s.idName = ng.idName and rg.idPsg = ng.idPsg
            left join gen_lookups g on s.Status = g.`Code` and g.`Table_Name` = 'Visit_Status';




-- -----------------------------------------------------
-- View `vstays_log`
-- -----------------------------------------------------
create or replace view vstays_log as
select
	l.Sub_Type,
	l.User_Name,
	l.idName,
	l.idVisit,
	l.Span,
	l.idStay,
	l.idRr,
	l.Log_Text,
	l.Timestamp,
	n.Name_First,
	n.Name_Last,
	r.Title,
        rg.idPsg
from visit_log l left join name n on l.idName = n.idName
	left join room r on l.idRr = r.idRoom
        left join registration rg on l.idRegistration = rg.idRegistration
where l.log_Type = 'stay'
order by l.idVisit, l.Span, l.idStay, l.Timestamp;



-- -----------------------------------------------------
-- View `vvisit_listing`
-- -----------------------------------------------------
CREATE or replace VIEW `vvisit_listing` AS
select
    v.idVisit,
    v.Span,
    r.idPsg,
        `r`.`Pref_Token_Id`,
    v.idRegistration,
    v.idReservation,
    v.idResource,
    v.idHospital_stay,
    v.idPrimaryGuest,
    v.Arrival_Date,
    v.Key_Dep_Disposition,
    v.DepositPayType,
    v.Expected_Departure,
    v.Actual_Departure,
    v.Span_Start,
    v.Span_End,
    DATEDIFF(ifnull(v.Actual_Departure, now()), v.Arrival_Date) as `Actual_Nights`,
    DATEDIFF(v.Expected_Departure, v.Arrival_Date) as `Expected_Nights`,
    DATEDIFF(ifnull(v.Span_End, now()), v.Span_Start) as `Actual_Span_Nights`,
    DATEDIFF(v.Expected_Departure, v.Span_Start) as `Expected_Span_Nights`,
    v.Return_Date,
    v.Ext_Phone_Installed,
    v.OverRideMaxOcc,
    p.Notes,
    v.Status,
    g2.Description as `Status_Title`,
    v.Updated_By,
    v.Last_Updated,
    re.Title,
    v.Pledged_Rate,
    v.Rate_Category,
    v.idRoom_rate,
    rv.Visit_Fee,
    v.Rate_Glide_Credit,
    case when v.Status = 'a' then (select count(*) from stays where idVisit = v.idVisit and Status = 'a') else 0 end as `Current_Guests`,
    ifnull(g1.Description, '') as `Key_Disposition_Title`
from
    visit v
        left join reservation rv on v.idReservation = rv.idReservation
        left join
    resource re ON v.idResource = re.idResource
        left join
    registration r ON v.idRegistration = r.idRegistration
        left join
    psg p on r.idPsg = p.idPsg
        left join
    gen_lookups g1 ON g1.Table_Name = 'Key_Disposition'
        and g1.Code = v.Key_Dep_Disposition
        left join
    gen_lookups g2 ON g2.Table_Name = 'Visit_Status'
        and g2.Code = v.Status
where v.Span = (select max(Span) from visit where visit.idVisit = v.idVisit);


-- -----------------------------------------------------
-- View `vvisit_patient`
-- -----------------------------------------------------
CREATE OR REPLACE VIEW vvisit_patient AS
    SELECT 
        v.idReservation,
        v.idPrimaryGuest,
        v.Arrival_Date,
        v.Expected_Departure,
        r.Title as `Room`,
        hs.idPsg,
        hs.idPatient,
        n.Name_Full AS `Patient_Name`,
        (SELECT 
                COUNT(*)
            FROM
                stays
            WHERE
                idVisit = v.idVisit
                    AND Visit_Span = v.Span
                    AND `Status` = 'a') AS `NumberGuests`
    FROM
        visit v
            LEFT JOIN
        resource r on v.idResource = r.idResource
            LEFT JOIN
        hospital_stay hs ON v.idHospital_stay = hs.idHospital_stay
            LEFT JOIN
        name n ON hs.idPatient = n.idName
    WHERE
        v.`Status` = 'a';



-- -----------------------------------------------------
-- View `vvisit_stmt`
-- -----------------------------------------------------
CREATE or replace VIEW `vvisit_stmt` AS
select
    v.idVisit,
    v.Span,
    v.idRegistration,
    v.idPrimaryGuest,
    v.idResource,
    ifnull(v.Arrival_Date, '') as `Arrival_Date`,
    ifnull(v.Expected_Departure, '') as `Expected_Departure`,
    ifnull(v.Actual_Departure, '') as `Actual_Departure`,
    ifnull(v.Span_Start, '') as `Span_Start`,
    ifnull(v.Span_End, '') as `Span_End`,
    v.`Status`,
    v.Rate_Glide_Credit,
    ifnull(rm.Title, '') as `Title`,
    ifnull(g.Substitute, 0) as Deposit_Amount,
    v.DepositPayType,
    v.Pledged_Rate,
    v.Rate_Category,
    v.idRoom_rate,
    v.Expected_Rate,
    rv.Visit_Fee as `Visit_Fee_Amount`,
    DATEDIFF(ifnull(v.Span_End, now()), v.Span_Start) as `Actual_Span_Nights`,
    ifnull(hs.idPsg, 0) as `idPsg`,
    ifnull(hs.idHospital, 0) as `idHospital`,
    ifnull(hs.idAssociation, 0) as `idAssociation`,
    (SELECT SUM(DATEDIFF(IFNULL(s.Span_End_Date, NOW()), s.Span_Start_Date))
        FROM stays s
        where s.idVisit = v.idVisit AND s.Visit_Span = v.Span)
     as `Guest_Nights`
from
    visit v
        left join
    reservation rv on v.idReservation = rv.idReservation
        left join
    hospital_stay hs on v.idHospital_stay = hs.idHospital_stay
        left join
    resource_room re ON v.idResource = re.idResource
        left join
    room rm on re.idRoom = rm.idRoom
        left join
    gen_lookups g on g.`Table_Name` = 'Key_Deposit_Code' and g.`Code` = rm.Key_Deposit_Code;





-- -----------------------------------------------------
-- View `vvol_categories2`
-- -----------------------------------------------------
CREATE or replace VIEW `vvol_categories2` AS
select `vm`.`Id` AS `Id`,
(case when (`vm`.`MemberRecord` = 1) then `vm`.`Name_Last` else `vm`.`Company` end) AS `Name_Last`,
(case when (`vm`.`MemberRecord` = 1) then `vm`.`Name_First` else '' end) AS `Name_First`,
`nv`.`Vol_Code` AS `Vol_Code`,
`gc`.`Description` AS `Category`,
`nv`.`Vol_Category` AS `Category_Code`,
(case when (ifnull(`g`.`Code`,'') <> '') then ifnull(`g`.`Description`,'') end) AS `Description`,
`nv`.`Vol_Status` AS `Vol_Status`,
(case when (`vm`.`Member_Type` = 'ai') then `vm`.`Fullname` when (`vm`.`Member_Type` <> 'ai') then `vm`.`Company` else '' end) AS `Name_Full`,
`vm`.`Preferred_Phone` AS `PreferredPhone`,
`vm`.`Preferred_Email` AS `PreferredEmail`,
concat_ws(' ',`vm`.`Address_1`,`vm`.`Address_2`,`vm`.`City`,`vm`.`StateProvince`,`vm`.`PostalCode`) AS `Full_Address`,
concat_ws(' ',`vm`.`Address_1`,`vm`.`Address_2`) AS `Address`,
`vm`.`City` AS `City`,
`vm`.`StateProvince` AS `State`,
`vm`.`PostalCode` AS `Zip`,
ifnull(`d`.`Title`,'') AS `Title`,
cast(`d`.`Begin_Active` as date) AS `Begin_Active`,
cast(`d`.`End_Active` as date) AS `End_Active`,
ifnull(`nv`.`Vol_Notes`,'') AS `Vol_Notes`,
`vm`.`Member_Type` AS `Member_Type`,
`nv`.`Vol_Begin` AS `Vol_Begin`,
`nv`.`Vol_End` AS `Vol_End`,
ifnull(`gr`.`Description`,'') AS `Vol_Rank`,
`nv`.`Vol_Check_Date` AS `Check_Date`,
`nv`.`Vol_Rank` AS `Vol_Rank_Code`
from (((((`vmember_listing` `vm` join `name_volunteer2` `nv` on(((`vm`.`Id` = `nv`.`idName`) and (`vm`.`MemberStatus` = 'a'))))
left join `dormant_schedules` `d` on(((`nv`.`Dormant_Code` = `d`.`Code`) and (`d`.`Status` = 'a'))))
left join `gen_lookups` `g` on(((`nv`.`Vol_Code` = `g`.`Code`) and (`g`.`Table_Name` = `nv`.`Vol_Category`))))
left join `gen_lookups` `gr` on(((`nv`.`Vol_Rank` = `gr`.`Code`) and (`gr`.`Table_Name` = 'Vol_Rank'))))
left join `gen_lookups` `gc` on(((`nv`.`Vol_Category` = `gc`.`Code`) and (`gc`.`Table_Name` = 'Vol_Category'))));




-- -----------------------------------------------------
-- View `vvol_checkdates`
-- -----------------------------------------------------
CREATE or replace VIEW `vvol_checkdates` AS
select `vc`.`Id` AS `Id`,
`vc`.`Check_Date` AS `Check Date`,
`vc`.`Category` AS `Category`,
`vc`.`Description` AS `Description`,
`vc`.`Vol_Notes` AS `Notes`,
`vc`.`Name_Last` AS `Last Name`,
`vc`.`Name_First` AS `First Name`,
`vc`.`PreferredPhone` AS `Phone`,
`vc`.`Address` AS `Address`,
`vc`.`City` AS `City`,
`vc`.`State` AS `State`,
`vc`.`Zip` AS `Zip`,
`vc`.`PreferredEmail` AS `Email`
from `vvol_categories2` `vc`
where ((`vc`.`Vol_Status` = 'a') and (`vc`.`Check_Date` is not null))
union select `vd`.`Id` AS `Id`,
`nd`.`Contact_Date` AS `Check Date`,
'General' AS `Category`,
'General Notes' AS `Description`,
ifnull(`nd`.`Gen_Notes`,'') AS `Notes`,
(case when (`vd`.`MemberRecord` = 1) then `vd`.`Name_Last` else `vd`.`Company` end) AS `Last Name`,
(case when (`vd`.`MemberRecord` = 1) then `vd`.`Name_First` else '' end) AS `First Name`,
`vd`.`Preferred_Phone` AS `Phone`,
concat_ws(' ',`vd`.`Address_1`,`vd`.`Address_2`) AS `Address`,
`vd`.`City` AS `City`,
`vd`.`StateProvince` AS `State`,
`vd`.`PostalCode` AS `Zip`,
`vd`.`Preferred_Email` AS `Email`
from (`name_demog` `nd` join `vmember_listing` `vd` on(((`nd`.`idName` = `vd`.`Id`) and (`nd`.`Contact_Date` is not null))));



-- -----------------------------------------------------
-- View `vwaitlist`
-- -----------------------------------------------------
create or replace view vwaitlist as
select
  `w`.`idWaitlist`,
  ifnull(vp.Name_Last, `w`.`Patient_Last`) as `Patient_Last`,
  ifnull((case when vp.Name_Nickname = '' then vp.Name_First else vp.Name_Nickname end), `w`.`Patient_First`) as `Patient_First`,
  `w`.`idGuest`,
  `w`.`idPatient`,
  `w`.`Hospital`,
  ifnull(v.Name_Last, `w`.`Guest_Last`) as `Guest_Last`,
  ifnull((case when v.Name_Nickname = '' then v.Name_First else v.Name_Nickname end), `w`.`Guest_First`) as `Guest_First`,
  `w`.`Phone`,
  `w`.`Email`,
  `w`.`Number_Adults`,
  `w`.`Number_Children`,
  `w`.`Arrival_Date`,
  `w`.`Expected_Duration`,
  `w`.`Notes`,
  `w`.`Status`,
  `w`.`Final_Status`,
  `w`.`Final_Status_Date`,
  `w`.`Timestamp`,
  ifnull(g.`Description`, '') as `Status_Title`,
  ifnull(g1.`Description`,'') as `Final_Status_Title`,
  ifnull(g2.`Description`,'') as `Hospital_Title`
from waitlist w
    left join vmember_listing v on `w`.`idGuest` = v.Id
    left join vmember_listing vp on `w`.`idPatient` = vp.Id
    left join `gen_lookups` g on g.`Table_Name` = 'WL_Status' and g.`Code` = `w`.`Status`
    left join `gen_lookups` g1 on g1.`Table_Name` = 'WL_Final_Status' and g1.`Code` = `w`.`Final_Status`
    left join `gen_lookups` g2 on g2.`Table_Name` = 'Hospitals' and g2.`Code` = `w`.`Hospital`;





-- -----------------------------------------------------
-- View `vweb_users`
-- -----------------------------------------------------
CREATE or replace VIEW `vweb_users` AS
select
    v.Id AS Id,
    concat_ws(' ', v.Name_First, v.Name_Last) AS Name,
    u.User_Name AS Username,
    gs.Description AS Status,
    gr.Description AS Role,
    wg.Title as `Authorization Code`,
    u.Last_Login AS `Last Login`,
    u.Updated_By AS `Updated By`,
    DATE_FORMAT(u.Last_Updated, '%m/%d/%Y') AS `Last Updated`
from
    ((((w_users u
    left join vmember_listing v ON ((u.idName = v.Id)))
    left join w_auth a ON ((u.idName = a.idName)))
	left join id_securitygroup s on s.idName = u.idName
	left join w_groups wg on s.Group_Code = wg.Group_Code
    left join gen_lookups gr ON (((a.Role_Id = gr.Code) and (gr.Table_Name = 'Role_Codes'))))
    left join gen_lookups gs ON (((u.Status = gs.Code) and (gs.Table_Name = 'Web_User_Status'))));




-- -----------------------------------------------------
-- View `vweb_volunteers`
-- -----------------------------------------------------
CREATE or replace VIEW `vweb_volunteers` AS
select `u`.`idName` AS `Id`,
(case when isnull(`n`.`Name_First`) then `f`.`fb_First_Name` else `n`.`Name_First` end) AS `First`,
(case when isnull(`n`.`Name_Last`) then `f`.`fb_Last_Name` else `n`.`Name_Last` end) AS `Last`,
`f`.`PIFH_Username` AS `Username`,
`gf`.`Description` AS `Status`,
(case when (`f`.`Access_Code` = 'web') then 'Web' else 'Facebook' end) AS `Access`,
`gr`.`Description` AS `Role`,
`f`.`fb_Phone` AS `Phone`,
`f`.`fb_Email` AS `Email`,
`u`.`Last_Login` AS `Last Login`,
`u`.`Updated_By` AS `Updated By`,
`u`.`Last_Updated` AS `Last Updated`
from ((((((`fbx` `f` left join `w_users` `u` on((`f`.`idName` = `u`.`idName`)))
left join `name` `n` on((`f`.`idName` = `n`.`idName`)))
left join `w_auth` `a` on((`f`.`idName` = `a`.`idName`)))
left join `gen_lookups` `gr` on(((`a`.`Role_Id` = `gr`.`Code`) and (`gr`.`Table_Name` = 'Role_Codes'))))
left join `gen_lookups` `gs` on(((`u`.`Status` = `gs`.`Code`) and (`gs`.`Table_Name` = 'Web_User_Status'))))
left join `gen_lookups` `gf` on(((`f`.`Status` = `gf`.`Code`) and (`gf`.`Table_Name` = 'FB_Status'))))
where (`n`.`idName` > 0);



