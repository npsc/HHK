<?php
/**
 * IndivMember.php
 *
 *
 *
 * @category  member
 * @package   Hospitality HouseKeeper
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2015 <nonprofitsoftwarecorp.org>
 * @license   GPL and MIT
 * @link      https://github.com/ecrane57/Hospitality-HouseKeeper
 */

/**
 * IndivMember
 * @author Eric
 */
class IndivMember extends Member {

    /**
     *
     * @var array
     */
    protected $languageRSs = array();

    /**
     *
     * @var array
     */
    protected $insuranceRSs = array();


    protected function getDefaultMemBasis() {
        return MemBasis::Indivual;
    }

    /**
     *
     * @return MemDesignation
     */
    public function getMemberDesignation(){
        return MemDesignation::Individual;
    }

    public function getMemberName() {
        return ($this->get_nickName() != '' ? $this->get_nickName() : $this->get_firstName()) . " " . $this->get_lastName();
    }

    public function getMemberFrmlName() {
        return $this->get_firstName() . " " . $this->get_lastName();
    }

    /**
     *
     * @param PDO $dbh
     * @param string $statusClass HTML Class attribute for status control
     * @param string $basisClass HTML class attribute for basis control
     * @param string $idPrefix
     * @return string HTML table markup
     */
    public function createMarkupTable() {

        $uS = Session::getInstance();
        $idPrefix = $this->getIdPrefix();

        $table = new HTMLTable();
        $table->addHeaderTr(
                HTMLContainer::generateMarkup('th', 'Id')
                . HTMLContainer::generateMarkup('th', 'Prefix')
                . HTMLContainer::generateMarkup('th', 'First Name')
                . HTMLContainer::generateMarkup('th', 'Middle')
                . HTMLContainer::generateMarkup('th', 'Last Name')
                . HTMLContainer::generateMarkup('th', 'Suffix')
                . HTMLContainer::generateMarkup('th', 'Nickname')
                . HTMLContainer::generateMarkup('th', 'Status')
                . HTMLContainer::generateMarkup('th', 'Basis')
                );

        // Id
        $tr = HTMLContainer::generateMarkup('td',
                HTMLInput::generateMarkup(($this->nameRS->idName->getStoredVal() == 0 ? '' : $this->nameRS->idName->getStoredVal())
                        , array('name'=>$idPrefix.'idName', 'readonly'=>'readonly', 'size'=>'5', 'style'=>'border:none;background-color:transparent;'))
                );

        // Prefix
        $tr .= HTMLContainer::generateMarkup('td', HTMLSelector::generateMarkup(
                HTMLSelector::doOptionsMkup($uS->nameLookups[GL_TableNames::NamePrefix],
                        $this->nameRS->Name_Prefix->getstoredVal(), TRUE), array('name'=>$idPrefix.'selPrefix')));

        // First Name
        $tr .= HTMLContainer::generateMarkup('td',
                HTMLInput::generateMarkup($this->nameRS->Name_First->getstoredVal(), array('name'=>$idPrefix.'txtFirstName', 'data-prefix'=>$idPrefix, 'class'=>'hhk-firstname')));

        // Middle Name
        $tr .= HTMLContainer::generateMarkup('td', HTMLInput::generateMarkup($this->nameRS->Name_Middle->getstoredVal(), array('name'=>$idPrefix.'txtMiddleName', 'data-prefix'=>$idPrefix,  'size'=>'5')));

        // Last Name
        $tr .= HTMLContainer::generateMarkup('td', HTMLInput::generateMarkup($this->nameRS->Name_Last->getstoredVal(), array('name'=>$idPrefix.'txtLastName', 'data-prefix'=>$idPrefix,  'class'=>'hhk-lastname')));

        // Suffix
        $tr .= HTMLContainer::generateMarkup('td', HTMLSelector::generateMarkup(
                HTMLSelector::doOptionsMkup($uS->nameLookups[GL_TableNames::NameSuffix],
                        $this->nameRS->Name_Suffix->getstoredVal(), TRUE), array('name'=>$idPrefix.'selSuffix')));

        // Nick Name
        $tr .= HTMLContainer::generateMarkup('td', HTMLInput::generateMarkup($this->nameRS->Name_Nickname->getstoredVal(), array('name'=>$idPrefix.'txtNickname', 'data-prefix'=>$idPrefix,  'size'=>'10')));

        // Status
        $tr .= HTMLContainer::generateMarkup('td', HTMLSelector::generateMarkup(
                HTMLSelector::doOptionsMkup(removeOptionGroups($uS->nameLookups[GL_TableNames::MemberStatus]),
                        $this->nameRS->Member_Status->getstoredVal(), FALSE), array('name'=>$idPrefix.'selStatus')));

        // Basis
        $basis = array();
        foreach ($uS->nameLookups[GL_TableNames::MemberBasis] as $b) {
            if ($b[Member::SUBT] == $this->getMemberDesignation()) {
                $basis[$b[Member::CODE]] = $b;
            }
        }
        $tr .= HTMLContainer::generateMarkup(
                'td',
                HTMLSelector::generateMarkup(
                        HTMLSelector::doOptionsMkup(
                                removeOptionGroups($basis),
                                $this->nameRS->Member_Type->getstoredVal(), FALSE), array('name'=>$idPrefix.'selMbrType')
                        )
                );

        $table->addBodyTr($tr);
        return $table->generateMarkup();
    }


