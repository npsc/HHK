<?php
/**
 * Patch.php
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2017 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */

/**
 * Description of Patch
 *
 * @author Eric
 */
class Patch {

    public $oldVersion = '';
    public $newVersion = '';

    public $results;

    public function __construct() {
        $this->results = array();
    }

    public function insertSiteConf(\PDO $dbh) {

        // load each section of the site.cfg file.
        $entryArray = array();

        $entryArray[] = array('site', 'Site_Name', 'siteName', 's', 'a');
        $entryArray[] = array('site', 'Site_Id', 'sId', 'i', 'a');

        $entryArray[] = array('email_server', 'Type', 'EmailType', 'lu', 'es');
        $entryArray[] = array('email_server', 'Host', 'SMTP_Host', 's', 'es');
        $entryArray[] = array('email_server', 'Port', 'SMTP_Port', 'i', 'es');
        $entryArray[] = array('email_server', 'Username', 'SMTP_Username', 's', 'es');
        $entryArray[] = array('email_server', 'Password', 'SMTP_Password', 'op', 'es');
        $entryArray[] = array('email_server', 'Auth_Required', 'SMTP_Auth_Required', 'b', 'es');
        $entryArray[] = array('email_server', 'Secure', 'SMTP_Secure', 's', 'es');
        $entryArray[] = array('email_server', 'Debug', 'SMTP_Debug', 'i', 'es');
        $entryArray[] = array('email_server', 'MaxAutoEmail', 'MaxAutoEmail', 'i', 'h');

        $entryArray[] = array('guest_email', 'FromAddress', 'FromAddress', 'ea', 'g');
        $entryArray[] = array('guest_email', 'BccAddress', 'BccAddress', 'ea', 'g');
        $entryArray[] = array('guest_email', 'ReplyTo', 'ReplyTo', 'ea', 'g');

        $entryArray[] = array('financial', 'RoomSubsidyId', 'subsidyId', 'i', 'f');
        $entryArray[] = array('financial', 'InvoiceTerm', 'InvoiceTerm', 'i', 'f');
        $entryArray[] = array('financial', 'CC_Gateway', 'ccgw', 'lu', 'fg');
        $entryArray[] = array('financial', 'BatchSettlementHour', 'BatchSettlementHour', 's', 'f');
        $entryArray[] = array('financial', 'PaymentDisclaimer', 'PaymentDisclaimer', 't', 'f');
        $entryArray[] = array('financial', 'PmtPageLogoUrl', 'PmtPageLogoUrl', 'url', 'fg');
        $entryArray[] = array('financial', 'receiptLogoFile', 'receiptLogoFile', 'url', 'f');
        $entryArray[] = array('financial', 'receiptLogoWidth', 'receiptLogoWidth', 'i', 'f');
        $entryArray[] = array('financial', 'statementLogoFile', 'statementLogoFile', 'url', 'f');
        $entryArray[] = array('financial', 'statementLogoWidth', 'statementLogoWidth', 'i', 'f');
        $entryArray[] = array('financial', 'StartGuestFeesYr', 'StartGuestFeesYr', 'i', 'h');

        $entryArray[] = array('calendar', 'TimeZone', 'tz', 'lu', 'a');

        $entryArray[] = array('house', 'NoReply', 'NoReplyAddr', 'ea', 'h');
        $entryArray[] = array('house', 'Admin_Address', 'Admin_Address', 'ea', 'h');
        $entryArray[] = array('house', 'Auto_Email_Address', 'Auto_Email_Address', 'ea', 'h');
        $entryArray[] = array('house', 'HouseKeepingEmail', 'HouseKeepingEmail', 'ea', 'h');
        $entryArray[] = array('house', 'Guest_Register_Email', 'Guest_Register_Email', 'ea', 'h');
        $entryArray[] = array('house', 'Zip_Code', 'Zip_Code', 's', 'h');

        $entryArray[] = array('recaptcha', 'HHK_Site_Key', 'HHK_Site_Key', 's', 'v');
        $entryArray[] = array('recaptcha', 'HHK_Secret_Key', 'HHK_Secret_Key', 's', 'v');

        $entryArray[] = array('vol_email', 'ReturnAddress ', 'ReturnAddress', 'ea', 'v');
        $entryArray[] = array('vol_email', 'RegSubj ', 'RegSubj', 's', 'v');
        $entryArray[] = array('vol_email', 'Disclaimer ', 'Disclaimer', 't', 'v');

        // Process the data
        $config = new Config_Lite(ciCFG_FILE);
        $titles = new Config_Lite(REL_BASE_DIR . 'conf' . DS . 'siteTitles.cfg');

        // Select all the values in sysConfig table
        $stmt = $dbh->query("select `Key` from `sys_config`");
        $sysConf = array();

        while ($k = $stmt->fetch(PDO::FETCH_NUM)) {
            $sysConf[$k[0]] = 1;
        }

        foreach ($entryArray as $r) {

            // already in the database
            if (isset($sysConf[$r[2]])) {
                continue;
            }

            // Only if the config file has this key
            if ($config->has($r[0], $r[1])) {

                $v = filter_var($config->getString($r[0], $r[1]), FILTER_SANITIZE_STRING);

                $c = $dbh->exec("REPLACE INTO `sys_config` (`Key`,`Value`,`Type`,`Category`,`Description`) VALUES "
                        . "('" .$r[2]. "', '$v', '" .$r[3]. "', '" .$r[4]. "', '" .$titles->getString($r[0], $r[1], ''). "')");

            }
        }

        // Load Registration document
        $instructionFileName = REL_BASE_DIR . 'conf'. DS . 'agreement.txt';

        $rstmt = $dbh->query("Select Code from gen_lookups where Table_Name = 'Reg_Agreement'");
        $rows = $rstmt->fetchAll(\PDO::FETCH_NUM);

        if (count($rows) == 0 && file_exists($instructionFileName)) {

            $doc = addslashes(file_get_contents($instructionFileName));

            $inctr = $dbh->exec("insert into document (`Title`, `Category`, `Type`, `Language`, `Doc`, `Status`, `Last_Updated`, `Updated_By`) "
                    . "Values ('Registration Form', 'form', 'html', 'en', '$doc', 'a', now(), 'patch')");

            if ($inctr > 0) {
                $dbh->exec("insert into gen_lookups (`Table_Name`, `Code`, `Description`, `Substitute`) "
                        . "VALUES ('Reg_Agreement', 'en', 'English', '$inctr')");
            }
        }

    }


