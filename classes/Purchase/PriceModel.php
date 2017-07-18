<?php
/**
 * Price.php
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2017 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */

/**
 * Description of Price
 *
 * @author Eric
 */
abstract class PriceModel {

    protected $creditDays = 0;
    protected $glideApplied = FALSE;
    protected $roomRates;
    protected $activeRoomRates;
    protected $remainderAmt = 0;
    protected $visitStatus = '';
    protected $priceModelCode = '';


    public function __construct(array $roomRates) {

        $this->roomRates = $roomRates;
        $this->activeRoomRates = array();

        foreach($roomRates as $rs) {

            if ($rs->Status->getStoredVal() == RateStatus::Active) {
                $this->activeRoomRates[$rs->idRoom_rate->getStoredVal()] = $rs;
            }
        }

    }

    public function loadVisitNights(\PDO $dbh, $idVisit) {

        // Get current nights .
        $stmt1 = $dbh->query("select * from `vvisit_stmt` where `idVisit` = $idVisit and `Status` != 'p' order by `Span`");

        return $stmt1->fetchAll(\PDO::FETCH_ASSOC);
    }

    public abstract function amountCalculator($nites, $idRoomRate, $rateCatetgory = '', $pledgedRate = 0, $guestDays = 0);

    public function daysPaidCalculator($amount, $idRoomRate, $rateCategory = '', $pledgedRate = 0, $rateAdjust = 0, $aveGuestPerDay = 1) {

        $this->remainderAmt = 0;

        if ($amount == 0) {
            return 0;
        }

        $rrateRs = $this->getCategoryRateRs($idRoomRate, $rateCategory);

        // Short circuit for fixed rate x
        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category) {
            if ($pledgedRate > 0) {
                $this->remainderAmt = $amount % $pledgedRate;
                return floor($amount / $pledgedRate);
            }
            return 0;
        }

        // Flat rate
        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::FlatRateCategory) {
            if ($rrateRs->Reduced_Rate_1->getStoredVal() > 0) {
                $rate = (1 + $rateAdjust / 100) * $rrateRs->Reduced_Rate_1->getStoredVal();
                $this->remainderAmt = $amount % $rate;
                return floor($amount / $rate);
            }
            return 0;
        }

        $nights = 0;
        $trialAmt = 0;

        // stupid way:  try each value for days until one matches
        for ($i = 1; $i < 700; $i++) {

            $previousTrialAmt = $trialAmt;
            $trialAmt = (1 + $rateAdjust / 100) * $this->amountCalculator($i, $idRoomRate, $rrateRs->FA_Category->getStoredVal(), $pledgedRate);

            if ($trialAmt <= 0) {
                break;
            }

            if ($amount < $trialAmt) {
                $nights = $i - 1;
                $this->remainderAmt = $amount - $previousTrialAmt;
                break;
            }

            if ($amount == $trialAmt) {
                $nights = $i;
                $this->remainderAmt = 0;
                break;
            }

        }

        return $nights;
    }

    public function getCategoryRateRs($idRoomRate = 0, $category = '') {

        if (isset($this->roomRates[$idRoomRate])) {

            return $this->roomRates[$idRoomRate];

        } else {

            // Default the category
            if ($category == '' || $category == RoomRateCategorys::FullRateCategory) {
                $category = RoomRateCategorys::FlatRateCategory;
            }

            foreach ($this->activeRoomRates as $rr) {

                if ($rr->FA_Category->getStoredVal() == $category) {
                    return $rr;
                }
            }
        }

        throw new Hk_Exception_Runtime('Unknown room rate category or Id.  ');

    }

    public function tiersCalculation($days, $idRoomRate, $rateCategory = '', $pledgedRate = 0, $rateAdjust = 0, $guestDays = 0) {

        $tiers = array();

        $rrateRs = $this->getCategoryRateRs($idRoomRate, $rateCategory);

        // Short circuit for fixed rate x
        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category) {
            $tiers[] = array('rate'=> $pledgedRate, 'days'=>$days, 'amt'=>($days * $pledgedRate));
            return $tiers;
        }

        $adjRatio = (1 + $rateAdjust/100);

        $amount = $rrateRs->Reduced_Rate_1->getStoredVal() * $days * $adjRatio;
        $tiers[] = array('rate'=>$rrateRs->Reduced_Rate_1->getStoredVal() * $adjRatio, 'days'=>$days, 'amt'=>$amount);

        return $tiers;
    }

    public function tiersMarkup($r, &$totalAmt, &$tbl, $tiers, &$startDT, $separator, &$totalGuestNites) {

        foreach ($tiers as $t) {

            $totalAmt += $t['amt'];

            $tbl->addBodyTr(
                 HTMLTable::makeTd($r['vid'] . '-' . $r['span'], array('style'=>'text-align:center;' . $separator))
                .HTMLTable::makeTd($r['title'], array('style'=>$separator))
                .HTMLTable::makeTd($startDT->format('M j, Y'), array('style'=>$separator))
                .HTMLTable::makeTd($startDT->add(new DateInterval('P' . $t['days'] . 'D'))->format('M j, Y'), array('style'=>$separator))
                .HTMLTable::makeTd(number_format($t['rate'], 2), array('style'=>'text-align:right;' . $separator))
                .HTMLTable::makeTd($t['days'], array('style'=>'text-align:center;' . $separator))
                .HTMLTable::makeTd(number_format($t['amt'], 2), array('style'=>'text-align:right;' . $separator))
            );

            $separator = '';

        }
    }

    public function itemMarkup($r, &$tbl) {

        $tbl->addBodyTr(
            HTMLTable::makeTd($r['orderNum'], array('style'=>'text-align:center;'))
            .HTMLTable::makeTd('')
            .HTMLTable::makeTd($r['date'])
            .HTMLTable::makeTd($r['desc'], array('colspan'=>'3', 'style'=>'text-align:right;'))
            .HTMLTable::makeTd($r['amt'], array('style'=>'text-align:right;')));

    }

    public function rateHeaderMarkup(&$tbl, $labels) {
        $tbl->addHeaderTr(HTMLTable::makeTh('Visit Id').HTMLTable::makeTh('Room').HTMLTable::makeTh('Start').HTMLTable::makeTh('End')
            .HTMLTable::makeTh($labels->getString('statement', 'rateHeader', 'Rate')).HTMLTable::makeTh('Nights').HTMLTable::makeTh($labels->getString('statement', 'chargeHeader', 'Charge')));

    }

    public function rateTotalMarkup(&$tbl, $label, $numberNites, $totalAmt, $guestNites) {

        // Room Fee totals
        $tbl->addBodyTr(HTMLTable::makeTd($label, array('colspan'=>'5', 'class'=>'tdlabel hhk-tdTotals', 'style'=>'font-weight:bold;'))
            .HTMLTable::makeTd($numberNites, array('class'=>'hhk-tdTotals', 'style'=>'text-align:center;font-weight:bold;'))
            .HTMLTable::makeTd('$'. $totalAmt, array('class'=>'hhk-tdTotals', 'style'=>'text-align:right;font-weight:bold;')));

    }

    public static function priceModelFactory(\PDO $dbh, $modelCode) {

        switch ($modelCode) {

            case ItemPriceCode::Basic:

                $pm = new PriceBasic(PriceModel::getModelRoomRates($dbh, $modelCode));
                $myRates = array();

                foreach ($pm->getActiveModelRoomRates() as $r) {
                    if ($r->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category) {
                        $myRates[$r->idRoom_rate->getStoredVal()] = $r;
                    }
                }

                if (count($myRates) != 1) {
                    throw new Hk_Exception_Runtime('Fixed Rate Category (x) is not set.  ');
                }

                $pm->activeRoomRates = $myRates;
                $pm->priceModelCode = $modelCode;
                return $pm;


            case ItemPriceCode::Dailey;
                $pm = new PriceDailey(PriceModel::getModelRoomRates($dbh, $modelCode));
                $pm->priceModelCode = $modelCode;
                return $pm;


            case ItemPriceCode::PerGuestDaily;
                //throw new Hk_Exception_Runtime('Guest Days temporarily disabled.  ');

                $pm = new PriceGuestDay(PriceModel::getModelRoomRates($dbh, $modelCode));
                $pm->priceModelCode = $modelCode;
                return $pm;


            case ItemPriceCode::NdayBlock:
                $pm = new PriceNdayBlock(PriceModel::getModelRoomRates($dbh, $modelCode));
                $pm->interval = $pm->getBlockLength($dbh);
                $pm->priceModelCode = $modelCode;
                return $pm;


            case ItemPriceCode::PerpetualStep:
                $pm = new PricePerpetualSteps(PriceModel::getModelRoomRates($dbh, $modelCode));
                $pm->priceModelCode = $modelCode;
                return $pm;


            case ItemPriceCode::Step3:
                $pm = new Price3Steps(PriceModel::getModelRoomRates($dbh, $modelCode));
                $pm->periods = $pm->getRatePeriods($dbh);
                $pm->priceModelCode = $modelCode;
                return $pm;

            case ItemPriceCode::None:
                $pm = new PriceNone(PriceModel::getModelRoomRates($dbh, $modelCode));
                $pm->priceModelCode = $modelCode;
                return $pm;


            default:
                throw new Hk_Exception_Runtime('Price Model not defined. ');

        }
    }

    protected static function getModelRoomRates(\PDO $dbh, $priceModelCode) {

        // Room rates
        $rpRs = new Room_RateRS();

        $rpRs->PriceModel->setStoredVal($priceModelCode);
        $rows = EditRS::select($dbh, $rpRs, array($rpRs->PriceModel), 'and', array($rpRs->FA_Category));
        $rrates = array();

        foreach ($rows as $r) {

            $rs = new Room_RateRS();
            EditRS::loadRow($r, $rs);
            $rrates[$rs->idRoom_rate->getStoredVal()] = $rs;
        }

        return $rrates;
    }

    public function getActiveModelRoomRates() {
        //Return them all
        return $this->activeRoomRates;
    }

    public function getEditMarkup(\PDO $dbh, $defaultRoomRate = 'e') {

        $fTbl = new HTMLTable();
        $fTbl->addHeaderTr(
            HTMLTable::makeTh('Title')
            .HTMLTable::makeTh('Default')
            .HTMLTable::makeTh('Rate')
            );

        // Room rates
        $rows = $this->getActiveModelRoomRates();

        foreach ($rows as $r) {

            $attrs = array('type'=>'radio', 'name'=>'rrdefault');

            if ($r->FA_Category->getStoredVal() == $defaultRoomRate) {
                $attrs['checked'] = 'checked';
            } else {
                unset($attrs['checked']);
            }

            $fTbl->addBodyTr(
                HTMLTable::makeTd(HTMLInput::generateMarkup($r->Title->getStoredVal(), array('name'=>'ratetitle['.$r->idRoom_rate->getStoredVal().']', 'size'=>'13')))
                .HTMLTable::makeTd(HTMLInput::generateMarkup($r->FA_Category->getStoredVal(), $attrs) . ' (' . $r->FA_Category->getStoredVal() . ')')
                .HTMLTable::makeTd('$'.HTMLInput::generateMarkup(number_format($r->Reduced_Rate_1->getStoredVal(), 2), array('name'=>'rr1['.$r->idRoom_rate->getStoredVal().']', 'size'=>'3')), array('style'=>'text-align:center;'))
            );
        }

        return $fTbl;
    }

    public function saveEditMarkup(\PDO $dbh, $post, $username) {

        $defaultRate = 'x';

        if (isset($post['ratetitle'])) {

            $rr1 = filter_var_array($post['rr1'], FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
            $title = filter_var_array($post['ratetitle'], FILTER_SANITIZE_STRING);

            if (isset($post['rrdefault'])) {
                $defaultRate = filter_var($post['rrdefault'], FILTER_SANITIZE_STRING);
            }

            $rows = $this->getActiveModelRoomRates();


            foreach ($rows as $oldRs) {

                $idRoomRate = $oldRs->idRoom_rate->getStoredVal();
                $rpRs = new Room_RateRS();

                $rpRs->PriceModel->setNewVal($this->getPriceModelCode());
                $rpRs->FA_Category->setNewVal($oldRs->FA_Category->getStoredVal());

                $changed = FALSE;


                if (isset($rr1[$idRoomRate])) {

                    $rate1 = str_replace(',', '', str_replace('$', '', $rr1[$idRoomRate]));
                    $rpRs->Reduced_Rate_1->setNewVal($rate1);

                    if ($rate1 != $oldRs->Reduced_Rate_1->getStoredVal()) {

                        $changed = TRUE;
                    }
                }

                if ($changed) {
                    // Insert New
                    $rpRs->Title->setNewVal($title[$idRoomRate]);
                    $rpRs->Updated_By->setNewVal($username);
                    $rpRs->Last_Updated->setNewVal(date('Y-m-d H:i:s'));
                    $rpRs->Status->setNewVal(RateStatus::Active);
                    EditRS::insert($dbh, $rpRs);

                    // update old
                    $oldRs->Status->setNewVal(RateStatus::NotActive);
                    $oldRs->Updated_By->setNewVal($username);
                    $oldRs->Last_Updated->setNewVal(date('Y-m-d H:i:s'));
                    EditRS::update($dbh, $oldRs, array($oldRs->idRoom_rate));

                } else {
                    // update
                    $oldRs->Title->setNewVal($title[$idRoomRate]);
                    $oldRs->Updated_By->setNewVal($username);
                    $oldRs->Last_Updated->setNewVal(date('Y-m-d H:i:s'));
                    EditRS::update($dbh, $oldRs, array($oldRs->idRoom_rate));
                }

            }
        }

        return $defaultRate;
    }

    protected function getPriceModelCode() {
        return $this->priceModelCode;
    }

    public function setCreditDays($days) {
        $this->creditDays = intval($days, 10);
    }

    public function getGlideApplied() {
        return $this->glideApplied;
    }

    public function getRemainderAmt() {
        return $this->remainderAmt;
    }

    public function hasRateCalculator() {
        return TRUE;
    }

    public function getVisitStatus() {
        return $this->visitStatus;
    }

    public function setVisitStatus($visitStatus) {
        $this->visitStatus = $visitStatus;
        return $this;
    }

    public static function installRates(\PDO $dbh, $modelCode) {

        switch ($modelCode) {

            case ItemPriceCode::Basic:
                PriceBasic::InstallRate($dbh);
                break;

            case ItemPriceCode::Dailey;
                PriceDailey::installRate($dbh);
                break;

            case ItemPriceCode::PerGuestDaily;
                PriceGuestDay::installRate($dbh);
                break;

            case ItemPriceCode::NdayBlock:
                PriceNdayBlock::installRate($dbh);
                break;

            case ItemPriceCode::PerpetualStep:
                PricePerpetualSteps::installRate($dbh);
                break;

            case ItemPriceCode::Step3:
                Price3Steps::InstallRate($dbh);
                break;

            case ItemPriceCode::None:
                PriceNone::InstallRate($dbh);
                break;

            default:
                throw new Hk_Exception_Runtime('Price Model code "' . $modelCode . '" not defined. ');

        }
    }

}


