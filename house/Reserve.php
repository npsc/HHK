<?php

use HHK\Config_Lite\Config_Lite;
use HHK\sec\{Session, WebInit};
use HHK\HTMLControls\HTMLContainer;
use HHK\Payment\PaymentSvcs;
use HHK\Exception\RuntimeException;
use HHK\House\Reservation\Reservation_1;
use HHK\Member\Role\Guest;
use HHK\House\TemplateForm\ConfirmationForm;
use HHK\House\ReserveData\ReserveData;
use HHK\Member\Role\AbstractRole;
use HHK\Payment\PaymentGateway\AbstractPaymentGateway;
use HHK\SysConst\RoomRateCategories;
use HHK\sec\Labels;

/**
 * Reserve.php
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2020 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */
require ("homeIncludes.php");


try {
    $wInit = new webInit();
} catch (Exception $exw) {
    die($exw->getMessage());
}

$dbh = $wInit->dbh;

// get session instance
$uS = Session::getInstance();


// Get labels
$labels = Labels::getLabels();
$paymentMarkup = '';
$receiptMarkup = '';
$payFailPage = $wInit->page->getFilename();
$idGuest = -1;
$idReserv = 0;
$idPsg = 0;

// Hosted payment return
try {

    if (is_null($payResult = PaymentSvcs::processSiteReturn($dbh, $_REQUEST)) === FALSE) {

        $receiptMarkup = $payResult->getReceiptMarkup();

        if ($payResult->getDisplayMessage() != '') {
            $paymentMarkup = HTMLContainer::generateMarkup('p', $payResult->getDisplayMessage());
        }
    }

} catch (RuntimeException $ex) {
    $paymentMarkup = $ex->getMessage();
}


if (isset($_POST['hdnCfmRid']) && isset($_POST['hdnCfmDocCode']) && isset($_POST['hdnCfmAmt'])) {

    $idReserv = intval(filter_var($_POST['hdnCfmRid'], FILTER_SANITIZE_NUMBER_INT), 10);
    $docId = intval(filter_var($_POST['hdnCfmDocCode'], FILTER_SANITIZE_NUMBER_INT), 10);
    $amt = filter_var($_POST['hdnCfmAmt'], FILTER_SANITIZE_STRING);
    $amt = preg_replace('/\D/', '', $amt);
    $amt = floatval($amt/100);
    
    $resv = Reservation_1::instantiateFromIdReserv($dbh, $idReserv);

    $idGuest = $resv->getIdGuest();

    $guest = new Guest($dbh, '', $idGuest);

    $notes = '';
    if (isset($_POST['tbCfmNotes'])) {
        $notes = filter_var($_POST['tbCfmNotes'], FILTER_SANITIZE_STRING);
    }

    //require(HOUSE . 'TemplateForm.php');
    //require(HOUSE . 'ConfirmationForm.php');

    try {
        $confirmForm = new ConfirmationForm($dbh, $docId);

        $formNotes = $confirmForm->createNotes($notes, FALSE);
        $form = '<!DOCTYPE html>' . $confirmForm->createForm($confirmForm->makeReplacements($resv, $guest, $amt, $formNotes));

        header('Content-Disposition: attachment; filename=confirm.doc');
        header("Content-Description: File Transfer");
        header('Content-Type: text/html');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        echo($form);
        exit();

    } catch (Exception $ex) {
        $paymentMarkup .= "Confirmation Form Error: " . $ex->getMessage();
    }
}

if (isset($uS->cofrid)) {
    $idReserv = $uS->cofrid;
    unset($uS->cofrid);
}


$resvObj = new ReserveData(array());


if (isset($_GET['id'])) {
    $idGuest = intval(filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT), 10);
}

if (isset($_GET['rid'])) {
    $idReserv = intval(filter_var($_GET['rid'], FILTER_SANITIZE_NUMBER_INT), 10);
}

if (isset($_GET['idPsg'])) {
    $idPsg = intval(filter_var($_GET['idPsg'], FILTER_SANITIZE_NUMBER_INT), 10);
}

if ($idReserv > 0 || $idGuest >= 0) {

    $mk1 = "<h2>Loading...</h2>";
    $resvObj->setIdResv($idReserv);
    $resvObj->setId($idGuest);
    $resvObj->setIdPsg($idPsg);

} else {
    // Guest Search markup
	$gMk = AbstractRole::createSearchHeaderMkup("gst", $labels->getString('MemberType', 'guest', 'Guest')." or " . $labels->getString('MemberType', 'patient', 'Patient') . " Name Search: ");
    $mk1 = $gMk['hdr'];

}