    /**
     *
     * @param PDO $dbh
     * @param string $inputClass HTML class attribute for each control
     * @param bool $showOrientDate
     * @return string HTML UL with following DIV tab panels
     */
    public function createMiscTabsMarkup(\PDO $dbh) {

        $panels = "";
        $tabs = "";
        $attrs = array('id'=>'adminTab', 'class'=>'ui-tabs-hide');

        $panels .= HTMLContainer::generateMarkup(
                'div',
                $this->createAdminPanel(),
                $attrs);

        $excl = $this->createExcludesPanel();
        $attrs['id'] = 'excludesTab';
        $panels .= HTMLContainer::generateMarkup(
                'div',
                $excl['markup'],
                $attrs);

        $attrs['id'] = 'miscTab';
        $panels .= HTMLContainer::generateMarkup(
                'div',
                $this->createDemographicsPanel($dbh),
                $attrs);

        $tabs .= HTMLContainer::generateMarkup('li',
                HTMLContainer::generateMarkup('a', 'Admin', array('href'=>'#adminTab', 'title'=>'Administrative Details'))
                );

        $tabs .= HTMLContainer::generateMarkup('li',
                HTMLContainer::generateMarkup('a', $excl['tabIcon'] . 'Exclude', array('href'=>'#excludesTab', 'title'=>'Exclude Addresses'))
                );

        $tabs .= HTMLContainer::generateMarkup('li',
                HTMLContainer::generateMarkup('a', 'Demographics', array('href'=>'#miscTab', 'title'=>'Miscellaneous demographics'))
                );

                // wrap tabs in a UL
        $ul = HTMLContainer::generateMarkup('ul', $tabs);

        return $ul . $panels;

    }