class PriceNone extends PriceModel {



    public function amountCalculator($nites, $idRoomRate, $rateCatetgory = '', $pledgedRate = 0, $guestDays = 0) {

        return 0.00;

    }

    public function daysPaidCalculator($amount, $idRoomRate, $rateCatetgory = '', $pledgedRate = 0, $rateAdjust = 0, $aveGuestPerDay = 1) {

        $this->remainderAmt = 0;
        return 0;
    }

    public function tiersCalculation($days, $idRoomRate, $category = '', $pledgedRate = 0, $rateAdjust = 0, $guestDays = 0) {

        $tiers[] = array('rate'=> 0, 'days'=>$days, 'amt'=>0);
        return $tiers;
    }

    public function hasRateCalculator() {
        return FALSE;
    }

    protected static function installRate(\PDO $dbh) {

        $modelCode = ItemPriceCode::None;

        $dbh->exec("Insert into `room_rate` (`idRoom_rate`,`Title`,`Description`,`FA_Category`,`PriceModel`,`Reduced_Rate_1`,`Reduced_Rate_2`,`Reduced_Rate_3`,`Min_Rate`,`Status`) values "
                . "(5,'Flat Rate','','e','$modelCode',0.00,0.00,0.00,0,'a'), "
                . "(6,'Assigned','','x','$modelCode',0,0,0,0,'a');");
/*
REPLACE INTO `room_rate` (`idRoom_rate`,`Title`,`Description`,`FA_Category`, `PriceModel`,`Reduced_Rate_1`,`Reduced_Rate_2`,`Reduced_Rate_3`,`Min_Rate`,`Status`) VALUES
(1,'Rate A','','a','ns',15.00,10.00,5.00,0,'a'),
(2,'Rate B','','b','ns',25.00,20.00,15.00,0,'a'),
(3,'Rate C','','c','ns',35.00,30.00,25.00,0,'a'),
(4,'Rate D','','d','ns',55.00,45.00,35.00,0,'a'),
(5,'Flat Rate','','e','ns',55.00,55.00,55.00,0,'a'),
(6,'Assigned','','x','ns',0,0,0,0,'a');
*/
    }

}

/**
 *  Fixed rate category only.  Doesn't support income chooser.
 */
