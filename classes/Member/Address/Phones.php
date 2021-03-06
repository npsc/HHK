<?php

namespace HHK\Member\Address;

use HHK\AuditLog\NameLog;
use HHK\HTMLControls\{HTMLContainer, HTMLInput, HTMLTable};
use HHK\SysConst\PhonePurpose;
use HHK\Tables\EditRS;
use HHK\Tables\Name\NamePhoneRS;

/**
 * Phones.php
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2017 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */

/**
 * Class Phone - Phone Numbers
 *
 */
class Phones extends AbstractContactPoint {


    protected function loadRecords(\PDO $dbh) {

        $id = $this->name->get_idName();
        $phRS = new NamePhoneRS();
        $rsArray = array();

        if ($id > 0) {

            $phRS->idName->setStoredVal($id);
            $rows = EditRS::select($dbh, $phRS, array($phRS->idName));
            $phRS = null;

            foreach ($rows as $r) {

                $rsArray[$r['Phone_Code']] = new NamePhoneRS();
                EditRS::loadRow($r, $rsArray[$r['Phone_Code']]);

            }
        }

        // Fill out any missing purposes
        foreach ($this->codes as $p) {

            if (isset($rsArray[$p[0]]) === FALSE) {
                $rsArray[$p[0]] = new NamePhoneRS();
            }
        }
        return $rsArray;
    }

    public function get_preferredCode() {
        return $this->name->get_preferredPhone();
    }

    public function getTitle() {
        return "Phone Number";
    }

    public function setPreferredCode($code) {

        if ($code == "" || isset($this->codes[$code])) {
            $this->name->set_preferredPhone($code);
        }
    }

    public function get_Data($code = "") {

        // Cheap way to get around not putting a var into the signature.
        if ($code == "" && $this->get_preferredCode() != "") {
            $code = $this->get_preferredCode();
        }

        $data = array();
        $data["Preferred_Phone"] = $this->get_preferredCode();

        if ($code != "" && isset($this->rSs[$code])) {

            $data["Phone_Num"] = $this->rSs[$code]->Phone_Num->getStoredVal();
            $data["Phone_Extension"] = $this->rSs[$code]->Phone_Extension->getStoredVal();

        } else {

            $data["Phone_Num"] = "";
            $data["Phone_Extension"] = "";

        }

        return $data;
    }

    public function isRecordSetDefined($code) {

        $adrRS = $this->get_recordSet($code);

        if (is_null($adrRS) || $adrRS->Phone_Num->getStoredVal() == '') {
            return FALSE;
        } else {
            return TRUE;
        }

    }

    public function createMarkup($inputClass = '', $idPrefix = "", $room = FALSE, $roomPhoneCkd = FALSE) {

        $table = new HTMLTable();

        foreach ($this->codes as $p) {

            $trContents = $this->createPhoneMarkup($p, $inputClass, $idPrefix);
            // Wrapup this TR
            $table->addBodyTr($trContents);
        }

        if ($room) {
            $table->addBodyTr($this->createHousePhoneMarkup('yr', $idPrefix, $roomPhoneCkd));
        }

        return $table->generateMarkup();
    }

    public function createPhoneMarkup($p, $inputClass = '', $idPrefix = "", $showPrefCheckbox = TRUE) {

        // Preferred Radio button
        $tdContents = HTMLContainer::generateMarkup('label', $p[1], array('for'=>$idPrefix.'ph'.$p[0], 'style'=>'margin-right:6px;'));

        $prefAttr = array();
        $prefAttr['id'] = $idPrefix.'ph' . $p[0];
        $prefAttr['name'] = $idPrefix.'rbPhPref';
        $prefAttr['class'] = 'prefPhone ' . $inputClass;
        $prefAttr['title'] = 'Make this the Preferred phone number';
        $prefAttr['type'] = 'radio';

        if ($p[0] == $this->get_preferredCode()) {
            $prefAttr['checked'] = 'checked';
        } else {
            unset($prefAttr['checked']);
        }

        if ($showPrefCheckbox === FALSE) {
            $prefAttr['style'] = 'display:none;';
        }
        $tdContents .= HTMLInput::generateMarkup($p[0], $prefAttr);
        // Start the row
        $trContents = HTMLTable::MakeTd($tdContents, array('class'=>'tdlabel '.$p[2]));

        // PHone number
        $attr = array();
        $attr['id'] = $idPrefix.'txtPhone' . $p[0];
        $attr['name'] = $idPrefix.'txtPhone[' . $p[0] . ']';
        $attr['title'] = 'Enter a phone number';
        $attr['class'] = 'hhk-phoneInput ' . $inputClass;
        $attr['size'] = '16';

        $tdContents = HTMLInput::generateMarkup($this->rSs[$p[0]]->Phone_Num->getStoredVal(), $attr);

        if ($p[0] != PhonePurpose::Cell && $p[0] != PhonePurpose::Cell2) {
            // Extension
            $attr['id'] = $idPrefix.'txtExtn' . $p[0];
            $attr['name'] = $idPrefix.'txtExtn[' . $p[0] . ']';
            $attr['title'] = 'If needed, enter an Extension here';
            $attr['size'] = '5';

            if ($inputClass != '') {
                $attr['class'] = $inputClass;
            } else {
                unset($attr['class']);
            }
            $tdContents .=  'x'.HTMLInput::generateMarkup($this->rSs[$p[0]]->Phone_Extension->getStoredVal(), $attr);
        }

        // Wrapup the this td
        $trContents .= HTMLTable::MakeTd($tdContents, array('class'=>$p[2]));
        return $trContents;
    }

