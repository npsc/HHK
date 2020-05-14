<?php
/**
 * WebInit.php
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2017 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */

/**
 * WebInit Class
 *
 * Web page or service initialization and authorization
 *
 */
class webInit {

    const SYS_CONFIG = 'sys_config';

    public $page;
    public $menuTitle;
    public $pageTitle = "";
    public $pageHeading = '';
    public $resourceURL;
    public $siteName;
    public $testVersion;

    public $dbh;

    function __construct($page_Type = WebPageCode::Page, $addCSP = TRUE) {

        SecurityComponent::rerouteIfNotLoggedIn($page_Type, 'index.php');

        // define db connection obj
        try {

            $this->dbh = initPDO(FALSE);

        } catch (Hk_Exception_Runtime $hex) {

            if ($page_Type == WebPageCode::Page) {
                echo('<h3>' . $hex->getMessage() . '; <a href="index.php">Continue</a></h3>');
            } else {
                $rtn = array("error" => $hex->getMessage());
                echo json_encode($rtn);
            }

            exit();
        }

        // Page authorization check
        $this->page = new ScriptAuthClass($this->dbh);

        $this->page->Authorize_Or_Die();

        // get session instance
        $uS = Session::getInstance();

        // Run as test?
        $this->testVersion = $uS->testVersion;
        $this->siteName = $uS->siteName;

        $this->menuTitle = $this->siteName;
        $this->pageTitle = $this->siteName;

        // Demo or training version?
        if ($uS->mode !== Mode::Live) {
            $this->menuTitle = $this->siteName . " " . ucfirst($uS->mode);
            $this->pageTitle = ucfirst($uS->mode) . " - " . $this->siteName;
        }

        /*
        * if test version, put a big TEST on the page
        */
        if ($this->testVersion !== FALSE) {
            $this->menuTitle = "TEST VERSION";
            $this->pageTitle = "TEST - " . $this->siteName;
        }


        $this->pageHeading = $this->page->get_Page_Title();

        $this->sessionLoadGenLkUps();
        $this->sessionLoadGuestLkUps();

        // set timezone
        date_default_timezone_set($uS->tz);

        // Check session timeout
        if (isset($uS->SessionTimeout) === FALSE || $uS->SessionTimeout < 1) {
            $uS->SessionTimeout = 30;
        }

        if (!isset($uS->timeout_idle)) {
            $uS->timeout_idle = time() + ($uS->SessionTimeout * 60);
        } else {
            if ($uS->timeout_idle < time()) {
                $uS->logged = FALSE;
                $this->page->die_if_not_Logged_In($page_Type, "index.php");
            } else {
                $uS->timeout_idle = time() + ($uS->SessionTimeout * 60);
            }
        }

        if ($addCSP) {
            $cspURL = $this->page->getHostName();
            header("Content-Security-Policy: "
                    . "default-src data: $cspURL https://online.instamed.com https://pay.instamed.com https://instamedprd.cachefly.net; "
                    . "script-src $cspURL https://online.instamed.com https://pay.instamed.com https://instamedprd.cachefly.net 'unsafe-inline' 'unsafe-eval'; "
                    . "style-src $cspURL https://online.instamed.com https://pay.instamed.com https://instamedprd.cachefly.net 'unsafe-inline';"
                    . "img-src $cspURL https://api.qrserver.com 'unsafe-inline';"
                    . "object-src $cspURL; "); // FF 23+ Chrome 25+ Safari 7+ Opera 19+

            header("X-Content-Security-Policy: "
                    . "default-src data: $cspURL https://online.instamed.com https://pay.instamed.com https://instamedprd.cachefly.net; "
                    . "script-src $cspURL https://online.instamed.com https://pay.instamed.com https://instamedprd.cachefly.net 'unsafe-inline'; "
                    . "style-src $cspURL https://online.instamed.com https://pay.instamed.com https://instamedprd.cachefly.net 'unsafe-inline'; "
                    . "img-src $cspURL https://api.qrserver.com 'unsafe-inline';"
                    . "object-src $cspURL;"); // IE 10+

            header('X-Frame-Options: DENY');

            if (SecurityComponent::isHTTPS()) {
                header('Strict-Transport-Security: max-age=31536000'); // FF 4 Chrome 4.0.211 Opera 12
            }
        }
    }