    public function createAdminPanel() {

        $table = new HTMLTable();

        $table->addBodyTr(
                HTMLTable::makeTd('Last Updated:', array('class'=>'tdlabel'))
                . HTMLTable::makeTd(HTMLContainer::generateMarkup('span', $this->get_lastUpdated()), array('style'=>'display:table-cell;'))
                );

        $table->addBodyTr(
                HTMLTable::makeTd('Updated By:', array('class'=>'tdlabel'))
                . HTMLTable::makeTd(HTMLContainer::generateMarkup('span', $this->get_updatedBy()), array('style'=>'display:table-cell;'))
                );

        $table->addBodyTr(
                HTMLTable::makeTd('Date Added:', array('class'=>'tdlabel'))
                . HTMLTable::makeTd(HTMLContainer::generateMarkup('span', $this->get_dateAdded()), array('style'=>'display:table-cell;'))
                );

        $table->addBodyTr(
                HTMLTable::makeTd('Orientation:', array('class'=>'tdlabel'))
                . HTMLTable::makeTd(HTMLInput::generateMarkup(($this->get_orientationDate() != "" ? date('M j, Y', strtotime($this->get_orientationDate())) : ""), array('name'=>'txtOrienDate', 'class'=>'ckdate')), array('style'=>'display:table-cell;'))
                );
        return $table->generateMarkup();
    }
    /**
     *
      * @return string HTML table structure
     */
    public function createDemographicsPanel(\PDO $dbh) {

        $uS = Session::getInstance();
        $idPrefix = $this->idPrefix;

        $demos = readGenLookupsPDO($dbh, 'Demographics');

        $table = new HTMLTable();

        $table->addBodyTr(
                HTMLTable::makeTd('Birth Date:', array('class'=>'tdlabel'))
                . HTMLTable::makeTd(
                        HTMLInput::generateMarkup(($this->get_birthDate() == '' ? '' : date('M j, Y', strtotime($this->get_birthDate()))), array('name'=>'txtBirthDate', 'class'=>'ckbdate'))
                , array('style'=>'display:table-cell;'))
                );

        $table->addBodyTr(
                HTMLTable::makeTd('Birth Month:', array('class'=>'tdlabel'))
                . HTMLTable::makeTd($this->prepBirthMonthMarkup($this->get_bmonth()), array('style'=>'display:table-cell;'))
                );

        if (isset($demos['g']) && $demos['g'][2] == 'y') {
            $table->addBodyTr(
                HTMLTable::makeTd('Gender:', array('class'=>'tdlabel'))
                . HTMLTable::makeTd(
                        HTMLSelector::generateMarkup(
                                HTMLSelector::doOptionsMkup($uS->nameLookups[GL_TableNames::Gender], $this->nameRS->Gender->getStoredVal()),
                                array('name'=>'selGender')
                                )
                        , array('style'=>'display:table-cell;')
                        )
                );
        }

        if (isset($demos['a']) && $demos['a'][2] == 'y') {
            $table->addBodyTr(
                HTMLTable::makeTd('Age Range:', array('class'=>'tdlabel'))
                . HTMLTable::makeTd(
                        HTMLSelector::generateMarkup(
                                HTMLSelector::doOptionsMkup($uS->nameLookups[GL_TableNames::AgeBracket], $this->demogRS->Age_Bracket->getStoredVal()),
                                array('name'=>'selAgeRange')
                                )
                        , array('style'=>'display:table-cell;')
                        )
                );
        }

        if (isset($demos['i']) && $demos['i'][2] == 'y') {
            $table->addBodyTr(
                HTMLTable::makeTd('Income Level:', array('class'=>'tdlabel'))
                . HTMLTable::makeTd(
                        HTMLSelector::generateMarkup(
                                HTMLSelector::doOptionsMkup($uS->nameLookups[GL_TableNames::IncomeBracket], $this->demogRS->Income_Bracket->getStoredVal()),
                                array('name'=>'selIncomeBracket')
                                )
                        , array('style'=>'display:table-cell;')
                        )
                );
        }

        if (isset($demos['1']) && $demos['1'][2] == 'y') {
            $table->addBodyTr(
                HTMLTable::makeTd('Education Level:', array('class'=>'tdlabel'))
                . HTMLTable::makeTd(
                        HTMLSelector::generateMarkup(
                                HTMLSelector::doOptionsMkup($uS->nameLookups['Education_Level'], $this->demogRS->Education_Level->getStoredVal()),
                                array('name'=>'selEducationLevel')
                                )
                        , array('style'=>'display:table-cell;')
                        )
                );
        }

        if (isset($demos['e']) && $demos['e'][2] == 'y') {
            $table->addBodyTr(
                HTMLTable::makeTd('Ethnicity:', array('class'=>'tdlabel'))
                . HTMLTable::makeTd(
                        HTMLSelector::generateMarkup(
                                HTMLSelector::doOptionsMkup($uS->nameLookups[GL_TableNames::Ethnicity], $this->demogRS->Ethnicity->getStoredVal()),
                                array('name'=>'selEthnicity')
                                )
                        , array('style'=>'display:table-cell;')
                        )
                );
        }

        if (isset($demos['sn']) && $demos['sn'][2] == 'y') {
            $table->addBodyTr(
                HTMLTable::makeTd('Special Needs:', array('class'=>'tdlabel'))
                . HTMLTable::makeTd(
                        HTMLSelector::generateMarkup(
                                HTMLSelector::doOptionsMkup($uS->nameLookups[GL_TableNames::SpecialNeeds], $this->demogRS->Special_Needs->getStoredVal()),
                                array('name'=>'selNeeds')
                                )
                        , array('style'=>'display:table-cell;')
                        )
                );
        }

        $table->addBodyTr(
                HTMLTable::makeTd('Previous Name:', array('class'=>'tdlabel'))
                . HTMLTable::makeTd(
                        HTMLInput::generateMarkup(
                                $this->nameRS->Name_Previous,
                                array('name'=>'txtPreviousName', 'size'=>'9')
                                )
                        , array('style'=>'display:table-cell;')
                        )
                );

        if (isset($demos['ms']) && $demos['ms'][2] == 'y') {
            $table->addBodyTr(
                HTMLTable::makeTd('Media Source:', array('class'=>'tdlabel', 'title'=>'How did you hear of us?'))
                . HTMLTable::makeTd(
                        HTMLSelector::generateMarkup(
                                HTMLSelector::doOptionsMkup($uS->nameLookups['Media_Source'], $this->demogRS->Media_Source->getStoredVal()),
                                array('name'=>'selMedia', 'title'=>'How did you hear of us?')
                                )
                        , array('style'=>'display:table-cell;')
                        )
                );
        }

        // Newsletter
        $nlAttr = array('type'=>'checkbox', 'name'=>'cbnewsltr', 'title'=>'Receive our newsletter?');
        if ($this->demogRS->Newsletter->getStoredVal() > 0) {
            $nlAttr['checked'] = 'checked';
        }

        $table->addBodyTr(
                HTMLTable::makeTd('Newsletter:', array('class'=>'tdlabel', 'title'=>'Receive our newsletter?'))
                . HTMLTable::makeTd(
                        HTMLInput::generateMarkup('', $nlAttr)
                        , array('style'=>'display:table-cell;')
                        )
                );


        // Photo permissions
        $ppAttr = array('type'=>'checkbox', 'name'=>'cbphotoperm', 'title'=>'Permission for using photos?');
        if ($this->demogRS->Photo_Permission->getStoredVal() > 0) {
            $ppAttr['checked'] = 'checked';
        }
        $table->addBodyTr(
                HTMLTable::makeTd('Photo Permission:', array('class'=>'tdlabel', 'title'=>'Permission for using photos?'))
                . HTMLTable::makeTd(
                        HTMLInput::generateMarkup('', $ppAttr)
                        , array('style'=>'display:table-cell;')
                        )
                );


        // No Return
        $nrAttr = array('type'=>'checkbox', 'name'=>'cbnoReturn', 'title'=>'Flag for No Return');

        if ($this->demogRS->No_Return->getStoredVal() != '') {
            $nrAttr['checked'] = 'checked';
        }

        $table->addBodyTr(
            HTMLTable::makeTd('No Return: ' . HTMLInput::generateMarkup('', $nrAttr), array('class'=>'tdlabel', 'title'=>'Flag for No Return'))
            . HTMLTable::makeTd(
                    HTMLSelector::generateMarkup(
                            HTMLSelector::doOptionsMkup($uS->nameLookups['NoReturnReason'], $this->demogRS->No_Return->getStoredVal(), FALSE)
                                ,array('name'=>'selnoReturn', 'style'=>'display:none;'))
                    , array('style'=>'display:table-cell;')
                    )
            );

        // Second row
        $tbl2 = new HTMLTable();

        // Deceased checkbox and date
        $deAttr = array('type'=>'checkbox', 'name'=>'cbdeceased', 'title'=>'Check if deceased.');
        $dateAttr = array('style'=>'display:none;', 'id'=>'disp_deceased');

        if ($this->get_status() == MemStatus::Deceased) {
            $deAttr['checked'] = 'checked';
            $dateAttr['style'] = 'display:table-cell;';
        }

        $tbl2->addBodyTr(
                HTMLTable::makeTd('Deceased: ' . HTMLInput::generateMarkup('', $deAttr) . HTMLInput::generateMarkup('', array('type'=>'hidden','name'=>'cbMarker_deceased')), array('class'=>'tdlabel', 'title'=>'Check if deceased.'))
                . HTMLTable::makeTd(HTMLContainer::generateMarkup('div',
                        'Date: ' . HTMLInput::generateMarkup(($this->get_DateDeceased() == '' ? '' : date('M j, Y', strtotime($this->get_DateDeceased()))), array('name'=>'txtDeathDate', 'class'=>'ckbdate')), $dateAttr))
                );

        // Language
        if ($uS->LangChooser) {

            $langs = array();

            $stmt = $dbh->query("Select idLanguage, Title, ISO_639_1 from language where Display = 1");
            $defaultLangId = '';

            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $langs[$r['idLanguage']] = array(0=>$r['idLanguage'], 1=>$r['Title'] . ' (' . $r['ISO_639_1'] . ')');

                // Set English as default
                if ($r['ISO_639_1'] == 'en') {
                    $defaultLangId = $r['idLanguage'];
                }

            }

            $choices = array();
            foreach ($this->languageRSs as $lRs) {
                $choices[$lRs->Language_Id->getStoredVal()] = $lRs->Language_Id->getStoredVal();
            }

//            if (count($choices) < 1) {
//                $choices[$defaultLangId] = $defaultLangId;
//            }

            $tbl2->addBodyTr(
                HTMLTable::makeTd('Languages:', array('class'=>'tdlabel', 'title'=>'Choose languages'))
                . HTMLTable::makeTd(
                        HTMLSelector::generateMarkup(
                                HTMLSelector::doOptionsMkup($langs, $choices, FALSE),
                                array('name'=>'selLanguage[]', 'class'=>'hhk-multisel', 'title'=>'Choose languages', 'multiple'=>'multiple', 'size'=>'2')
                                )
                        , array('style'=>'display:table-cell;')
                        )
                );

        }