$resvAr = $resvObj->toArray();
$resvAr['patBD'] = $resvObj->getPatBirthDateFlag();
$resvAr['gstBD'] = false; //disable guest bd check on reservation
$resvAr['patAddr'] = $uS->PatientAddr;
$resvAr['gstAddr'] = $uS->GuestAddr;
$resvAr['addrPurpose'] = $resvObj->getAddrPurpose();
$resvAr['patAsGuest'] = $resvObj->getPatAsGuestFlag();
$resvAr['insistPayFilledIn'] = $uS->InsistCkinPayAmt;

$resvManagerOptions = [];
if($uS->UseIncidentReports){
	$resvManagerOptions["UseIncidentReports"] = true;
}else{
	$resvManagerOptions["UseIncidentReports"] = false;
}
$resvManagerOptionsEncoded = json_encode($resvManagerOptions);

// Page title
$title = $wInit->pageHeading;

// Imediate checkin, no prior reservation
if (isset($_GET['title'])) {
	
	$nowDT = new DateTime();
	$extendHours = intval($uS->ExtendToday);
	
	

	if ($extendHours > 0 && $extendHours < 9 && intval($nowDT->format('H')) < $extendHours) {
		$nowDT->sub(new DateInterval('P1D'));
	}
	
	$resvAr['arrival'] = $nowDT->format('M j, Y');
    $title = 'Check In';
    
}

$resvObjEncoded = json_encode($resvAr);

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $wInit->pageTitle; ?></title>
        <meta http-equiv="x-ua-compatible" content="IE=edge">
        <?php echo JQ_UI_CSS; ?>
        <?php echo HOUSE_CSS; ?>
        <?php echo DR_PICKER_CSS ?>
        <?php echo JQ_DT_CSS; ?>
        <?php echo NOTY_CSS; ?>
        <?php echo MULTISELECT_CSS; ?>
        <?php echo INCIDENT_CSS; ?>
        <?php echo GRID_CSS; ?>

        <?php echo FAVICON; ?>