    protected function createHousePhoneMarkup($prefCode, $idPrefix = "", $roomPhoneCkd = FALSE) {

        // Preferred Radio button
        $tdContents = HTMLContainer::generateMarkup('label', 'ROOM', array('for'=>$idPrefix.'ph'.$prefCode, 'style'=>'margin-right:6px;'));

        $prefAttr = array();
        $prefAttr['id'] = $idPrefix.'ph' . $prefCode;
        $prefAttr['name'] = $idPrefix.'rbPhPref';
        $prefAttr['class'] = 'prefPhone';
        $prefAttr['title'] = 'Make this the Preferred phone number';
        $prefAttr['type'] = 'radio';

        if ($roomPhoneCkd) {
            $prefAttr['checked'] = 'checked';
        } else {
            unset($prefAttr['checked']);
        }

        $tdContents .= HTMLInput::generateMarkup($prefCode, $prefAttr);
        // Start the row
        $trContents = HTMLTable::MakeTd($tdContents, array('class'=>'tdlabel i'));

        // Wrapup the this td
        $trContents .= HTMLTable::MakeTd('House Phone');
        return $trContents;
    }

    public function savePost(\PDO $dbh, array $post, $user, $idPrefix = '') {

        $message = '';
        $id = $this->name->get_idName();

        if ($id < 1) {
            return "Bad member Id.  ";
        }

        foreach ($this->codes as $purpose) {

            $this->SavePhoneNumber($dbh, $post, $purpose, $user, $idPrefix);
        }

        $message .= $this->name->verifyPreferredAddress($dbh, $this, $user);

        return $message;
    }

    public function SavePhoneNumber(\PDO $dbh, $post, $purpose, $user, $idPrefix = "") {

        if (isset($post[$idPrefix.'txtPhone'][$purpose[0]]) === FALSE) {
            return;
        }

        $id = $this->name->get_idName();
        // Set some convenience vars.
        $a = $this->rSs[$purpose[0]];
        $message = "";

        // Phone Number exists in DB?
        if ($a->idName->getStoredVal() > 0) {
            // Phone Number exists in the DB

            if ($post[$idPrefix.'txtPhone'][$purpose[0]] == '') {

                // Delete the Phone Number record
                if (EditRS::delete($dbh, $a, array($a->idName, $a->Phone_Code)) === FALSE) {
                    $message .= 'Problem with deleting this phone number.  ';
                } else {
                    NameLog::writeDelete($dbh, $a, $id, $user, $purpose[1]);
                    $this->rSs[$purpose[0]] = new NamePhoneRS();
                    $message .= 'Phone Number deleted.  ';
                }

            } else {

                // Update the Phone Number
                $this->loadPostData($a, $post, $purpose[0], $user, $idPrefix);
                $numRows = EditRS::update($dbh, $a, array($a->idName, $a->Phone_Code));
                if ($numRows > 0) {
                    NameLog::writeUpdate($dbh, $a, $id, $user, $purpose[1]);
                    $message .= 'Phone Number Updated.  ';
                }
            }

        } else {
            // Phone Number does not exist inthe DB.
            // Did the user fill in this Phone Number panel?
            if ($post[$idPrefix.'txtPhone'][$purpose[0]] != '') {

                // Insert a new Phone Number
                $this->loadPostData($a, $post, $purpose[0], $user, $idPrefix);

                $a->idName->setNewVal($id);
                $a->Phone_Code->setNewVal($purpose[0]);
                EditRS::insert($dbh, $a);

                NameLog::writeInsert($dbh, $a, $id, $user, $purpose[1]);
                $message .= 'Phone Number Inserted.  ';

            }
        }

        // update the recordset
        EditRS::updateStoredVals($a);
        return $message;
    }

    private function loadPostData(NamePhoneRS $a, array $p, $typeCode, $uname, $idPrefix = '') {

        $ph = trim(filter_var($p[$idPrefix.'txtPhone'][$typeCode], FILTER_SANITIZE_STRING));
        $a->Phone_Num->setNewVal($ph);
        if (isset($p[$idPrefix.'txtExtn'][$typeCode])) {
            $a->Phone_Extension->setNewVal(trim(filter_var($p[$idPrefix.'txtExtn'][$typeCode], FILTER_SANITIZE_STRING)));
        }
        // phone search - use only the numberals for efficient phone number search
        $ary = array('+', '-');
        $a->Phone_Search->setNewVal(str_replace($ary, '', filter_var($ph, FILTER_SANITIZE_NUMBER_INT)));
        $a->Status->setNewVal('a');
        $a->Last_Updated->setNewVal(date("Y-m-d H:i:s"));
        $a->Updated_By->setNewVal($uname);

    }

}
?>