        // Insurance
        if ($uS->InsuranceChooser) {
            $tbl2->addBodyTr(
                HTMLTable::makeTd(
                        $this->createInsurancePanel($dbh, $idPrefix)
                        , array('style'=>'display:table-cell;', 'colspan'=>'3')));
        }

        return $table->generateMarkup(array('style'=>'float:left; margin-right:10px;')) . $tbl2->generateMarkup(array('style'=>'float:left;'));

    }

    public function createInsurancePanel(\PDO $dbh, $idPrefix) {

        $uS = Session::getInstance();

        if (!$uS->InsuranceChooser) {
            return '';
        }

        // Insurance Companies
        $stmt = $dbh->query("select * from insurance order by `Type`, `Title`");
        $ins = array();

        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ins[$r['Type']][$r['idInsurance']] = array(0=>$r['idInsurance'], 1=>$r['Title']);
        }

        // Insurance Types
        $stmt2 = $dbh->query("Select * from `insurance_type` order by `List_Order`");
        $insTypes = array();

        while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $insTypes[$r['idInsurance_type']] = $r;
        }

        $tbl = new HTMLTable();
        $tbl->addHeaderTr(HTMLTable::makeTh('Insurance', array('colspan'=>'3')));

        foreach ($insTypes as $i) {

            // Chosen Insurnaces...
            $choices = array();
            foreach ($this->insuranceRSs as $lRs) {
                if (isset($ins[$i['idInsurance_type']][$lRs->Insurance_Id->getStoredVal()])) {
                    $choices[$lRs->Insurance_Id->getStoredVal()] = $lRs->Insurance_Id->getStoredVal();
                }
            }

            if (count($choices) === 0) {
                $choices[''] = '';
            }

            $attr = array(
                'name'=>$idPrefix.'sel' . $i['Title'],
            );

            if ($i['Multiselect'] > 1) {
                $attr['multiple'] = 'multiple';
                $attr['class'] = 'hhk-multisel';
                $attr['name'] = $idPrefix.'sel' . $i['Title'] . '[]';
                $attr['id'] = $idPrefix.'sel' . $i['Title'];
            }

            $tbl->addBodyTr(
                    HTMLTable::makeTd($i['Title'])
                    .HTMLTable::makeTd(HTMLSelector::generateMarkup(HTMLSelector::doOptionsMkup($ins[$i['idInsurance_type']], $choices, TRUE),$attr)));

        }

        // help message
        //$tbl->addBodyTr(HTMLTable::makeTd('Insurance Types can be defined on the Resource Builder page under the Lookups tab.', array('colspan'=>'3', 'class'=>'hhk-htip')));


        return $tbl->generateMarkup();

    }


    /**
     *
     * @param array $rel Array of relationship types
     * @param string $page Link to page for related members
     * @return string HTML markup
     */
    public function createRelationsTabs(array $rel, $page = "NameEdit.php") {

        $relTab = HTMLContainer::generateMarkup('div', $rel[RelLinkType::Spouse]->createMarkup($page), array('style'=>'float:left; margin-left:20px;'))
                .HTMLContainer::generateMarkup('div',$rel[RelLinkType::Sibling]->createMarkup($page), array('style'=>'float:left; margin-left:20px;'))
                . HTMLContainer::generateMarkup('div',$rel[RelLinkType::Parnt]->createMarkup($page), array('style'=>'float:left; margin-left:20px;'))
                . HTMLContainer::generateMarkup('div',$rel[RelLinkType::Child]->createMarkup($page), array('style'=>'float:left; margin-left:20px;'))
                . HTMLContainer::generateMarkup('div',$rel[RelLinkType::Relative]->createMarkup($page), array('style'=>'float:left; margin-left:20px;'));

        $coTab = $this->createOrgMarkup($rel, $page);

        $ul = HTMLContainer::generateMarkup('ul',
            HTMLContainer::generateMarkup('li', HTMLContainer::generateMarkup('a', 'Relatives', array('href'=>'#relvs')))
            . HTMLContainer::generateMarkup('li', HTMLContainer::generateMarkup('a', 'Company', array('href'=>'#copt')))
            );

        $coptDiv = HTMLContainer::generateMarkup('div', $coTab, array('id'=>'copt', 'class'=>'ui-tabs-hide'));
        $relDiv = HTMLContainer::generateMarkup('div', $relTab, array('id'=>'relvs', 'class'=>'ui-tabs-hide'));

        return $ul . $coptDiv . $relDiv;
    }

    /**
     *
     * @param array $rel Array of relationship types
     * @param string $page link
     * @return string HTML markup
     */
    public function createOrgMarkup(array $rel, $page = "NameEdit.php") {

        $table = new HTMLTable();
        $table->addHeaderTr(
                HTMLTable::makeTh('Title')
        );

        $table->addBodyTr(
                 // title
                HTMLTable::makeTd(HTMLInput::generateMarkup($this->get_title(), array('name' => 'txtTitle', 'size' => '10'))
                )
        );

        $coTab = $table->generateMarkup(array('style'=>'float:left;')) . HTMLContainer::generateMarkup('div', $rel[RelLinkType::Company]->createMarkup($page), array('style'=>'float:left; margin-left:20px;'));

        return $coTab;
    }


    public function loadRealtionships(PDO $dbh) {

       return array(
            RelLinkType::Sibling => new Siblings($dbh, $this->get_idName()),
            RelLinkType::Child => new Children($dbh, $this->get_idName()),
            RelLinkType::Parnt => new Parents($dbh, $this->get_idName()),
            RelLinkType::Spouse => new Partner($dbh, $this->get_idName()),
            RelLinkType::Company => new Company($dbh, $this->get_idName()),
            RelLinkType::Relative => new Relatives($dbh, $this->get_idName())
            );
    }


    private function prepBirthMonthMarkup($month) {

        $numMonth = intval($month, 10);

        $markup = "<select name='selBirthMonth' id='selBirthMonth'>";
        $monthList = array(0 => "", 1 => "(1) Jan", 2 => "(2) Feb", 3 => "(3) Mar", 4 => "(4) Apr", 5 => "(5) May", 6 => "(6) Jun", 7 => "(7) Jul", 8 => "(8) Aug", 9 => "(9) Spt", 10 => "(10) Oct", 11 => "(11) Nov", 12 => "(12) Dec");
        for ($i = 0; $i < 13; $i++) {
            if ($i === $numMonth) {
                $markup .= "<option value='$i' selected='selected'>" . $monthList[$i] . "</option>";
            } else {
                $markup .= "<option value='$i'>" . $monthList[$i] . "</option>";
            }
        }
        $markup .= "</select>";
        return $markup;
    }

    public function getAssocDonorLabel() {
        return "Associate";
    }

    public function getAssocDonorList(array $rel) {
        $rA = array();
        $partner = $rel[RelLinkType::Spouse];

        if (count($partner->getRelNames()) > 0) {
            $rNames = $partner->getRelNames();
            $rA[$rNames[0]['Id']] = array(0=>$rNames[0]['Id'], 1=>'Spouse');
        }
        return $rA;
    }

    public function getDefaultDonor(array $rel) {

        $partner = $rel[RelLinkType::Spouse];

        if (count($partner->getRelNames()) > 0) {
            $rNames = $partner->getRelNames();
            return $rNames[0]['Id'];
        }
        return '';

    }

    /**
     *
     * @param PDO $dbh
     * @param array $post
     * @throws Hk_Exception_Runtime
     */
    protected function processMember(\PDO $dbh, array $post) {

        $uS = Session::getInstance();
        // Convenience var
        $n = $this->nameRS;
        $idPrefix = $this->getIdPrefix();

        //  Name
        $first = $n->Name_First->getStoredVal();
        if (isset($post[$idPrefix.'txtFirstName'])) {
            $n->Name_First->setNewVal(ucfirst(trim(filter_var($post[$idPrefix.'txtFirstName'], FILTER_SANITIZE_STRING))));
            $first = $n->Name_First->getNewVal();
        }

        $last = $n->Name_Last->getStoredVal();
        if (isset($post[$idPrefix.'txtLastName'])) {
            $n->Name_Last->setNewVal(ucfirst(trim(filter_var($post[$idPrefix.'txtLastName'], FILTER_SANITIZE_STRING))));
            $last = $n->Name_Last->getNewVal();
        }

        $middle = $n->Name_Middle->getStoredVal();
        if (isset($post[$idPrefix.'txtMiddleName'])) {
            $n->Name_Middle->setNewVal(ucfirst(trim(filter_var($post[$idPrefix.'txtMiddleName'], FILTER_SANITIZE_STRING))));
            $middle = $n->Name_Middle->getNewVal();
        }

        $prefix = $n->Name_Prefix->getStoredVal();
        if (isset($post[$idPrefix.'selPrefix'])) {
            $n->Name_Prefix->setNewVal(filter_var($post[$idPrefix.'selPrefix'], FILTER_SANITIZE_STRING));
            $prefix = $n->Name_Prefix->getNewVal();
        }

        $suffix = $n->Name_Suffix->getStoredVal();
        if (isset($post[$idPrefix.'selSuffix'])) {
            $n->Name_Suffix->setNewVal(filter_var($post[$idPrefix.'selSuffix'], FILTER_SANITIZE_STRING));
            $suffix = $n->Name_Suffix->getNewVal();
        }

        // Minimum requirements for saving a record.
        if ($n->Name_Last->getStoredVal() == '' && $n->Name_Last->getNewVal() == '') {
            throw new Hk_Exception_Runtime("The Last Name cannot be blank.");
        }

        // Name Last-First
        $comma = '';
        if ($first != '') {
            $comma = ', ';
        }
        $n->Name_Last_First->setNewVal(trim($last . $comma . $first));


        // Name Full
        if (isset($uS->nameLookups[GL_TableNames::NamePrefix][$prefix])) {
            $prefix = $uS->nameLookups[GL_TableNames::NamePrefix][$prefix][Member::DESC];
        }

        if (isset($uS->nameLookups[GL_TableNames::NameSuffix][$suffix])) {
            $suffix = $uS->nameLookups[GL_TableNames::NameSuffix][$suffix][Member::DESC];
        }

        $nstring = '';

        if ($middle != '') {
            $nstring .= trim($prefix . ' ' . $first . ' ' . $middle . ' ' . $last . ' ' . $suffix);
        } else {
            $nstring .= trim($prefix . ' ' . $first . ' ' . $last . ' ' . $suffix);
        }

        $n->Name_Full->setNewVal($nstring);


        //  Title
        if (isset($post[$idPrefix.'txtTitle'])) {
            $n->Title->setNewVal(filter_var($post[$idPrefix.'txtTitle'], FILTER_SANITIZE_STRING));
        }

        //  Previous Name
        if (isset($post[$idPrefix.'txtPreviousName'])) {
            $n->Name_Previous->setNewVal(ucfirst(trim(filter_var($post[$idPrefix.'txtPreviousName'], FILTER_SANITIZE_STRING))));
        }

        //  Nickname
        if (isset($post[$idPrefix.'txtNickname'])) {
            $n->Name_Nickname->setNewVal(ucfirst(trim(filter_var($post[$idPrefix.'txtNickname'], FILTER_SANITIZE_STRING))));
        }


        //  Birth Month
        if (isset($post[$idPrefix.'selBirthMonth'])) {
            $n->Birth_Month->setNewVal(filter_var($post[$idPrefix.'selBirthMonth'], FILTER_SANITIZE_NUMBER_INT));
        }

        //  Birth Date
        if (isset($post[$idPrefix.'txtBirthDate'])) {
            $bd = filter_var($post[$idPrefix.'txtBirthDate'], FILTER_SANITIZE_STRING);
            if ($bd != '') {
                $n->BirthDate->setNewVal(date('Y-m-d H:i:s', strtotime($bd)));
                $n->Birth_Month->setNewVal(date('m', strtotime($bd)));
            } else {
                $n->BirthDate->setNewVal('');
            }
        }

        //  Gender
        if (isset($post[$idPrefix.'selGender'])) {
            $n->Gender->setNewVal(filter_var($post[$idPrefix.'selGender'], FILTER_SANITIZE_STRING));
        }

        //  Age
        if (isset($post[$idPrefix.'selAgeRange'])) {
            $this->demogRS->Age_Bracket->setNewVal(filter_var($post[$idPrefix.'selAgeRange'], FILTER_SANITIZE_STRING));
        }

        //  Income
        if (isset($post[$idPrefix.'selIncomeBracket'])) {
            $this->demogRS->Income_Bracket->setNewVal(filter_var($post[$idPrefix.'selIncomeBracket'], FILTER_SANITIZE_STRING));
        }

        //  Education
        if (isset($post[$idPrefix.'selEducationLevel'])) {
            $this->demogRS->Education_Level->setNewVal(filter_var($post[$idPrefix.'selEducationLevel'], FILTER_SANITIZE_STRING));
        }

        //  Ethnicity
        if (isset($post[$idPrefix.'selEthnicity'])) {
            $this->demogRS->Ethnicity->setNewVal(filter_var($post[$idPrefix.'selEthnicity'], FILTER_SANITIZE_STRING));
        }

        //  Special Needs
        if (isset($post[$idPrefix.'selNeeds'])) {
            $this->demogRS->Special_Needs->setNewVal(filter_var($post[$idPrefix.'selNeeds'], FILTER_SANITIZE_STRING));
        }

        //  Media Source
        if (isset($post[$idPrefix.'selMedia'])) {
            $this->demogRS->Media_Source->setNewVal(filter_var($post[$idPrefix.'selMedia'], FILTER_SANITIZE_STRING));
        }

        //  Languages
        if ($uS->LangChooser) {
            $this->saveLanguages($dbh, $post, $idPrefix, $uS->username);
        }

        //  Insurance
        if ($uS->InsuranceChooser) {
            $this->saveInsurance($dbh, $post, $idPrefix, $uS->username);
        }

        //  No Return
        if (isset($post[$idPrefix.'cbnoReturn']) && isset($post[$idPrefix.'selnoReturn'])) {

            $reason = filter_var($post[$idPrefix.'selnoReturn'], FILTER_SANITIZE_STRING);

            if (isset($uS->nameLookups['NoReturnReason'][$reason])) {
                $this->demogRS->No_Return->setNewVal($reason);
            } else {
                $this->demogRS->No_Return->setNewVal('');
            }

        } else if (isset($post[$idPrefix.'selnoReturn'])) {
            $this->demogRS->No_Return->setNewVal('');
        }


        //  Newsletter
        if (isset($post[$idPrefix.'cbnewsltr'])) {
            $this->demogRS->Newsletter->setNewVal(1);
        } else {
            $this->demogRS->Newsletter->setNewVal(0);
        }

        //  Photo Permission
        if (isset($post[$idPrefix.'cbphotoperm'])) {
            $this->demogRS->Photo_Permission->setNewVal(1);
        } else {
            $this->demogRS->Photo_Permission->setNewVal(0);
        }

    }


    protected function saveLanguages(\PDO $dbh, $post, $idPrefix, $username) {

        if ($this->get_idName() > 0) {

            $myLangs = array();
            $langs = array();
            $langs2 = array();

            if (isset($post[$idPrefix.'selLanguage']) && $this->get_idName() > 0) {
                $langs = filter_var_array($post[$idPrefix.'selLanguage'], FILTER_SANITIZE_NUMBER_INT);
                $langs2 = array_flip($langs);
            }


            // Remove any unset languages.
            foreach ($this->languageRSs as $langRs) {

                if (!isset($langs2[$langRs->Language_Id->getStoredVal()])) {
                    // remove recordset
                    EditRS::delete($dbh, $langRs, array($langRs->Language_Id, $langRs->idName));

                } else {
                    $myLangs[] = $langRs;
                }
            }

            // set any new languages
            foreach ($langs as $v) {

                $idLang = intval($v, 10);

                if ($idLang < 1) {
                    continue;
                }

                $found = FALSE;

                foreach ($this->languageRSs as $lRs) {
                    if ($lRs->Language_Id->getStoredVal() == $idLang) {
                        $found = TRUE;
                    }
                }

                if (!$found) {
                    $langRs = new Name_LanguageRS();
                    $langRs->Language_Id->setNewVal($idLang);
                    $langRs->idName->setNewVal($this->get_idName());
                    $langRs->Updated_By->setNewVal($username);
                    $recId = EditRS::insert($dbh, $langRs);

                    if ($recId > 0) {
                        $langRs->setStoredVal($recId);
                        $myLangs[] = $langRs;
                    }
                }
            }

            $this->languageRSs = $myLangs;
        }

    }


    protected function saveInsurance(\PDO $dbh, $post, $idPrefix, $username) {

        $myInss = array();

        // Insurance Types
        $stmt2 = $dbh->query("Select * from `insurance_type` order by `List_Order`");
        $insTypes = array();

        while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $insTypes[$r['idInsurance_type']] = $r;
        }

        foreach ($insTypes as $i) {

            if (isset($post[$idPrefix.'sel'.$i['Title']]) && $post[$idPrefix.'sel'.$i['Title']] != '' && $this->get_idName() > 0) {

                if ($i['Multiselect'] > 1) {
                    $inss = filter_var_array($post[$idPrefix.'sel'.$i['Title']], FILTER_SANITIZE_NUMBER_INT);
                    $inss2 = array_flip($inss);
                } else {
                    $ins = filter_var($post[$idPrefix.'sel'.$i['Title']], FILTER_SANITIZE_NUMBER_INT);
                    $inss = array($ins=>$ins);
                    $inss2[$ins] = $ins;
                }

                // Remove any unset languages.
                foreach ($this->insuranceRSs as $insRs) {

                    if (!isset($inss2[$insRs->Insurance_Id->getStoredVal()])) {
                        // remove recordset
                        EditRS::delete($dbh, $insRs, array($insRs->Insurance_Id, $insRs->idName));

                    } else {
                        $myInss[] = $insRs;
                    }
                }

                // set any new languages
                foreach ($inss as $v) {

                    $idins = intval($v, 10);

                    if ($idins < 1) {
                        continue;
                    }

                    $found = FALSE;

                    foreach ($this->insuranceRSs as $lRs) {
                        if ($lRs->Insurance_Id->getStoredVal() == $idins) {
                            $found = TRUE;
                        }
                    }

                    if (!$found) {
                        $insRs = new Name_InsuranceRS();
                        $insRs->Insurance_Id->setNewVal($idins);
                        $insRs->idName->setNewVal($this->get_idName());
                        $insRs->Updated_By->setNewVal($username);

                        $recId = EditRS::insert($dbh, $insRs);

                        if ($recId > 0) {
                            $insRs->setStoredVal($recId);
                            $myInss[] = $insRs;
                        }
                    }
                }
            }
        }

        $this->insuranceRSs = $myInss;

    }
    /**
     *
     * @param mixed $v
     * @throws Hk_Exception_InvalidArguement
     */
    public function set_companyRcrd($v) {
        if ($v == 1 || $v == TRUE) {
            throw new Hk_Exception_InvalidArguement("Individual Member Record cannot be set to Organization.");
        }
    }

    public function getAgeRange() {
        return $this->demogRS->Age_Bracket->getStoredVal();
    }

    public function setAgeRange($v) {
        $this->demogRS->Age_Bracket->setNewVal($v);
    }

    public function getEthnicity() {
        return $this->demogRS->Ethnicity->getStoredVal();
    }

    public function getMediaSource() {
        return $this->demogRS->Media_Source->getStoredVal();
    }

    public function getNoReturnDemog() {
        return $this->demogRS->No_Return->getStoredVal();
    }

    public function setMediaSource($v) {
        $this->demogRS->Media_Source->setNewVal($v);
    }

     public function getNewsletter() {
        return $this->demogRS->Newsletter->getStoredVal();
    }

    public function setNewsletter($v) {
        $this->demogRS->Newsletter->setNewVal($v);
    }


}