class PriceBasic extends PriceModel {

    public function amountCalculator($nites, $idRoomRate, $rateCatetgory = '', $pledgedRate = 0, $guestDays = 0) {

        return $nites * $pledgedRate;

    }

    public function daysPaidCalculator($amount, $idRoomRate, $rateCatetgory = '', $pledgedRate = 0, $rateAdjust = 0, $aveGuestPerDay = 1) {

        $this->remainderAmt = 0;

        if ($pledgedRate > 0) {
            $this->remainderAmt = $amount % $pledgedRate;
            return floor($amount / $pledgedRate);
        }

        return 0;
    }

    public function hasRateCalculator() {
        return FALSE;
    }

    protected static function installRate(\PDO $dbh) {

        $modelCode = ItemPriceCode::Basic;

        $dbh->exec("Insert into `room_rate` (`idRoom_rate`,`Title`,`Description`,`FA_Category`,`PriceModel`,`Reduced_Rate_1`,`Reduced_Rate_2`,`Reduced_Rate_3`,`Min_Rate`,`Status`) values "
                . "(5,'Flat Rate','','e','$modelCode',10.00,10.00,10.00,10,'a'), "
                . "(6,'Assigned','','x','$modelCode',0,0,0,0,'a');");
    }

}

class PriceGuestDay extends PriceModel {

    public function loadVisitNights(\PDO $dbh, $idVisit) {

        $spans = parent::loadVisitNights($dbh, $idVisit);

        $stmt = $dbh->query("SELECT s.Visit_Span, SUM(DATEDIFF(IFNULL(s.Span_End_Date, NOW()), s.Span_Start_Date)) AS GDays FROM stays s WHERE s.idVisit = $idVisit GROUP BY s.Visit_Span");

        $stays = array();

        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stays[$r['Visit_Span']] = $r['GDays'];
        }

        for ($n=0; $n<count($spans); $n++) {

            if (isset($stays[$spans[$n]['Span']])) {
                $spans[$n]['Guest_Nights'] = $stays[$spans[$n]['Span']];
            }

        }

        return $spans;

    }

    public function amountCalculator($nites, $idRoomRate, $rateCategory = '', $pledgedRate = 0, $guestDays = 0) {

        // Short circuit for fixed rate x
        if ($rateCategory == RoomRateCategorys::Fixed_Rate_Category) {
            return $nites * $pledgedRate;
        }

        $rrateRs = $this->getCategoryRateRs($idRoomRate, $rateCategory);

        $amount = $rrateRs->Reduced_Rate_1->getStoredVal() * $guestDays;
        return $amount;

    }

    public function daysPaidCalculator($amount, $idRoomRate, $rateCategory = '', $pledgedRate = 0, $rateAdjust = 0, $aveGuestPerDay = 1) {

        $this->remainderAmt = 0;
        $guestDays = 0;

        if ($amount == 0) {
            return 0;
        }

        $rrateRs = $this->getCategoryRateRs($idRoomRate, $rateCategory);

        // Short circuit for fixed rate x
        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category && $pledgedRate > 0) {
            $this->remainderAmt = $amount % $pledgedRate;
            return floor($amount / $pledgedRate);
        }

        // Flat rate
        if ($rrateRs->Reduced_Rate_1->getStoredVal() > 0) {
            $rate = (1 + $rateAdjust / 100) * $rrateRs->Reduced_Rate_1->getStoredVal();
            $this->remainderAmt = $amount % $rate;
            $guestDays = floor($amount / $rate);
        }

        if ($guestDays < 1) {
            return 0;
        }

        // Figure out the real days.
        if ($aveGuestPerDay > 0) {
            return floor($guestDays / $aveGuestPerDay);
        } else {
            return $guestDays;
        }

    }

    public function tiersCalculation($days, $idRoomRate, $rateCategory = '', $pledgedRate = 0, $rateAdjust = 0, $guestDays = 0) {

        $tiers = array();

        $rrateRs = $this->getCategoryRateRs($idRoomRate, $rateCategory);

        // Short circuit for fixed rate x
        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category) {
            $tiers[] = array('rate'=> $pledgedRate, 'days'=>$days, 'amt'=>($days * $pledgedRate), 'gdays'=>$guestDays);
            return $tiers;
        }

        $adjRatio = (1 + $rateAdjust/100);

        $amount = $rrateRs->Reduced_Rate_1->getStoredVal() * $guestDays * $adjRatio;
        $tiers[] = array('rate'=>$rrateRs->Reduced_Rate_1->getStoredVal() * $adjRatio, 'days'=>$days, 'amt'=>$amount, 'gdays'=>$guestDays);

        return $tiers;
    }

    public function tiersMarkup($r, &$totalAmt, &$tbl, $tiers, &$startDT, $separator, &$totalGuestNites) {

        foreach ($tiers as $t) {

            $totalAmt += $t['amt'];
            $totalGuestNites += $t['gdays'];
            $startDate = $startDT->format('M j, Y');
            $startDT->add(new DateInterval('P' . $t['days'] . 'D'));
            $endDate = new DateTime($startDT->format('y-m-d 00:00:00'));

            $today = new DateTime();
            $today->setTime(0, 0, 0);

            if ($today < $endDate) {
                $gDays = $t['gdays'] . ' (Est.)';
                $total = number_format($t['amt'], 2) . ' (Est.)';
            } else {
                $gDays = $t['gdays'];
                $total = number_format($t['amt'], 2);
            }

            $tbl->addBodyTr(
                 HTMLTable::makeTd($r['vid'] . '-' . $r['span'], array('style'=>'text-align:center;' . $separator))
                .HTMLTable::makeTd($r['title'], array('style'=>$separator))
                .HTMLTable::makeTd($startDate, array('style'=>$separator))
                .HTMLTable::makeTd($startDT->format('M j, Y'), array('style'=>$separator))
                .HTMLTable::makeTd(number_format($t['rate'], 2), array('style'=>'text-align:right;' . $separator))
                .HTMLTable::makeTd($t['days'], array('style'=>'text-align:center;' . $separator))
                .HTMLTable::makeTd($gDays, array('style'=>'text-align:center;' . $separator))
                .HTMLTable::makeTd($total, array('style'=>'text-align:right;' . $separator))
            );

            $separator = '';

        }
    }

    public function rateHeaderMarkup(&$tbl, $labels) {

        $tbl->addHeaderTr(HTMLTable::makeTh('Visit Id').HTMLTable::makeTh('Room').HTMLTable::makeTh('Start').HTMLTable::makeTh('End')
            .HTMLTable::makeTh($labels->getString('statement', 'rateHeader', 'Rate')).HTMLTable::makeTh('Nights').HTMLTable::makeTh('Guest Nights').HTMLTable::makeTh($labels->getString('statement', 'chargeHeader', 'Charge')));

    }

    public function rateTotalMarkup(&$tbl, $desc, $numberNites, $totalAmt, $guestNites) {

        // Room Fee totals
        $tbl->addBodyTr(HTMLTable::makeTd($desc, array('colspan'=>'5', 'class'=>'tdlabel hhk-tdTotals', 'style'=>'font-weight:bold;'))
            .HTMLTable::makeTd($numberNites, array('class'=>'hhk-tdTotals', 'style'=>'text-align:center;font-weight:bold;'))
            .HTMLTable::makeTd($guestNites, array('class'=>'hhk-tdTotals', 'style'=>'text-align:center;font-weight:bold;'))
            .HTMLTable::makeTd('$'. $totalAmt, array('class'=>'hhk-tdTotals', 'style'=>'text-align:right;font-weight:bold;')));

    }


    public function itemMarkup($r, &$tbl) {

        $tbl->addBodyTr(
            HTMLTable::makeTd($r['orderNum'], array('style'=>'text-align:center;'))
            .HTMLTable::makeTd('')
            .HTMLTable::makeTd($r['date'])
            .HTMLTable::makeTd($r['desc'], array('colspan'=>'4', 'style'=>'text-align:right;'))
            .HTMLTable::makeTd($r['amt'], array('style'=>'text-align:right;')));

    }


    public function getEditMarkup(\PDO $dbh, $defaultRoomRate = 'e') {

        $fTbl = new HTMLTable();
        $fTbl->addHeaderTr(
            HTMLTable::makeTh('Title')
            .HTMLTable::makeTh('Default')
            .HTMLTable::makeTh('Rate/1st Guest')
            .HTMLTable::makeTh('Rate/2nd Guest')
            .HTMLTable::makeTh('Rate/3rd or more guests')
            );

        // Room rates
        $rows = $this->getActiveModelRoomRates();

        foreach ($rows as $r) {

            $attrs = array('type'=>'radio', 'name'=>'rrdefault');

            if ($r->FA_Category->getStoredVal() == $defaultRoomRate) {
                $attrs['checked'] = 'checked';
            } else {
                unset($attrs['checked']);
            }

            $fTbl->addBodyTr(
                HTMLTable::makeTd(HTMLInput::generateMarkup($r->Title->getStoredVal(), array('name'=>'ratetitle['.$r->idRoom_rate->getStoredVal().']', 'size'=>'13')))
                .HTMLTable::makeTd(HTMLInput::generateMarkup($r->FA_Category->getStoredVal(), $attrs) . ' (' . $r->FA_Category->getStoredVal() . ')')
                .HTMLTable::makeTd('$'.HTMLInput::generateMarkup(number_format($r->Reduced_Rate_1->getStoredVal(), 2), array('name'=>'rr1['.$r->idRoom_rate->getStoredVal().']', 'size'=>'3')), array('style'=>'text-align:center;'))
                .HTMLTable::makeTd('$'.HTMLInput::generateMarkup(number_format($r->Reduced_Rate_2->getStoredVal(), 2), array('name'=>'rr2['.$r->idRoom_rate->getStoredVal().']', 'size'=>'3')), array('style'=>'text-align:center;'))
                .HTMLTable::makeTd('$'.HTMLInput::generateMarkup(number_format($r->Reduced_Rate_3->getStoredVal(), 2), array('name'=>'rr3['.$r->idRoom_rate->getStoredVal().']', 'size'=>'3')), array('style'=>'text-align:center;'))
            );
        }

        return $fTbl;
    }

    protected static function installRate(\PDO $dbh) {

        $modelCode = ItemPriceCode::PerGuestDaily;

        $dbh->exec("Insert into `room_rate` (`idRoom_rate`,`Title`,`Description`,`FA_Category`,`PriceModel`,`Reduced_Rate_1`,`Reduced_Rate_2`,`Reduced_Rate_3`,`Min_Rate`,`Status`) values "
                . "(1,'Rate A','','a','$modelCode',5.00,3.00,1.00,0,'a'),"
                . "(2,'Rate B','','b','$modelCode',10.00,7.00,3.00,0,'a'),"
                . "(3,'Rate C','','c','$modelCode',20.00,15.00,10.00,0,'a'),"
                . "(4,'Rate D','','d','$modelCode',25.00,20.00,10.00,0,'a'),"
                . "(5,'Flat Rate','','e','$modelCode',25.00,25.00,25.00,10,'a'), "
                . "(6,'Assigned','','x','$modelCode',0,0,0,0,'a');");
    }
}

