<?php
namespace HHK\ Tables\VolCalendar;

use HHK\Tables\AbstractTableRS;
use HHK\Tables\Fields\{DB_Field, DbIntSanitizer, DbStrSanitizer, DbDateSanitizer, DbBitSanitizer};

/**
 * McalendarRS.php
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2017 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */

class McalendarRS extends AbstractTableRS {
    
    public $idmcalendar;  // int(11) NOT NULL AUTO_INCREMENT,
    public $E_Id;  // varchar(100) NOT NULL,
    public $idName;  // int(11) NOT NULL DEFAULT '0',
    public $idName2;  // int(11) NOT NULL DEFAULT '0',
    public $E_Title;  // varchar(45) NOT NULL DEFAULT '',
    public $E_Start;  // datetime NOT NULL,
    public $E_End;  // datetime DEFAULT NULL,
    public $E_URL;  // varchar(145) NOT NULL DEFAULT '',
    public $E_ClassName;  // varchar(45) NOT NULL DEFAULT '',
    public $E_Editable;  // bit(1) NOT NULL DEFAULT b'0',
    public $E_Source;  // varchar(244) NOT NULL DEFAULT '',
    public $E_Description;  // text,
    public $E_AllDay;  // bit(1) NOT NULL DEFAULT b'0',
    public $E_Vol_Category;  // varchar(45) NOT NULL DEFAULT '',
    public $E_Vol_Code;  // varchar(45) NOT NULL DEFAULT '',
    public $E_Status;  // varchar(4) NOT NULL DEFAULT '',
    public $E_Take_Overable;  // bit(1) NOT NULL DEFAULT b'0',
    public $E_Fixed_In_Time;  // bit(1) NOT NULL DEFAULT b'0',
    public $E_Shell;  // bit(1) NOT NULL DEFAULT b'0',
    public $E_Locked;  // bit(1) NOT NULL DEFAULT b'0',
    public $E_Shell_Id;  // int(11) NOT NULL DEFAULT '0',
    public $E_Rpt_Id;
    public $E_Show_All;
    
    public $Updated_By;
    public $Last_Updated;
    public $Timestamp;
    
    function __construct($TableName = "mcalendar") {
        
        $this->idmcalendar = new DB_Field("idmcalendar", 0, new DbIntSanitizer());
        $this->E_Id = new DB_Field("E_Id", "", new DbStrSanitizer(100));
        $this->idName = new DB_Field("idName", 0, new DbIntSanitizer());
        $this->idName2 = new DB_Field("idName2", 0, new DbIntSanitizer());
        $this->E_Title = new DB_Field("E_Title", "", new DbStrSanitizer(45));
        $this->E_Start = new DB_Field("E_Start", NULL, new DbDateSanitizer("Y-m-d H:i:s"));
        $this->E_End = new DB_Field("E_End", NULL, new DbDateSanitizer("Y-m-d H:i:s"));
        $this->E_URL = new DB_Field("E_URL", "", new DbStrSanitizer(145));
        $this->E_ClassName = new DB_Field("E_ClassName", "", new DbStrSanitizer(45));
        $this->E_Editable = new DB_Field("E_Editable", 0, new DbBitSanitizer());
        $this->E_Source = new DB_Field("E_Source", "", new DbStrSanitizer(244));
        $this->E_Description = new DB_Field("E_Description", "", new DbStrSanitizer(1000));
        $this->E_AllDay = new DB_Field("E_AllDay", 0, new DbBitSanitizer());
        $this->E_Vol_Category = new DB_Field("E_Vol_Category", "", new DbStrSanitizer(45));
        $this->E_Vol_Code = new DB_Field("E_Vol_Code", "", new DbStrSanitizer(45));
        $this->E_Status = new DB_Field("E_Status", "", new DbStrSanitizer(5));
        $this->E_Take_Overable = new DB_Field("E_Take_Overable", 0, new DbBitSanitizer());
        $this->E_Fixed_In_Time = new DB_Field("E_Fixed_In_Time", 0, new DbBitSanitizer());
        $this->E_Shell = new DB_Field("E_Shell", 0, new DbBitSanitizer());
        $this->E_Locked = new DB_Field("E_Locked", 0, new DbBitSanitizer());
        $this->E_Shell_Id = new DB_Field("E_Shell_Id", 0, new DbIntSanitizer());
        $this->E_Rpt_Id = new DB_Field("E_Rpt_Id", 0, new DbIntSanitizer());
        $this->E_Show_All = new DB_Field("E_Show_All", 0, new DbIntSanitizer());
        
        $this->Updated_By = new DB_Field("Updated_By", "", new DbStrSanitizer(45), FALSE);
        $this->Last_Updated = new DB_Field("Last_Updated", NULL, new DbDateSanitizer("Y-m-d H:i:s"), FALSE);
        $this->Timestamp = new DB_Field("Timestamp", NULL, new DbDateSanitizer("Y-m-d H:i:s"), FALSE);
        
        parent::__construct($TableName);
    }
}
?>