<?php

namespace HHK\House;

use HHK\Member\RoleMember\GuestMember;
use HHK\SysConst\MemBasis;
use HHK\Tables\Name\NameAddressRS;
use HHK\Member\Address\Address;
use HHK\sec\Session;
use HHK\SysConst\GLTableNames;
use HHK\HTMLControls\HTMLContainer;
use HHK\sec\Labels;
use HHK\HTMLControls\HTMLTable;
use HHK\HTMLControls\HTMLSelector;
use HHK\HTMLControls\HTMLInput;
use HHK\Tables\Name\NameRS;
use HHK\Tables\Name\NameDemogRS;
use HHK\Member\AbstractMember;

/**
 * GuestReferral.php
 *
 * Class for Guest/public facing referral form
 *
 * @author    Will Ireland <wireland@nonprofitsoftwarecorp.org>
 * @copyright 2010-2020 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */

class GuestReferral {
    
    public $patient;
    public $addressRS;
    public $address;
    public $nameRS;
    public $nameDemogRS;
    
    public $colors;
    
    public function __construct(\PDO $dbh){
        $uS = Session::getInstance();
        $this->patient = new GuestMember($dbh, MemBasis::Indivual, 0, NULL);
        $this->patient->setIdPrefix('');
        $this->nameRS = new NameRS();
        $this->nameDemogRS = new NameDemogRS();
        $this->addressRS = new NameAddressRS();
        $this->address = new Address($dbh, $this->patient, $uS->nameLookups[GLTableNames::AddrPurpose]);
        $this->colors = array(
            'header'=>[
                'bg'=>'#f37a27',
                'txt'=>'#ffffff'
            ]
        );
    }
    
    public function createPatientInformationMarkup(){
        $mkup = '';
        $uS = Session::getInstance();
        
        //header
        $mkup .= HTMLContainer::generateMarkup('div', '<h2>' . Labels::getString("memberType", "patient", 'Patient') . ' Information</h2>', array('class'=>'ui-widget-header ui-state-default ui-corner-top'));
        
        //Left Table
        $ltbl = new HTMLTable();
        
        //prefix
        $ltbl->addBodyTr(
            $ltbl->makeTd('Prefix', array('class'=>'tdlabel')) .
            $ltbl->makeTd(
                HTMLSelector::generateMarkup(
                    HTMLSelector::doOptionsMkup($uS->nameLookups[GLTableNames::NamePrefix],
                        $this->nameRS->Name_Prefix->getstoredVal(), TRUE), array('name'=>'selPrefix'))
            )
        );
        
        //first name
        $ltbl->addBodyTr(
            $ltbl->makeTd('First Name', array('class'=>'tdlabel')) .
            $ltbl->makeTd(
                HTMLInput::generateMarkup($this->nameRS->Name_First->getstoredVal(), array('name'=>'txtFirstName', 'class'=>'hhk-firstname'))
            )
        );
        
        //middle name
        $ltbl->addBodyTr(
            $ltbl->makeTd('Middle Name', array('class'=>'tdlabel')) .
            $ltbl->makeTd(
                HTMLInput::generateMarkup($this->nameRS->Name_Middle->getstoredVal(), array('name'=>'txtMiddleName', 'class'=>'hhk-middlename'))
            )
        );
        
        //last name
        $ltbl->addBodyTr(
            $ltbl->makeTd('Last Name', array('class'=>'tdlabel')) .
            $ltbl->makeTd(
                HTMLInput::generateMarkup($this->nameRS->Name_Last->getstoredVal(), array('name'=>'txtLastName', 'class'=>'hhk-lastname'))
            )
        );
        
        //Right Table
        $rtbl = new HTMLTable();
        
        //Nickname
        $rtbl->addBodyTr(
            $rtbl->makeTd('Nickname', array('class'=>'tdlabel')) .
            $rtbl->makeTd(
                HTMLInput::generateMarkup($this->nameRS->Name_Nickname->getstoredVal(), array('name'=>'txtNickname','size'=>'10'))
            )
        );
        
        //email
        $rtbl->addBodyTr(
            $rtbl->makeTd('Email', array('class'=>'tdlabel')) .
            $rtbl->makeTd(
                HTMLInput::generateMarkup('', array('name'=>'txtEmail', 'type'=>'email'))
            )
        );
        
        //birthdate
        $rtbl->addBodyTr(
            $rtbl->makeTd('Birth Date', array('class'=>'tdlabel')) .
            $rtbl->makeTd(
                HTMLInput::generateMarkup('', array('name'=>'txtBirthDate', 'class'=>'ckbdate'))
            )
        );
        
        //gender
        $rtbl->addBodyTr(
            $rtbl->makeTd('Gender', array('class'=>'tdlabel')) .
            $rtbl->makeTd(
                HTMLSelector::generateMarkup(
                    HTMLSelector::doOptionsMkup(removeOptionGroups($uS->nameLookups['Gender']), $this->patient->getDemographicsEntry('Gender')),
                    array('name'=>'sel_Gender', 'class'=>'hhk-demog-input')
                )
            )
        );
        
        
        $mkup .= HTMLContainer::generateMarkup("div",
            $ltbl->generateMarkup(array('class'=>'col-md-6')) .
            $rtbl->generateMarkup(array('class'=>'col-md-6'))
        , array('class'=>'ui-corner-bottom ui-widget-content row'));

        return $mkup;
        
    }
    
    public function createAddressMarkup(){
        $mkup = '';
        
        //header
        $mkup .= HTMLContainer::generateMarkup('div', '<h2>Address</h2>', array('class'=>'ui-widget-header ui-state-default ui-corner-top'));
        
        //body
        $mkup .= HTMLContainer::generateMarkup("div",
            $this->address->createPanelMarkup(AbstractMember::CODE, $this->addressRS)
        , array('class'=>'ui-corner-bottom ui-widget-content'));
        
        return $mkup;
    }
    
}

?>