class PriceDailey extends PriceModel {

    public function amountCalculator($nites, $idRoomRate, $rateCategory = '', $pledgedRate = 0, $guestDays = 0) {

        // Short circuit for fixed rate x
        if ($rateCategory == RoomRateCategorys::Fixed_Rate_Category) {
            return $nites * $pledgedRate;
        }

        $rrateRs = $this->getCategoryRateRs($idRoomRate, $rateCategory);

        $amount = $rrateRs->Reduced_Rate_1->getStoredVal() * $nites;
        return $amount;

    }

    public function daysPaidCalculator($amount, $idRoomRate, $rateCategory = '', $pledgedRate = 0, $rateAdjust = 0, $aveGuestPerDay = 1) {

        $this->remainderAmt = 0;

        $rrateRs = $this->getCategoryRateRs($idRoomRate, $rateCategory);

        // Short circuit for fixed rate x
        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category) {

            if ($pledgedRate > 0) {
                $this->remainderAmt = $amount % $pledgedRate;
                return floor($amount / $pledgedRate);
            }

            return 0;
        }


        if ($rrateRs->Reduced_Rate_1->getStoredVal() > 0) {

            $rate = (1 + $rateAdjust / 100) * $rrateRs->Reduced_Rate_1->getStoredVal();
            $this->remainderAmt = $amount % $rate;
            return floor($amount / $rate);
        }

        return 0;
    }

    protected static function installRate(\PDO $dbh) {

        $modelCode = ItemPriceCode::Dailey;

        $dbh->exec("Insert into `room_rate` (`idRoom_rate`,`Title`,`Description`,`FA_Category`,`PriceModel`,`Reduced_Rate_1`,`Reduced_Rate_2`,`Reduced_Rate_3`,`Min_Rate`,`Status`) values "
                . "(1,'Rate A','','a','$modelCode',5.00,3.00,1.00,0,'a'),"
                . "(2,'Rate B','','b','$modelCode',10.00,7.00,3.00,0,'a'),"
                . "(3,'Rate C','','c','$modelCode',20.00,15.00,10.00,0,'a'),"
                . "(4,'Rate D','','d','$modelCode',25.00,20.00,10.00,0,'a'),"
                . "(5,'Flat Rate','','e','$modelCode',25.00,25.00,25.00,10,'a'), "
                . "(6,'Assigned','','x','$modelCode',0,0,0,0,'a');");
    }
}


class Price3Steps extends PriceModel {

    protected $periods;
    const TABLE_NAME = 'Rate_Period';

    public function amountCalculator($days, $idRoomRate, $rateCategory = '', $pledgedRate = 0, $guestDays = 0) {

        $amount = 0.00;

        $rrateRs = $this->getCategoryRateRs($idRoomRate, $rateCategory);

        // Short circuit for fixed rate x
        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category) {
            return $days * $pledgedRate;
        }

        $periods = $this->periods;

        $allDays = $days + $this->creditDays;

        if ($allDays <= $periods['1'] || $rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::FlatRateCategory) {

           $amount = $rrateRs->Reduced_Rate_1->getStoredVal() * $days;
           return $amount;
        }


        if ($allDays <= $periods['2']) {

            if ($this->creditDays <= $periods['1']) {
                // period 1 and period 2 days
                $amount = $rrateRs->Reduced_Rate_1->getStoredVal() * (($periods['1'] - $this->creditDays));
                $amount += $rrateRs->Reduced_Rate_2->getStoredVal() * ($days - ($periods['1'] - $this->creditDays));

            } else {
                // only period 2 days
                $amount = $rrateRs->Reduced_Rate_2->getStoredVal() * ($days);

            }

        } else {


            if ($this->creditDays <= $periods['1']) {
                // period 1, 2 and period 3 days
                $amount = $rrateRs->Reduced_Rate_1->getStoredVal() * ($periods['1'] - $this->creditDays);

                $daysleft = $days - ($periods['1'] - $this->creditDays);

                if ($daysleft <= ($periods['2'] - $periods['1'])) {

                    $amount += $rrateRs->Reduced_Rate_2->getStoredVal() * $daysleft;

                } else {
                    $amount += $rrateRs->Reduced_Rate_2->getStoredVal() * ($periods['2'] - $periods['1']);
                    $amount += $rrateRs->Reduced_Rate_3->getStoredVal() * ($daysleft - ($periods['2'] - $periods['1']));
                }

            } else if ($this->creditDays <= $periods['2']) {

                $amount = $rrateRs->Reduced_Rate_2->getStoredVal() * ($periods['2'] - $this->creditDays);
                $daysleft = $days - ($periods['2'] - $this->creditDays);
                $amount += $rrateRs->Reduced_Rate_3->getStoredVal() * $daysleft;

            } else {
                $amount = $rrateRs->Reduced_Rate_3->getStoredVal() * $days;
            }

        }