    public function logout($page = 'index.php') {

        $uS = Session::getInstance();
        $uS->destroy(TRUE);

        header( "Location: $page");
    }


    public function generatePageMenu() {
        // generate menu markup if page type = 'p'
        return $this->page->generateMenu($this->menuTitle, $this->dbh);

    }

    public function sessionLoadGenLkUps() {

        // get session instance
        $uS = Session::getInstance();

        // Load session if not already there
        if (isset($uS->nameLookups) === FALSE) {
            $this->reloadGenLkUps($uS);
        }
        return $uS->nameLookups;

    }

    public function reloadGenLkUps($uS) {

        $query = "select `Table_Name`, `Code`, `Description`, `Substitute` from `gen_lookups`
            where `Table_Name` in ('Address_Purpose','Email_Purpose','rel_type', 'NoReturnReason', 'Member_Basis','mem_status','Name_Prefix','Name_Suffix','Phone_Type', 'Pay_Type', 'Salutation', 'Role_Codes') order by `Table_Name`, `Code`;";
        $stmt = $this->dbh->query($query);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $nameLookups = array();

        foreach ($rows as $r) {
            $nameLookups[$r['Table_Name']][$r['Code']] = array($r['Code'],$r['Description'],$r['Substitute']);
        }

        // Demographics
        $demos = readGenLookupsPDO($this->dbh, 'Demographics', 'Order');

        foreach ($demos as $d) {

            $entries = readGenLookupsPDO($this->dbh, $d[0], 'Order');

            foreach ($entries as $e) {
                $nameLookups[$d[0]][$e['Code']] = array($e['Code'],$e['Description'],$e['Substitute']);
            }
        }

        $uS->nameLookups = $nameLookups;

        SysConfig::getCategory($this->dbh, $uS, "'a'", webInit::SYS_CONFIG);
        SysConfig::getCategory($this->dbh, $uS, "'d'", webInit::SYS_CONFIG);
        SysConfig::getCategory($this->dbh, $uS, "'es'", webInit::SYS_CONFIG);
        SysConfig::getCategory($this->dbh, $uS, "'f'", webInit::SYS_CONFIG);
        SysConfig::getCategory($this->dbh, $uS, "'fg'", webInit::SYS_CONFIG);
        SysConfig::getCategory($this->dbh, $uS, "'pr'", webInit::SYS_CONFIG);

        return $uS->nameLookups;

    }

    public function sessionLoadGuestLkUps() {

        // get session instance
        $uS = Session::getInstance();

        // Load session if not already there
        if (isset($uS->guestLookups) === FALSE) {
            $this->reloadSessionGuestLUs();

        }
        return $uS->guestLookups;

    }

    public function reloadSessionGuestLUs() {

        // get session instance
        $uS = Session::getInstance();

        SysConfig::getCategory($this->dbh, $uS, "'h'", webInit::SYS_CONFIG);
        SysConfig::getCategory($this->dbh, $uS, "'c'", webInit::SYS_CONFIG);
        SysConfig::getCategory($this->dbh, $uS, "'g'", webInit::SYS_CONFIG);

        $query = "select `Table_Name`, `Code`, `Description`, `Substitute` from `gen_lookups`
            where `Table_Name` in ('Patient_Rel_Type', 'Key_Deposit_Code', 'Room_Category', 'Static_Room_Rate', 'Room_Type', 'Resource_Type', 'Resource_Status', 'Room_Status', 'Visit_Status')
            UNION Select 'Hospitals' as `Table_Name`, `idHospital` as `Code`, `Title` as `Description`, `Type` as `Substitute` from hospital where `Status` ='a'
            UNION select `Category` as `Table_Name`, `Code`, `Title` as `Description`, `Other` as `Substitute` from `lookups` where `Show` = 'y'
            order by `Table_Name`, `Description`;";
        $stmt = $this->dbh->query($query);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $nameLookups = array();

        foreach ($rows as $r) {
            $nameLookups[$r['Table_Name']][$r['Code']] = array($r['Code'],$r['Description'],$r['Substitute']);
        }


        $uS->guestLookups = $nameLookups;

        return $uS->guestLookups;

    }