<!--        Fix the ugly checkboxes-->
        <style>
            .ui-icon-background, .ui-state-active .ui-icon-background {background-color:#fff;}
        </style>

        <script type="text/javascript" src="<?php echo JQ_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo JQ_UI_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo MOMENT_JS ?>"></script>
        <script type="text/javascript" src="<?php echo STATE_COUNTRY_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo PRINT_AREA_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo ADDR_PREFS_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo CREATE_AUTO_COMPLETE_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo MULTISELECT_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo PAYMENT_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo RESV_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo DR_PICKER_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo VISIT_DIALOG_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo JQ_DT_JS ?>"></script>
        <script type="text/javascript" src="<?php echo NOTY_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo NOTY_SETTINGS_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo NOTES_VIEWER_JS ?>"></script>
        <script type="text/javascript" src="<?php echo PAG_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo INCIDENT_REP_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo RESV_MANAGER_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo JSIGNATURE_JS; ?>"></script>
        <?php if ($uS->PaymentGateway == AbstractPaymentGateway::INSTAMED) {echo INS_EMBED_JS;} ?>
        

    </head>
    <body <?php if ($wInit->testVersion) {echo "class='testbody'";} ?>>
        <?php echo $wInit->generatePageMenu() ?>
        <div id="contentDiv" class="container-fluid" style="margin-left: auto;">
            <h1><?php echo $title; ?> <span id="spnStatus" style="display:inline;"></span></h1>
            <div id="paymentMessage" style="clear:left;float:left; margin-top:5px;margin-bottom:5px; display:none;" class="ui-widget ui-widget-content ui-corner-all ui-state-highlight hhk-panel hhk-tdbox">
                <?php echo $paymentMarkup; ?>
            </div>
            <div id="guestSearch" style="padding-left:0;padding-top:0; margin-bottom:1.5em; clear:left; float:left;">
                <?php echo $mk1; ?>
            </div>
            <form action="Reserve.php" method="post"  id="form1">
                <div id="datesSection" style="display:none;" class="ui-widget ui-widget-header ui-state-default ui-corner-all hhk-panel mb-3"></div>
                <div id="famSection" style="font-size: .9em; display:none; min-width: 810px; margin-bottom:.5em;" class="ui-widget hhk-visitdialog mb-3"></div>
                <?php if ($uS->UseIncidentReports) { ?>
	            <div id="incidentsSection" style="font-size: .9em; display: none; min-width: 810px;" class="ui-widget hhk-visitdialog mb-3">
		            <div style="padding:2px; cursor:pointer;" class="ui-widget-header ui-state-default ui-corner-top hhk-incidentHdr">
			            <div class="hhk-checkinHdr" style="display: inline-block;">Incidents<span id="incidentCounts"></span></div>
			            <ul style="list-style-type:none; float:right;margin-left:5px;padding-top:2px;" class="ui-widget"><li class="ui-widget-header ui-corner-all" title="Open - Close"><span id="f_drpDown" class="ui-icon ui-icon-circle-triangle-n"></span></li></ul>
			        </div>
	                <div id="incidentContent" style="padding: 5px; display: none;" class="ui-corner-bottom hhk-tdbox ui-widget-content"></div>
	            </div>
	            <?php } ?>
                <div id="hospitalSection" style="font-size: .9em; display:none; min-width: 810px;"  class="ui-widget hhk-visitdialog mb-3"></div>
                <div id="resvSection" style="font-size:.9em; display:none; min-width: 810px;" class="ui-widget hhk-visitdialog mb-3"></div>
                <div style="clear:left; min-height: 70px;"></div>
                <div id="submitButtons" class="ui-corner-all" style="font-size:.9em; clear:both;">
                    <table >
                        <tr><td ><span id="pWarnings" style="display:none; font-size: 1.4em; border: 1px solid #ddce99;margin-bottom:3px; padding: 0 2px; color:red; background-color: yellow; float:right;"></span></td></tr>
                        <tr><td>
                        <input type="button" id="btnDelete" value="Delete" style="display:none;"/>
                        <input type="button" id="btnCheckinNow" value='Check-in Now' style="display:none;"/><input type="hidden" id="resvCkinNow" name="resvCkinNow" value="no" />
                        <input type="button" id="btnShowReg" value='Show Registration Form' style="display:none;"/>
                        <input type='button' id='btnDone' value='Continue' style="display:none;"/>
                            </td></tr>
                    </table>
                </div>

            </form>

            <div id="pmtRcpt" style="font-size: .9em; display:none;"><?php echo $receiptMarkup; ?></div>
            <div id="resDialog" class="hhk-tdbox hhk-visitdialog" style="display:none;font-size:.8em;"></div>
            <div id="hsDialog" class="hhk-tdbox hhk-visitdialog" style="display:none;font-size:.9em;"></div>
            <div id="psgDialog" class="hhk-tdbox hhk-visitdialog" style="display:none;"></div>
            <div id="activityDialog" class="hhk-tdbox hhk-visitdialog" style="display:none;font-size:.9em;"></div>
            <div id="faDialog" class="hhk-tdbox hhk-visitdialog" style="display:none;font-size:.9em;"></div>
            <div id="keysfees" style="display:none;font-size: .85em;"></div>

        </div>
        <form name="xform" id="xform" method="post"></form>
        <div id="confirmDialog" class="hhk-tdbox hhk-visitdialog" style="display:none;">
            <form id="frmConfirm" action="Reserve.php" method="post"></form>
        </div>
        <input type="hidden" value="<?php echo RoomRateCategories::Fixed_Rate_Category; ?>" id="fixedRate"/>
        <input type="hidden" value="<?php echo $payFailPage; ?>" id="payFailPage"/>
        <input type="hidden" value="<?php echo $labels->getString("momentFormats", "report", "MMM D, YYYY"); ?>" id="dateFormat"/>
        <input type="hidden" value="<?php echo $labels->getString('MemberType', 'visitor', 'Guest'); ?>" id="visitorLabel" />
        <input type="hidden" value="<?php echo $labels->getString('MemberType', 'guest', 'Guest'); ?>" id="guestLabel" />
        <input type="hidden" value='<?php echo $resvObjEncoded; ?>' id="resv"/>
        <input type="hidden" value='<?php echo $resvManagerOptionsEncoded; ?>' id="resvManagerOptions"/>
        <input type="hidden" value='<?php echo $paymentMarkup; ?>' id="paymentMarkup"/>
        <script type="text/javascript" src="<?php echo RESERVE_JS; ?>"></script>
    </body>
</html>