    public function verifyUpLoad($zipFile, $versionFileName, $origBuild) {

        $fname = '..' . DS .'patch' . DS . 'patchVer.cfg';
        $fileSize = 0;
        $this->oldVersion = $origBuild;

        $zip = zip_open($zipFile);

        if (is_resource($zip)) {

            while (($entry = zip_read($zip)) !== false) {

                if (zip_entry_name($entry) == $versionFileName) {

                    // copy the new version in
                    $fileSize = file_put_contents($fname, zip_entry_read($entry, zip_entry_filesize($entry)));

                    zip_entry_close($entry);

                    if ($fileSize === false) {
                        zip_close($zip);
                        throw new Hk_Exception_Runtime("Unable to write patch version file.  ");
                    }

                    break;

                }
            }
        }

        zip_close($zip);


        if ($fileSize <= 0) {
            throw new Hk_Exception_Runtime("Patch file not found or empty.  ");
        }


        $siteCnf = new Config_Lite($fname);
        $newBuild = intval($siteCnf->getString('code', 'Build'), 10);
        $newVersion = $siteCnf->getString('code', 'Version');

        $this->newVersion = $newVersion . '.' . $newBuild;

        $newVersions = explode('.', $newVersion);

        if (count($newVersions) < 2) {
            throw new Hk_Exception_Runtime("New version not in proper format: " . $newVersion);
        }

        $origBuilds = explode('.', $origBuild);

        if (count($origBuilds) < 3) {
            throw new Hk_Exception_Runtime("Current site version not in proper format: " . $origBuild);
        }

        // Major version
        if (intval($newVersions[0], 10) < intval($origBuilds[0], 10)) {
            throw new Hk_Exception_Runtime("The major version of this update (" . $newVersions[0] . ") is lower than this site's major version (" . $origBuilds[0] . ").  ");
        }

        // Minor Version
        if (intval($newVersions[1], 10) < intval($origBuilds[1], 10)) {
            throw new Hk_Exception_Runtime("The minor version of this update (" . $newVersions[1] . ") is lower than this site's minor version (" . $origBuilds[1] . ").  ");
        }

        // Build Number
        if ($newBuild < intval($origBuilds[2], 10)) {
            throw new Hk_Exception_Runtime("The build number of this update (" . $newBuild . ") is lower than this site's build number (" . $origBuilds[2] . ").  ");
        }

    }