        return $amount;
    }

    public function getEditMarkup(\PDO $dbh, $defaultRoomRate = 'e') {

        $rp = readGenLookupsPDO($dbh, Price3Steps::TABLE_NAME);

        $fTbl = new HTMLTable();
        $fTbl->addHeaderTr(
            HTMLTable::makeTh('Title')
            .HTMLTable::makeTh('Default')
            .HTMLTable::makeTh('Starting Rate')
            .HTMLTable::makeTh('After ' . HTMLInput::generateMarkup($rp['1'][2], array('name'=>'rp1', 'size'=>'3', 'disabled'=>'disabled')) . ' days')
            .HTMLTable::makeTh('After ' . HTMLInput::generateMarkup($rp['2'][2], array('name'=>'rp2', 'size'=>'3', 'disabled'=>'disabled')) . ' days')
            );

        // Room rates
        $rows = $this->getActiveModelRoomRates();

        foreach ($rows as $r) {

            $attrs = array('type'=>'radio', 'name'=>'rrdefault');

            if ($r->FA_Category->getStoredVal() == $defaultRoomRate) {
                $attrs['checked'] = 'checked';
            } else {
                unset($attrs['checked']);
            }

            $fTbl->addBodyTr(
                HTMLTable::makeTd(HTMLInput::generateMarkup($r->Title->getStoredVal(), array('name'=>'ratetitle['.$r->idRoom_rate->getStoredVal().']', 'size'=>'13')))
                .HTMLTable::makeTd(HTMLInput::generateMarkup($r->FA_Category->getStoredVal(), $attrs) . ' (' . $r->FA_Category->getStoredVal() . ')')
                .HTMLTable::makeTd('$'.HTMLInput::generateMarkup(number_format($r->Reduced_Rate_1->getStoredVal()), array('name'=>'rr1['.$r->idRoom_rate->getStoredVal().']', 'size'=>'3')), array('style'=>'text-align:center;'))
                .($r->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category || $r->FA_Category->getStoredVal() == RoomRateCategorys::FlatRateCategory ? '' :  HTMLTable::makeTd('$'.HTMLInput::generateMarkup(number_format($r->Reduced_Rate_2->getStoredVal()), array('name'=>'rr2['.$r->idRoom_rate->getStoredVal().']', 'size'=>'3')), array('style'=>'text-align:center;')))
                .($r->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category || $r->FA_Category->getStoredVal() == RoomRateCategorys::FlatRateCategory ? '' :  HTMLTable::makeTd('$'.HTMLInput::generateMarkup(number_format($r->Reduced_Rate_3->getStoredVal()), array('name'=>'rr3['.$r->idRoom_rate->getStoredVal().']', 'size'=>'3')), array('style'=>'text-align:center;')))
            );

        }

        return $fTbl;
    }

    public function saveEditMarkup(\PDO $dbh, $post, $username) {

        // Rate Periods
//        if (isset($post['rp1'])) {
//            $rp1 = intval(filter_var($post['rp1'], FILTER_SANITIZE_NUMBER_INT), 10);
//            $dbh->exec("update gen_lookups set Substitute = $rp1 where Table_Name = 'Rate_Period' and Code = '1'");
//        }
//
//        if (isset($post['rp2'])) {
//            $rp2 = intval(filter_var($post['rp2'], FILTER_SANITIZE_NUMBER_INT), 10);
//            $dbh->exec("update gen_lookups set Substitute = $rp2 where Table_Name = 'Rate_Period' and Code = '2'");
//        }

        $defaultRate = 'x';

        if (isset($post['ratetitle'])) {

            $rr1 = filter_var_array($post['rr1'], FILTER_SANITIZE_STRING);
            $rr2 = filter_var_array($post['rr2'], FILTER_SANITIZE_STRING);
            $rr3 = filter_var_array($post['rr3'], FILTER_SANITIZE_STRING);
            $title = filter_var_array($post['ratetitle'], FILTER_SANITIZE_STRING);

            if (isset($post['rrdefault'])) {
                $defaultRate = filter_var($post['rrdefault'], FILTER_SANITIZE_STRING);
            }

            $rows = $this->getActiveModelRoomRates();

            foreach ($rows as $oldRs) {

                $idRoomRate = $oldRs->idRoom_rate->getStoredVal();
                $rpRs = new Room_RateRS();
                $rpRs->idRoom_rate->setStoredVal($idRoomRate);
                $rpRs->PriceModel->setNewVal($this->getPriceModelCode());
                $rpRs->FA_Category->setNewVal($oldRs->FA_Category->getStoredVal());

                $changed = FALSE;

                if (isset($rr1[$idRoomRate])) {

                    $rate1 = intval(str_replace(',', '', str_replace('$', '', $rr1[$idRoomRate])), 10);
                    $rpRs->Reduced_Rate_1->setNewVal($rate1);

                    if ($rate1 != $oldRs->Reduced_Rate_1->getStoredVal()) {

                        $changed = TRUE;
                    }
                }


                if (isset($rr2[$idRoomRate])) {
                    $rate1 = intval(str_replace(',', '', str_replace('$', '', $rr2[$idRoomRate])), 10);
                    $rpRs->Reduced_Rate_2->setNewVal($rate1);

                    if ($rate1 != $oldRs->Reduced_Rate_2->getStoredVal()) {

                        $changed = TRUE;
                    }
                }

                if (isset($rr3[$idRoomRate])) {
                    $rate1 = intval(str_replace(',', '', str_replace('$', '', $rr3[$idRoomRate])), 10);
                    $rpRs->Reduced_Rate_3->setNewVal($rate1);

                    if ($rate1 != $oldRs->Reduced_Rate_3->getStoredVal()) {

                        $changed = TRUE;
                    }
                }



                if ($changed) {
                    // Insert New
                    $rpRs->Title->setNewVal($title[$idRoomRate]);
                    $rpRs->Updated_By->setNewVal($username);
                    $rpRs->Last_Updated->setNewVal(date('Y-m-d H:i:s'));
                    $rpRs->Status->setNewVal(RateStatus::Active);
                    EditRS::insert($dbh, $rpRs);

                    // update old
                    $oldRs->Status->setNewVal(RateStatus::NotActive);
                    $oldRs->Updated_By->setNewVal($username);
                    $oldRs->Last_Updated->setNewVal(date('Y-m-d H:i:s'));
                    EditRS::update($dbh, $oldRs, array($oldRs->idRoom_rate));

                } else {
                    // update
                    $oldRs->Title->setNewVal($title[$idRoomRate]);
                    $oldRs->Updated_By->setNewVal($username);
                    $oldRs->Last_Updated->setNewVal(date('Y-m-d H:i:s'));
                    EditRS::update($dbh, $oldRs, array($oldRs->idRoom_rate));
                }
            }
        }

        return $defaultRate;

    }

    public static function getRatePeriods(\PDO $dbh) {

        $query = "Select Code, Substitute from gen_lookups where Table_Name = '" . Price3Steps::TABLE_NAME . "'";
        $stmt = $dbh->query($query);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $pers = array();

        foreach ($rows as $r) {
            $pers[$r['Code']] = $r['Substitute'];
        }

        return $pers;
    }

    public function tiersCalculation($days, $idRoomRate, $rateCategory = '', $pledgedRate = 0, $rateAdjust = 0, $guestDays = 0) {

        $tiers = array();

        if ($days < 1) {
            return $tiers;
        }

        $rrateRs = $this->getCategoryRateRs($idRoomRate, $rateCategory);

        // Short circuit for fixed rate x
        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category) {
            $tiers[] = array('rate'=> $pledgedRate, 'days'=>$days, 'amt'=>($days * $pledgedRate));
            return $tiers;
        }

        $adjRatio = (1 + $rateAdjust/100);

        // Flat Rate?
        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::FlatRateCategory) {
           $amount = $rrateRs->Reduced_Rate_1->getStoredVal() * $days * $adjRatio;
           $tiers[] = array('rate'=>$rrateRs->Reduced_Rate_1->getStoredVal() * $adjRatio, 'days'=>$days, 'amt'=>$amount);
           return $tiers;
        }

        $periods = $this->periods;
        $p2Days = 0;

        $this->glideApplied = TRUE;

        if ($this->creditDays <= $periods['1']) {

            $p1Days = $periods['1'] - $this->creditDays;

            if ($p1Days > $days) {
                $p1Days = $days;
            }

            if ($p1Days > 0) {
                $amount = $rrateRs->Reduced_Rate_1->getStoredVal() * $p1Days * $adjRatio;
                $tiers[] = array('rate'=>$rrateRs->Reduced_Rate_1->getStoredVal() * $adjRatio, 'days'=>$p1Days, 'amt'=>$amount);
            }

            $days = $days - $p1Days;

            // now any p2 days
            if ($days > 0 && $days <= ($periods['2'] - $periods['1'])) {
                $p2Days = $days;
                $days = 0;
            } else if ($days > 0) {
                $p2Days = $periods['2'] - $periods['1'];
                $days = $days - $p2Days;
            }

        } else if ($this->creditDays <= $periods['2']) {

            $p2Days = $periods['2'] - $this->creditDays;

            if ($p2Days > $days) {
                $p2Days = $days;
            }

            $days = $days - $p2Days;

        }

        if ($p2Days > 0) {
            $amount = $rrateRs->Reduced_Rate_2->getStoredVal() * $p2Days * $adjRatio;
            $tiers[] = array('rate'=>$rrateRs->Reduced_Rate_2->getStoredVal() * $adjRatio, 'days'=>$p2Days, 'amt'=>$amount);
        }


        if ($days > 0) {
            $amount2 = $rrateRs->Reduced_Rate_3->getStoredVal() * ($days) * $adjRatio;
            $tiers[] = array('rate'=>$rrateRs->Reduced_Rate_3->getStoredVal() * $adjRatio, 'days'=>($days), 'amt'=>$amount2);
        }

        return $tiers;

    }

    protected static function installRate(\PDO $dbh) {

        $modelCode = ItemPriceCode::Step3;

        $dbh->exec("Insert into `room_rate` (`idRoom_rate`,`Title`,`Description`,`FA_Category`,`PriceModel`,`Reduced_Rate_1`,`Reduced_Rate_2`,`Reduced_Rate_3`,`Min_Rate`,`Status`) values "
                . "(1,'Rate A','','a','$modelCode',5.00,3.00,1.00,0,'a'),"
                . "(2,'Rate B','','b','$modelCode',10.00,7.00,3.00,0,'a'),"
                . "(3,'Rate C','','c','$modelCode',20.00,15.00,10.00,0,'a'),"
                . "(4,'Rate D','','d','$modelCode',25.00,20.00,10.00,0,'a'),"
                . "(5,'Flat Rate','','e','$modelCode',25.00,25.00,25.00,10,'a'), "
                . "(6,'Assigned','','x','$modelCode',0,0,0,0,'a');");
    }

}