    public function sessionLoadVolLkUps() {

        // get session instance
        $uS = Session::getInstance();

        // Load session if not already there
        if (isset($uS->volLookups) === FALSE) {

            $this->reloadSessionVolLkUps();
        }

        return $uS->volLookups;

    }

    public function reloadSessionVolLkUps() {

        // get session instance
        $uS = Session::getInstance();

        $stmt = $this->dbh->query("select `Table_Name`, `Code`, `Description`, `Substitute` from `gen_lookups`
            where `Table_Name` in ('Vol_Category', 'Vol_Rank') order by `Table_Name`, `Description`;");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $nameLookups = array();

        foreach ($rows as $r) {
            $nameLookups[$r['Table_Name']][$r['Code']] = array($r['Code'],$r['Description'],$r['Substitute']);
        }

        $uS->volLookups = $nameLookups;

        SysConfig::getCategory($this->dbh, $uS, "'v'", webInit::SYS_CONFIG);
        return $uS->volLookups;

    }

}


class SysConfig {

    /** Load given category into the session.
     *
     * @param \PDO $dbh
     * @param \Session $uS
     * @param string $category
     * @param string $tableName
     * @throws Hk_Exception_Runtime
     */
    public static function getCategory(\PDO $dbh, \Session $uS, $category, $tableName)
    {

        if ($tableName == '' || $category == '') {
            throw new Hk_Exception_Runtime('System Configuration database table name or category not specified.  ');
        }

        $stmt = $dbh->query("select `Key`,`Value`,`Type` from `" . $tableName . "` where Category in ($category) order by `Key`");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as $r) {

            $val = self::getTypedVal($r['Type'], $r['Value']);
            $key = $r['Key'];
            $uS->$key = $val;
        }

        unset($rows);
        $stmt = NULL;

    }

    public static function getKeyValue(\PDO $dbh, $tableName, $key) {

        if ($tableName == '' || $key == '') {
            throw new Hk_Exception_Runtime('System Configuration database table name or key not specified.  ');
        }

        $stmt = $dbh->query("select `Value`,`Type` from `" . $tableName . "` where `Key` = '$key' ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) == 1) {
            return self::getTypedVal($rows[0]['Type'], $rows[0]['Value']);
        } else {
            throw new Hk_Exception_Runtime('System Configuration key not found: ' . $key);
        }

    }

    public static function saveKeyValue(\PDO $dbh, $tableName, $key, $value) {

        if ($tableName == '' || $key == '') {
            throw new Hk_Exception_Runtime('System Configuration database table name or key not specified.  ');
        }

        $stmt = $dbh->query("select `Value`,`Type` from `" . $tableName . "` where `Key` = '$key' ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) == 1) {

            $value = self::setValueByType($value, $rows[0]['Type']);

            $oldVal = $rows[0]['Value'];


            if ($oldVal != $value) {
                // Update table
                $query = "update `" . $tableName . "` set `Value` = :val where `Key` = :key";
                $stmt = $dbh->prepare($query);
                $stmt->execute(array(':val'=>$value, ':key'=>$key));

                $uS = Session::getInstance();
                $logText = $key . ':' .$oldVal . '|_|' . $value;
                HouseLog::logSysConfig($dbh, $key, $value, $logText, $uS->username);

            }
        } else {
            throw new Hk_Exception_Runtime('System Configuration key not found: ' . $key);
        }
    }

    public static function getTypedVal($type, $value) {

        switch ($type) {
            case 'i':
                $val = (int)$value;
                break;
            case 'b':
                $val = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;
            case 'ob':
                $val = decryptMessage($value);
                break;
            default:
                $val = $value;
        }

        return $val;
    }

    public static function setValueByType($value, $type) {

        switch ($type) {
            case 'i':
                $val = (int)$value;
                break;
            case 'b':
                $temp = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                if ($temp) {
                    $val = 'true';
                } else {
                    $val = 'false';
                }
                break;
            case 'ob':
                $val = encryptMessage($value);
                break;
            default:
                $val = $value;
        }

        return $val;
    }
}