    public function loadFiles($fileRoot, $filePathName) {

        $result = "";

        $skipDirs = array('.git', 'install');

        self::deleteBakFiles($fileRoot);

         // Detect guest tracking subunit
        if (is_dir($fileRoot . "house") === FALSE) {
            $skipDirs[] = 'house';
        }
        // Detect volunteer subunit
        if (is_dir($fileRoot . "volunteer") === FALSE) {
            $skipDirs[] = 'volunteer';
        }

        // Renames existing files to *.bak and copies in new versions.
        $result .= $this->unzip($filePathName, $skipDirs);

        return $result;
    }

    public function updateWithSqlStmts(\PDO $dbh, $tfile, $type = '', $delimiter = ';', $splitAt = ';') {

        $this->results = array();

        if ($tfile == '') {
            return $type . ' Filename is missing.  ';
        }

        $tquery = file_get_contents($tfile);

        $tresult = self::multiQueryPDO($dbh, $tquery, $delimiter, $splitAt);

        if (count($tresult) > 0) {

            foreach ($tresult as $err) {
                $this->results[$err['errno']] = $err;
            }

        } else {
            return $type . ' Successful<br/>';
        }
    }

    public function loadConfigUpdates($configUpdateFile, Config_Lite $config) {

        if ($configUpdateFile == '') {
            return '';
        }

        if (file_exists($configUpdateFile) === FALSE) {
            return '';
        }

        $result = "";

        try {
            $cfupdates = new Config_Lite($configUpdateFile);
        } catch (Config_Lite_Exception_Runtime $ex) {
            $result = $ex->getMessage();
            return $result;
        }

        foreach ($cfupdates as $secName => $secArray) {

            foreach ($secArray as $itemName => $val) {

                // Only update if the target file is missing the section or item
                if ($config->has($secName, $itemName) === FALSE || $secName == 'code') {

                    $config->set($secName, $itemName, $val);
                    $result .= $secName . "." . $itemName . " = " . $val . "<br/>";
                }
            }
        }

        try {
            $config->save();

        } catch (Config_Lite_Exception_Runtime $ex) {

            $result .= $ex  . "<br/>";
        }

        return $result;
    }

    public static function deleteConfigItems($configDeleteFile, Config_Lite $config) {

        if ($configDeleteFile == '') {
            return '';
        }

        if (file_exists($configDeleteFile) === FALSE) {
            return '';
        }

        $result = "";

        try {
            $cfdeletes = new Config_Lite($configDeleteFile);
        } catch (Config_Lite_Exception_Runtime $ex) {
            $result = $ex->getMessage();
            return $result;
        }

        foreach ($cfdeletes as $secName => $secArray) {

            foreach ($secArray as $itemName => $val) {

                // Only update if the target file has the section or item
                if ($config->has($secName, $itemName) === TRUE) {

                    $config->remove($secName, $itemName);
                    $result .= $secName . "." . $itemName . " deleted<br/>";
                }
            }
        }

        try {
            $config->save();

        } catch (Config_Lite_Exception_Runtime $ex) {

            $result .= $ex  . "<br/>";
        }

        return $result;
    }