class PricePerpetualSteps extends PriceModel {

    public function amountCalculator($nites, $idRoomRate, $rateCategory = '', $pledgedRate = 0, $guestDays = 0) {

        if ($nites == 0) {
            return 0.00;
        }

        $rrateRs = $this->getCategoryRateRs($idRoomRate, $rateCategory);

        // Short circuit for fixed rate x
        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category) {
            return $nites * $pledgedRate;
        }

        // Flat Rate?
        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::FlatRateCategory) {
           return $nites * $rrateRs->Reduced_Rate_1->getStoredVal();
        }


        $interval = intval($rrateRs->Reduced_Rate_3->getStoredVal(), 10);
        $deltaAmount = floatval($rrateRs->Reduced_Rate_2->getStoredVal());
        $rate = floatval($rrateRs->Reduced_Rate_1->getStoredVal());
        $minRate = floatval($rrateRs->Min_Rate->getStoredVal());

        if ($interval <= 0
                || $deltaAmount <= 0
                || ($nites + $this->creditDays) <= $interval
                || $rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::FlatRateCategory) {

            // No steps
            return $rate * $nites;
        }

        // Run the credit nights down
        $creditLeft = $this->creditDays;

        While ($creditLeft >= $interval && $rate > 0) {
            $creditLeft = $creditLeft - $interval;
            $rate = max($minRate, $rate - $deltaAmount);
        }

        // Nothing left to charge.
        if ($rate <= 0) {
            return 0;
        }

        if (($nites + $creditLeft) <= $interval) {
            return $rate * $nites;
        }

        // creditsLeft must be less than interval
        if ($creditLeft > 0) {

            // A few more days in the current rate interval
            $amount = ($interval - $creditLeft) * $rate;
            $rate = max($minRate, $rate - $deltaAmount);
            $nitesLeft = $nites - ($interval - $creditLeft);

        } else {

            $nitesLeft = $nites;
            $amount = 0;
        }

        if ($nitesLeft <= $interval) {
            $amount += $rate * $nitesLeft;
            return $amount;
        }

        // Calculate the rate
        do {
            // Add each full interval
            $amount += $rate * $interval;

            $nitesLeft = $nitesLeft - $interval;
            $rate = max($minRate, $rate - $deltaAmount);

        } while ($nitesLeft > $interval && $rate > $minRate);

        // Add up any leftover days
        $amount += $nitesLeft * $rate;

        return $amount;

    }

    public function getEditMarkup(\PDO $dbh, $defaultRoomRate = 'e') {

        $fTbl = new HTMLTable();
        $fTbl->addHeaderTr(
            HTMLTable::makeTh('Title')
            .HTMLTable::makeTh('Default')
            .HTMLTable::makeTh('Starting Rate')
            .HTMLTable::makeTh('Amount Drop')
            .HTMLTable::makeTh('Each Days')
            .HTMLTable::makeTh('Minimum Rate')
            );

        // Room rates
        $rows = $this->getActiveModelRoomRates();

        foreach ($rows as $r) {

            $attrs = array('type'=>'radio', 'name'=>'rrdefault');

            if ($r->FA_Category->getStoredVal() == $defaultRoomRate) {
                $attrs['checked'] = 'checked';
            } else {
                unset($attrs['checked']);
            }

            $fTbl->addBodyTr(
                HTMLTable::makeTd(HTMLInput::generateMarkup($r->Title->getStoredVal(), array('name'=>'ratetitle['.$r->idRoom_rate->getStoredVal().']', 'size'=>'13')))
                .HTMLTable::makeTd(HTMLInput::generateMarkup($r->FA_Category->getStoredVal(), $attrs) . ' (' . $r->FA_Category->getStoredVal() . ')')
                .HTMLTable::makeTd('$'.HTMLInput::generateMarkup(number_format($r->Reduced_Rate_1->getStoredVal()), array('name'=>'rr1['.$r->idRoom_rate->getStoredVal().']', 'size'=>'3')), array('style'=>'text-align:center;'))
                .($r->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category || $r->FA_Category->getStoredVal() == RoomRateCategorys::FlatRateCategory ? '' :  HTMLTable::makeTd('$'.HTMLInput::generateMarkup(number_format($r->Reduced_Rate_2->getStoredVal()), array('name'=>'rr2['.$r->idRoom_rate->getStoredVal().']', 'size'=>'3')), array('style'=>'text-align:center;')))
                .($r->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category || $r->FA_Category->getStoredVal() == RoomRateCategorys::FlatRateCategory ? '' :  HTMLTable::makeTd(HTMLInput::generateMarkup(number_format($r->Reduced_Rate_3->getStoredVal()), array('name'=>'rr3['.$r->idRoom_rate->getStoredVal().']', 'size'=>'3')), array('style'=>'text-align:center;')))
                .($r->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category || $r->FA_Category->getStoredVal() == RoomRateCategorys::FlatRateCategory ? '' :  HTMLTable::makeTd('$'.HTMLInput::generateMarkup(number_format($r->Min_Rate->getStoredVal()), array('name'=>'minrt['.$r->idRoom_rate->getStoredVal().']', 'size'=>'3')), array('style'=>'text-align:center;')))
            );

        }

        return $fTbl;

    }

    public function saveEditMarkup(\PDO $dbh, $post, $username) {

        $defaultRate = 'x';

        if (isset($post['ratetitle'])) {

            $rr1 = filter_var_array($post['rr1'], FILTER_SANITIZE_STRING);
            $rr2 = filter_var_array($post['rr2'], FILTER_SANITIZE_STRING);
            $rr3 = filter_var_array($post['rr3'], FILTER_SANITIZE_STRING);
            $minRate = filter_var_array($post['minrt'], FILTER_SANITIZE_STRING);
            $title = filter_var_array($post['ratetitle'], FILTER_SANITIZE_STRING);

            if (isset($post['rrdefault'])) {
                $defaultRate = filter_var($post['rrdefault'], FILTER_SANITIZE_STRING);
            }

            $rows = $this->getActiveModelRoomRates();

            foreach ($rows as $oldRs) {

                $idRoomRate = $oldRs->idRoom_rate->getStoredVal();
                $rpRs = new Room_RateRS();
                $rpRs->idRoom_rate->setStoredVal($idRoomRate);
                $rpRs->PriceModel->setNewVal($this->getPriceModelCode());
                $rpRs->FA_Category->setNewVal($oldRs->FA_Category->getStoredVal());

                $startRate = $oldRs->Reduced_Rate_1->getStoredVal();
                $changed = FALSE;

                if (isset($rr1[$idRoomRate])) {

                    $rate1 = intval(str_replace(',', '', str_replace('$', '', $rr1[$idRoomRate])), 10);
                    $rpRs->Reduced_Rate_1->setNewVal($rate1);

                    if ($rate1 != $oldRs->Reduced_Rate_1->getStoredVal()) {
                        $changed = TRUE;
                        $startRate = $rate1;
                    }
                }


                if (isset($rr2[$idRoomRate])) {
                    $rate = intval(str_replace(',', '', str_replace('$', '', $rr2[$idRoomRate])), 10);
                    $rpRs->Reduced_Rate_2->setNewVal($rate);

                    if ($rate != $oldRs->Reduced_Rate_2->getStoredVal()) {
                        $changed = TRUE;
                    }
                }

                if (isset($rr3[$idRoomRate])) {
                    $rate = intval(str_replace(',', '', str_replace('$', '', $rr3[$idRoomRate])), 10);
                    $rpRs->Reduced_Rate_3->setNewVal($rate);

                    if ($rate != $oldRs->Reduced_Rate_3->getStoredVal()) {
                        $changed = TRUE;
                    }
                }

                if (isset($minRate[$idRoomRate])) {
                    $rate = intval(str_replace(',', '', str_replace('$', '', $minRate[$idRoomRate])), 10);
                    $rpRs->Min_Rate->setNewVal($rate);

                    if ($rate != $oldRs->Min_Rate->getStoredVal() && $rate <= $startRate) {
                        $changed = TRUE;
                    }
                }


                if ($changed) {
                    // Insert New
                    $rpRs->Title->setNewVal($title[$idRoomRate]);
                    $rpRs->Updated_By->setNewVal($username);
                    $rpRs->Last_Updated->setNewVal(date('Y-m-d H:i:s'));
                    $rpRs->Status->setNewVal(RateStatus::Active);
                    EditRS::insert($dbh, $rpRs);

                    // update old
                    $oldRs->Status->setNewVal(RateStatus::NotActive);
                    $oldRs->Updated_By->setNewVal($username);
                    $oldRs->Last_Updated->setNewVal(date('Y-m-d H:i:s'));
                    EditRS::update($dbh, $oldRs, array($oldRs->idRoom_rate));

                } else {
                    // update
                    $oldRs->Title->setNewVal($title[$idRoomRate]);
                    $oldRs->Updated_By->setNewVal($username);
                    $oldRs->Last_Updated->setNewVal(date('Y-m-d H:i:s'));
                    EditRS::update($dbh, $oldRs, array($oldRs->idRoom_rate));
                }
            }
        }
        return $defaultRate;
    }

    public function tiersCalculation($days, $idRoomRate, $rateCategory = '', $pledgedRate = 0, $rateAdjust = 0, $guestDays = 0) {

        $tiers = array();

        if ($days < 1) {
            return $tiers;
        }

        $rrateRs = $this->getCategoryRateRs($idRoomRate, $rateCategory);

        // Short circuit for fixed rate x
        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category) {
            $tiers[] = array('rate'=> $pledgedRate, 'days'=>$days, 'amt'=>($days * $pledgedRate));
            return $tiers;
        }

        $adjRatio = (1 + $rateAdjust/100);

        // Flat Rate?
        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::FlatRateCategory) {
           $amount = $rrateRs->Reduced_Rate_1->getStoredVal() * $days * $adjRatio;
           $tiers[] = array('rate'=>$rrateRs->Reduced_Rate_1->getStoredVal() * $adjRatio, 'days'=>$days, 'amt'=>$amount);
           return $tiers;
        }

        $interval = intval($rrateRs->Reduced_Rate_3->getStoredVal(), 10);
        $deltaAmount = floatval($rrateRs->Reduced_Rate_2->getStoredVal());
        $rate = floatval($rrateRs->Reduced_Rate_1->getStoredVal());
        $minRate = floatval($rrateRs->Min_Rate->getStoredVal());


        if ($interval <= 0
                || $deltaAmount <= 0
                || ($days + $this->creditDays) <= $interval
                || $rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::FlatRateCategory) {

            // No steps
            $tiers[] = array('rate'=> $rate * $adjRatio, 'days'=>$days, 'amt'=>($days * $rate * $adjRatio));
            return  $tiers;
        }

        $this->glideApplied = TRUE;
        $creditLeft = $this->creditDays;

        // use up the credit days
        While ($creditLeft >= $interval && $rate > 0) {
            $creditLeft = $creditLeft - $interval;
            $rate = max($minRate, $rate - $deltaAmount);
        }

        // Nothing left to charge.
        if ($rate <= 0) {
            $tiers[] = array('rate'=> 0, 'days'=>$days, 'amt'=>0);
            return  $tiers;
        }

        if (($days + $creditLeft) <= $interval) {
            $tiers[] = array('rate'=> $rate * $adjRatio, 'days'=>$days, 'amt'=>($days * $rate * $adjRatio));
            return  $tiers;
        }

        // creditsLeft must be less than interval
        if ($creditLeft > 0) {

            // A few more days in the current rate interval
            $amount = ($interval - $creditLeft) * $rate * $adjRatio;
            $tiers[] = array('rate'=> $rate * $adjRatio, 'days'=>($interval - $creditLeft), 'amt'=>$amount);
            $rate = max($minRate, $rate - $deltaAmount);
            $nitesLeft = $days - ($interval - $creditLeft);

        } else {

            $nitesLeft = $days;

        }

        if ($nitesLeft <= $interval) {
            $tiers[] = array('rate'=> $rate * $adjRatio, 'days'=>$nitesLeft, 'amt'=>($nitesLeft * $rate * $adjRatio));
            return $tiers;
        }

        do {
            // Add each full interval
            $tiers[] = array('rate'=> $rate * $adjRatio, 'days'=>$interval, 'amt'=>($interval * $rate * $adjRatio));

            $nitesLeft = $nitesLeft - $interval;
            $rate = max($minRate, $rate - $deltaAmount);

        } while ($nitesLeft > $interval && $rate > $minRate);

        // Add up any leftover days
        if ($nitesLeft > 0) {

            $tiers[] = array('rate'=> $rate * $adjRatio, 'days'=>$nitesLeft, 'amt'=>($nitesLeft * $rate * $adjRatio));

        }

        return $tiers;
    }

    protected static function installRate(\PDO $dbh) {

        $modelCode = ItemPriceCode::PerpetualStep;

        $dbh->exec("Insert into `room_rate` (`idRoom_rate`,`Title`,`Description`,`FA_Category`,`PriceModel`,`Reduced_Rate_1`,`Reduced_Rate_2`,`Reduced_Rate_3`,`Min_Rate`,`Status`) values "
                . "(1,'Rate A','','a','$modelCode',5.00,3.00,1.00,1,'a'),"
                . "(2,'Rate B','','b','$modelCode',10.00,7.00,3.00,2,'a'),"
                . "(3,'Rate C','','c','$modelCode',20.00,15.00,10.00,10,'a'),"
                . "(4,'Rate D','','d','$modelCode',25.00,20.00,10.00,10,'a'),"
                . "(5,'Flat Rate','','e','$modelCode',25.00,25.00,25.00,10,'a'), "
                . "(6,'Assigned','','x','$modelCode',0,0,0,0,'a');");
    }
}