    public static function deleteBakFiles($directory, $oldExtension = 'bak') {

        $fit = new FilesystemIterator($directory, FilesystemIterator::UNIX_PATHS | FilesystemIterator::CURRENT_AS_FILEINFO);

        foreach ($fit as $fileinfo) {

            if ($fileinfo->isDir()) {

                self::deleteBakFiles($directory.$fileinfo->getFilename().DS, $oldExtension);

            } else {

                if ($fileinfo->getExtension() == $oldExtension) {
                    unlink($fileinfo->getRealPath());
                }
            }
        }
    }

    public static function deleteDirectory($directory) {

        $fit = new FilesystemIterator($directory, FilesystemIterator::UNIX_PATHS | FilesystemIterator::CURRENT_AS_FILEINFO);

        foreach ($fit as $fileinfo) {

            if ($fileinfo->isDir()) {

                self::deleteDirectory($directory.$fileinfo->getFilename().DS);
                unlink($fileinfo->getRealPath());

            } else {
                unlink($fileinfo->getRealPath());
            }
        }

        // Finally the top directory
        rmdir($directory);

    }

    protected function unzip($file, array $skipDirs, $rootDir = 'hhk', $oldExtension = 'bak') {

        $result = '';
        $this->results = array();

        $zip = zip_open($file);

        if (is_resource($zip)) {

            $colCounter = 0;
            $table = new HTMLTable();
            $tr = "";

            while (($entry = zip_read($zip)) !== FALSE) {


                if (strpos(zip_entry_name($entry), "/") !== FALSE) {

                    $last = strrpos(zip_entry_name($entry), "/");
                    $dir = substr(zip_entry_name($entry), 0, $last);
                    $file = substr(zip_entry_name($entry), strrpos(zip_entry_name($entry), "/") + 1);


                    // Not these files
                    $flag = FALSE;
                    foreach ($skipDirs as $d) {
                        if (stripos($dir, $d) !== false) {
                            $flag = true;
                        }
                    }

                    if ($flag) {
                        continue;
                    }

                    $relDir = str_ireplace($rootDir, '..', $dir);

                    if (strlen(trim($file)) > 0) {

                        // rename the existing file
                        if (file_exists($relDir . "/" . $file)) {
                            $renamedFile = $relDir . "/" . $file . '.' . $oldExtension;
                            rename($relDir . "/" . $file, $renamedFile);
                        }

                        // copy the new version in
                        try {
                            $fileSize = file_put_contents($relDir . "/" . $file, zip_entry_read($entry, zip_entry_filesize($entry)));
                        } catch (Exception $ex) {
                            $this->results[] = array('error'=>"Unable to put file: $relDir/$file" . " Msg: " . $ex->getMessage(), 'errno'=> '', 'query'=> '' );
                            continue;
                        }

                        if ($colCounter >= 2) {
                            $table->addBodyTr($tr);
                            $colCounter = 0;
                            $tr = '';
                        }

                        if ($fileSize === false) {
                            $tr .= HTMLTable::makeTd("File not written: $relDir/$file");
                            $this->results[] = array('error'=>"Unable to write file: $relDir/$file", 'errno'=> '', 'query'=> '' );
                        } else {
                            $tr .= HTMLTable::makeTd($relDir . "/" . $file);
                        }

                        $colCounter++;
                    }
                }
            }

            if ($tr != '') {
                $table->addBodyTr($tr);
            }

            $result = $table->generateMarkup();

        } else {
            throw new Hk_Exception_Runtime("Unable to open zip file.  ");
        }

        return $result;
    }

    public static function multiQueryPDO(\PDO $dbh, $query, $delimiter = ";", $splitAt = ';') {

        $msg = array();

        if ($query === FALSE || trim($query) == '') {
            return $msg[] = array('error'=>'Empty query file ', 'errno'=> '', 'query'=> $query );
        }

        $qParts = explode($splitAt, $query);

        foreach ($qParts as $q) {

            $q = trim($q);
            if ($q == '' || $q == $delimiter || $q == 'DELIMITER') {
                continue;
            }

            try {
                if ($dbh->exec($q) === FALSE) {
                    $msg[] = array('error'=>$dbh->errorInfo(), 'errno'=> $dbh->errorCode(), 'query'=> $q );
                }
            } catch (PDOException $pex) {
                // do nothing
            }
        }

        return $msg;
    }

    public static function patchTabMu() {

        $uS = Session::getInstance();

        // Database info
        $dbt = new HTMLTable();

        $dbt->addBodyTr(HTMLTable::makeTd('Database:', array('class' => 'tdlabel')) . HTMLTable::makeTd($uS->dbms));
        $dbt->addBodyTr(HTMLTable::makeTd('URL:', array('class' => 'tdlabel')) . HTMLTable::makeTd($uS->databaseURL));
        $dbt->addBodyTr(HTMLTable::makeTd('Schema:', array('class' => 'tdlabel')) . HTMLTable::makeTd($uS->databaseName));
        $dbt->addBodyTr(HTMLTable::makeTd('User:', array('class' => 'tdlabel')) . HTMLTable::makeTd($uS->databaseUName));

        $markup = HTMLContainer::generateMarkup('fieldset',
                HTMLContainer::generateMarkup('legend', 'DB Info', array('style'=>'font-weight:bold;'))
                . $dbt->generateMarkup(), array('style'=>'float:left; margin:5px;', 'class'=>'hhk-panel'));

        // Software info
        $tbl = new HTMLTable();

        $tbl->addBodyTr(
                HTMLTable::makeTd('Build:', array('class' => 'tdlabel'))
                . HTMLTable::makeTd(CodeVersion::BUILD));
        $tbl->addBodyTr(
                HTMLTable::makeTd('Version:', array('class' => 'tdlabel'))
                . HTMLTable::makeTd(CodeVersion::VERSION));
        $tbl->addBodyTr(
                HTMLTable::makeTd('Patch:', array('class' => 'tdlabel'))
                . HTMLTable::makeTd(CodeVersion::PATCH));
        $tbl->addBodyTr(
                HTMLTable::makeTd('Git Id:', array('class' => 'tdlabel'))
                . HTMLTable::makeTd(CodeVersion::GIT_Id));
        $tbl->addBodyTr(
                HTMLTable::makeTd('Release Date:', array('class' => 'tdlabel'))
                . HTMLTable::makeTd(CodeVersion::REL_DATE));

        $markup .= HTMLContainer::generateMarkup('fieldset',
            HTMLContainer::generateMarkup('legend', 'Software Version', array('style'=>'font-weight:bold;'))
            . $tbl->generateMarkup(), array('style'=>'float:left; margin:5px; margin-left:25px;', 'class'=>'hhk-panel'));


        // Contributors
//        $ctbl = new HTMLTable();
//
//        $contributors = array(
//            array("ML", "Eubanks"),
//            array("E", "Crane"),
//            array("K", "Lannan"),
//            array("R", "Chan"),
//            array("B", "VanderMeer"),
//            array("W", "Ireland"),
//            );
//
//        foreach ($contributors as $c) {
//
//            $ctbl->addBodyTr(
//                HTMLTable::makeTd($c[0], array('class' => 'tdlabel'))
//                . HTMLTable::makeTd($c[1]));
//
//        }
//
//        $markup .= HTMLContainer::generateMarkup('fieldset',
//                HTMLContainer::generateMarkup('legend', 'Major Contributors<br/>in order of Appearance', array('style'=>'font-weight:bold;'))
//                . $ctbl->generateMarkup(), array('style'=>'float:left; margin:5px; margin-left:25px;', 'class'=>'hhk-panel'));

        return HTMLContainer::generateMarkup('div', $markup, array());
    }

}