class PriceNdayBlock extends PriceModel {

    protected $blockTitle = '';
    protected $blocks = 0;
    protected $interval = 0;

    public function amountCalculator($nites, $idRoomRate, $rateCategory = '', $pledgedRate = 0, $guestDays = 0) {

        if ($nites == 0) {
            return 0.00;
        }

        $rrateRs = $this->getCategoryRateRs($idRoomRate, $rateCategory);

        // Short circuit for fixed rate x
        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category) {
            return $nites * $pledgedRate;
        }

        // Flat Rate?
        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::FlatRateCategory) {
            return $nites * $rrateRs->Reduced_Rate_1->getStoredVal();
        }

        $blockRate = floatval($rrateRs->Reduced_Rate_2->getStoredVal());
        $dailyRate = $rrateRs->Reduced_Rate_1->getStoredVal();


        $interval = $this->getBlockLength($dbh);
        $creditNites = $this->creditDays % $interval;

        $blocks = floor($nites / $interval);
        $nitesLeft = $nites % $interval;


        // Check for a free day
        if ($creditNites + $nitesLeft >= $interval) {
            // one free day
            $nitesLeft--;
        }

        $amount = ($blocks * $blockRate) + ($nitesLeft * $dailyRate);

        return $amount;
    }

    public function getBlockLength(\PDO $dbh) {

        $rp = readGenLookupsPDO($dbh, 'Rate_Block');
        $interval = 0;

        foreach ($rp as $d) {
            if ($d[2] == '1') {
                $interval = intval($d[0], 10);
                $this->blockTitle = $d[1];
            }
        }

        if ($interval == 0) {
            throw new Hk_Exception_Runtime('Block Price Model discount duration interval not set (Rate_Block).');
        }

        return $interval;
    }

    public function getEditMarkup(\PDO $dbh, $defaultRoomRate = 'e') {

        $rp = readGenLookupsPDO($dbh, 'Rate_Block');
        $seld = '';

        foreach ($rp as $d) {
            if ($d[2] == '1') {
                $seld = $d[0];
            }
        }

        $fTbl = new HTMLTable();
        $fTbl->addHeaderTr(
            HTMLTable::makeTh('Title')
            .HTMLTable::makeTh('Default')
            .HTMLTable::makeTh('Dailey Rate')
            .HTMLTable::makeTh(HTMLSelector::generateMarkup(HTMLSelector::doOptionsMkup(removeOptionGroups($rp), $seld, FALSE), array('name'=>'selBlock', 'disabled'=>'disabled')) . ' Rate')
            );

        // Room rates
        $rows = $this->getActiveModelRoomRates();

        foreach ($rows as $r) {

            $attrs = array('type'=>'radio', 'name'=>'rrdefault');

            if ($r->FA_Category->getStoredVal() == $defaultRoomRate) {
                $attrs['checked'] = 'checked';
            } else {
                unset($attrs['checked']);
            }

            $fTbl->addBodyTr(
                HTMLTable::makeTd(HTMLInput::generateMarkup($r->Title->getStoredVal(), array('name'=>'ratetitle['.$r->idRoom_rate->getStoredVal().']', 'size'=>'13')))
                .HTMLTable::makeTd(HTMLInput::generateMarkup($r->FA_Category->getStoredVal(), $attrs) . ' (' . $r->FA_Category->getStoredVal() . ')')
                .HTMLTable::makeTd('$'.HTMLInput::generateMarkup(number_format($r->Reduced_Rate_1->getStoredVal()), array('name'=>'rr1['.$r->idRoom_rate->getStoredVal().']', 'size'=>'3')), array('style'=>'text-align:center;'))
                .($r->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category || $r['FA_Category'] == RoomRateCategorys::FlatRateCategory ? '' :  HTMLTable::makeTd('$'.HTMLInput::generateMarkup(number_format($r->Reduced_Rate_2->getStoredVal()), array('name'=>'rr2['.$r->idRoom_rate->getStoredVal().']', 'size'=>'3')), array('style'=>'text-align:center;')))
            );

        }

        return $fTbl;

    }

    public function saveEditMarkup(\PDO $dbh, $post, $username) {

        $defaultRate = 'x';

        if (isset($post['ratetitle'])) {

            $rr1 = filter_var_array($post['rr1'], FILTER_SANITIZE_NUMBER_INT);
            $rr2 = filter_var_array($post['rr2'], FILTER_SANITIZE_NUMBER_INT);
            $title = filter_var_array($post['ratetitle'], FILTER_SANITIZE_STRING);

            if (isset($post['rrdefault'])) {
                $defaultRate = filter_var($post['rrdefault'], FILTER_SANITIZE_STRING);
            }

            $rows = $this->getActiveModelRoomRates();

            foreach ($rows as $oldRs) {

                $idRoomRate = $oldRs->idRoom_rate->getStoredVal();
                $rpRs = new Room_RateRS();
                $rpRs->idRoom_rate->setStoredVal($idRoomRate);
                $rpRs->PriceModel->setNewVal($this->getPriceModelCode());
                $rpRs->FA_Category->setNewVal($oldRs->FA_Category->getStoredVal());
                $changed = FALSE;

                if (isset($rr1[$idRoomRate])) {

                    $rate1 = intval(str_replace(',', '', str_replace('$', '', $rr1[$idRoomRate])), 10);
                    $rpRs->Reduced_Rate_1->setNewVal($rate1);

                    if ($rate1 != $oldRs->Reduced_Rate_1->getStoredVal()) {

                        $changed = TRUE;
                    }
                }


                if (isset($rr2[$idRoomRate])) {
                    $rate1 = intval(str_replace(',', '', str_replace('$', '', $rr2[$idRoomRate])), 10);
                    $rpRs->Reduced_Rate_2->setNewVal($rate1);

                    if ($rate1 != $oldRs->Reduced_Rate_2->getStoredVal()) {

                        $changed = TRUE;
                    }
                }

                if ($changed) {
                    // Insert New
                    $rpRs->Title->setNewVal($title[$idRoomRate]);
                    $rpRs->Updated_By->setNewVal($username);
                    $rpRs->Last_Updated->setNewVal(date('Y-m-d H:i:s'));
                    $rpRs->Status->setNewVal(RateStatus::Active);
                    EditRS::insert($dbh, $rpRs);

                    // update old
                    $oldRs->Status->setNewVal(RateStatus::NotActive);
                    $oldRs->Updated_By->setNewVal($username);
                    $oldRs->Last_Updated->setNewVal(date('Y-m-d H:i:s'));
                    EditRS::update($dbh, $oldRs, array($oldRs->idRoom_rate));

                } else {
                    // update
                    $oldRs->Title->setNewVal($title[$idRoomRate]);
                    $oldRs->Updated_By->setNewVal($username);
                    $oldRs->Last_Updated->setNewVal(date('Y-m-d H:i:s'));
                    EditRS::update($dbh, $oldRs, array($oldRs->idRoom_rate));
                }
            }
        }

        return $defaultRate;
    }

    public function tiersCalculation($days, $idRoomRate, $rateCategory = '', $pledgedRate = 0, $rateAdjust = 0, $guestDays = 0) {

        $tiers = array();

        $rrateRs = $this->getCategoryRateRs($idRoomRate, $rateCategory);

        // Short circuit for fixed rate x
        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::Fixed_Rate_Category) {
            $tiers[] = array('rate'=> $pledgedRate, 'days'=>$days, 'amt'=>($days * $pledgedRate), 'dtext'=>$days);
            $this->daysAccumulator = 0;
            return $tiers;
        }

        $adjRatio = (1 + $rateAdjust/100);

        if ($rrateRs->FA_Category->getStoredVal() == RoomRateCategorys::FlatRateCategory) {
            $tiers[] = array('rate'=> $rrateRs->Reduced_Rate_1->getStoredVal() * $adjRatio, 'days'=>$days, 'amt'=>($days * $rrateRs->Reduced_Rate_1->getStoredVal() * $adjRatio), 'dtext'=>$days);
            $this->daysAccumulator = 0;
            return  $tiers;
        }

        $blockRate = floatval($rrateRs->Reduced_Rate_2->getStoredVal());
        $dailyRate = floatval($rrateRs->Reduced_Rate_1->getStoredVal());


        $creditNites = $this->creditDays % $this->interval;

        $blocks = floor($days / $this->interval);
        $nitesLeft = $days % $this->interval;
        $freeNites = 0;

        // Check for a free day
        if ($creditNites + $nitesLeft >= $this->interval) {
            // one free day
            $nitesLeft--;
            $freeNites = 1;
        }

        if ($blocks > 0) {
            $tiers[] = array('rate'=>number_format($blockRate * $adjRatio,2).'/'.$this->blockTitle, 'days'=>($blocks * $this->interval), 'amt'=>($blocks * $blockRate) * $adjRatio, 'dtext'=>($blocks * $this->interval) . ' (' . $blocks . ')');
            $this->blocks = $blocks;
        }

        if ($freeNites > 0) {
            $tiers[] = array('rate'=>'Free', 'days'=>$freeNites, 'amt'=>0, 'dtext'=>$freeNites);
        }

        if ($nitesLeft > 0) {
            $tiers[] = array('rate'=>number_format($dailyRate * $adjRatio, 2), 'days'=>$nitesLeft, 'amt'=>($nitesLeft * $dailyRate) * $adjRatio, 'dtext'=>$nitesLeft);
        }

        return $tiers;
    }

    public function tiersMarkup($r, &$totalAmt, &$tbl, $tiers, &$startDT, $separator, &$totalGuestNites) {

        foreach ($tiers as $t) {

            $totalAmt += $t['amt'];

            $tbl->addBodyTr(
                 HTMLTable::makeTd($r['vid'] . '-' . $r['span'], array('style'=>'text-align:center;' . $separator))
                .HTMLTable::makeTd($r['title'], array('style'=>$separator))
                .HTMLTable::makeTd($startDT->format('M j, Y'), array('style'=>$separator))
                .HTMLTable::makeTd($startDT->add(new DateInterval('P' . $t['days'] . 'D'))->format('M j, Y'), array('style'=>$separator))
                .HTMLTable::makeTd($t['rate'], array('style'=>'text-align:right;' . $separator))
                .HTMLTable::makeTd($t['dtext'], array('style'=>'text-align:center;' . $separator))
                .HTMLTable::makeTd(number_format($t['amt'], 2), array('style'=>'text-align:right;' . $separator))
            );

            $separator = '';

        }
    }

    protected static function installRate(\PDO $dbh) {

        $modelCode = ItemPriceCode::NdayBlock;

        $dbh->exec("Insert into `room_rate` (`idRoom_rate`,`Title`,`Description`,`FA_Category`,`PriceModel`,`Reduced_Rate_1`,`Reduced_Rate_2`,`Reduced_Rate_3`,`Min_Rate`,`Status`) values "
                . "(1,'Rate A','','a','$modelCode',5.00,3.00,1.00,0,'a'),"
                . "(2,'Rate B','','b','$modelCode',10.00,7.00,3.00,0,'a'),"
                . "(3,'Rate C','','c','$modelCode',20.00,15.00,10.00,0,'a'),"
                . "(4,'Rate D','','d','$modelCode',25.00,20.00,10.00,0,'a'),"
                . "(5,'Flat Rate','','e','$modelCode',25.00,25.00,25.00,10,'a'), "
                . "(6,'Assigned','','x','$modelCode',0,0,0,0,'a');");
    }

}