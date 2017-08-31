<?php
/**
 * Reserve.php
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2017 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */
require ("homeIncludes.php");


require (DB_TABLES . 'nameRS.php');
require (DB_TABLES . 'registrationRS.php');
require (DB_TABLES . 'ActivityRS.php');
require (DB_TABLES . 'visitRS.php');
require (DB_TABLES . 'ReservationRS.php');
require (DB_TABLES . 'MercuryRS.php');
require (DB_TABLES . 'PaymentsRS.php');

require (MEMBER . 'Member.php');
require (MEMBER . 'IndivMember.php');
require (MEMBER . 'OrgMember.php');
require (MEMBER . "Addresses.php");
require (MEMBER . "EmergencyContact.php");

require (CLASSES . 'CleanAddress.php');
require (CLASSES . 'AuditLog.php');
require (CLASSES . 'MercPay/Gateway.php');
require (CLASSES . 'MercPay/MercuryHCClient.php');
require (PMT . 'Payments.php');
require (PMT . 'HostedPayments.php');
require (PMT . 'CreditToken.php');
require (CLASSES . 'PaymentSvcs.php');
require THIRD_PARTY . 'PHPMailer/PHPMailerAutoload.php';

require (HOUSE . 'psg.php');
require (HOUSE . 'RoleMember.php');
require (HOUSE . 'Role.php');
require (HOUSE . 'Guest.php');
require (HOUSE . 'Agent.php');
require (HOUSE . 'Patient.php');
require (HOUSE . 'Reservation_1.php');
require (HOUSE . 'ReserveData.php');
require (HOUSE . 'RegistrationForm.php');
require (HOUSE . 'Room.php');
require (HOUSE . 'Resource.php');
require (HOUSE . 'Registration.php');
require (HOUSE . 'Hospital.php');
require (HOUSE . 'VisitLog.php');
require (HOUSE . 'Constraint.php');
require (HOUSE . 'Attributes.php');


try {
    $wInit = new webInit();
} catch (Exception $exw) {
    die($exw->getMessage());
}

$dbh = $wInit->dbh;

// get session instance
$uS = Session::getInstance();

$menuMarkup = $wInit->generatePageMenu();

// Load the session with member - based lookups
$wInit->sessionLoadGenLkUps();
$wInit->sessionLoadGuestLkUps();

// Get labels
$labels = new Config_Lite(LABEL_FILE);
$paymentMarkup = '';
$receiptMarkup = '';

$idGuest = 0;
$idReserv = 0;
$idPsg = 0;

// Hosted payment return
if (is_null($payResult = PaymentSvcs::processSiteReturn($dbh, $uS->ccgw, $_POST)) === FALSE) {

    $receiptMarkup = $payResult->getReceiptMarkup();
    $paymentMarkup = HTMLContainer::generateMarkup('p', $payResult->getDisplayMessage());
}

if (isset($_POST['hdnCfmRid'])) {

    $idReserv = intval(filter_var($_POST['hdnCfmRid'], FILTER_SANITIZE_NUMBER_INT), 10);
    $resv = Reservation_1::instantiateFromIdReserv($dbh, $idReserv);

    $idGuest = $resv->getIdGuest();

    $guest = new Guest($dbh, '', $idGuest);

    $notes = '';
    if (isset($_POST['tbCfmNotes'])) {
        $notes = filter_var($_POST['tbCfmNotes'], FILTER_SANITIZE_STRING);
    }

    require(HOUSE . 'ConfirmationForm.php');

    $confirmForm = new ConfirmationForm($uS->ConfirmFile);

    $formNotes = $confirmForm->createNotes($notes, FALSE);
    $form = '<!DOCTYPE html>' . $confirmForm->createForm($dbh, $resv, $guest, 0, $formNotes);

    header('Content-Disposition: attachment; filename=confirm.doc');
    header("Content-Description: File Transfer");
    header('Content-Type: text/html');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');

    echo($form);
    exit();
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


if ($idReserv > 0 || $idGuest > 0) {

    $mk1 = "<h2>Loading...</h2>";
    $resvObj->setIdResv($idReserv);
    $resvObj->setId($idGuest);

} else {
    // Guest Search markup
    $gMk = Role::createSearchHeaderMkup("gst", "Guest or " . $labels->getString('MemberType', 'patient', 'Patient') . " Name Search: ");
    $mk1 = $gMk['hdr'];

}


// Instantiate the alert message control
$alertMsg = new alertMessage("divAlert1");
$alertMsg->set_DisplayAttr("none");
$alertMsg->set_Context(alertMessage::Success);
$alertMsg->set_iconId("alrIcon");
$alertMsg->set_styleId("alrResponse");
$alertMsg->set_txtSpanId("alrMessage");
$alertMsg->set_Text("uh-oh");

$resultMessage = $alertMsg->createMarkup();


$resvObjEncoded = json_encode($resvObj->toArray());

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $wInit->pageTitle; ?></title>
        <link rel="icon" type="image/png" href="../images/hhkIcon.png" />
        <link rel="stylesheet" href="css/daterangepicker.min.css">

        <?php echo JQ_UI_CSS; ?>
        <?php echo HOUSE_CSS; ?>
<!--        Fix the ugly checkboxes-->
        <style>.ui-icon-background, .ui-state-active .ui-icon-background {background-color:#fff;}</style>

        <script type="text/javascript" src="<?php echo JQ_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo JQ_UI_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo PAG_JS; ?>"></script>
        <script type="text/javascript" src="../js/jquery.daterangepicker.min.js"></script>
        <script type="text/javascript" src="<?php echo STATE_COUNTRY_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo PRINT_AREA_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo VERIFY_ADDRS_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo PAYMENT_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo RESV_JS; ?>"></script>

    </head>
    <body <?php if ($wInit->testVersion) {echo "class='testbody'";} ?>>
        <?php echo $menuMarkup; ?>
        <div id="contentDiv">
            <h1><?php echo $wInit->pageHeading; ?> <span id="spnStatus" sytle="margin-left:50px; display:inline;"></span></h1>
            <div id="divAlertMsg"><?php echo $resultMessage; ?></div>
            <div id="paymentMessage" style="clear:left;float:left; margin-top:5px;margin-bottom:5px; display:none;" class="ui-widget ui-widget-content ui-corner-all ui-state-highlight hhk-panel hhk-tdbox">
                <?php echo $paymentMarkup; ?>
            </div>

            <div id="guestSearch" style="padding-left:0;padding-top:0; margin-bottom:1.5em; clear:left; float:left;">
                <?php echo $mk1; ?>
            </div>

            <form action="Referral.php" method="post"  id="form1">
                <div id="datesSection" style="clear:left; float:left; display:none;" class="ui-widget ui-widget-header ui-state-default ui-corner-all hhk-panel"></div>
                <div id="famSection" style="font-size: .9em; clear:left; float:left; display:none; min-width: 810px; margin-bottom:.5em;" class="ui-widget"></div>

                <div id="hospitalSection" style="font-size: .9em; padding-left:0;margin-top:0; margin-bottom:.5em; clear:left; float:left; display:none; min-width: 810px;"  class="ui-widget hhk-visitdialog"></div>
                <div id="resvSection" style="clear:left; float:left; font-size:.9em; display:none; margin-bottom:.5em; min-width: 810px;" class="ui-widget hhk-visitdialog"></div>

                <div id="submitButtons" class="ui-corner-all" style="font-size:.9em; clear:both;">
                    <input type="button" id="btnDelete" value="Delete" style="display:none;"/>
                    <input type="button" id="btnCkinForm" value='Show Registration Form' style="display:none;"/>
                    <input id='btnDone' type='button' value='Find a Room' style="display:none;"/>
                </div>

            </form>
            <div id="pmtRcpt" style="font-size: .9em; display:none;"><?php echo $receiptMarkup; ?></div>
            <div id="resDialog" class="hhk-tdbox hhk-visitdialog" style="display:none;font-size:.9em;"></div>
            <div id="psgDialog" class="hhk-tdbox hhk-visitdialog" style="display:none;"></div>
            <div id="activityDialog" class="hhk-tdbox hhk-visitdialog" style="display:none;font-size:.9em;"></div>
            <div id="faDialog" class="hhk-tdbox hhk-visitdialog" style="display:none;font-size:.9em;"></div>

        </div>
        <form name="xform" id="xform" method="post"><input type="hidden" name="CardID" id="CardID" value=""/></form>
        <div id="confirmDialog" class="hhk-tdbox hhk-visitdialog" style="display:none;">
            <form id="frmConfirm" action="Reserve.php" method="post"></form>
        </div>

<script type="text/javascript">
function setupVehicle(veh) {
    var nextVehId = 1;
    var $cbVeh = veh.find('#cbNoVehicle');
    var $nextVeh = veh.find('#btnNextVeh');
    var $tblVeh = veh.find('#tblVehicle');

    $cbVeh.change(function() {
        if (this.checked) {
            $tblVeh.hide('scale, horizontal');
        } else {
            $tblVeh.show('scale, horizontal');
        }
    });
    $cbVeh.change();
    $nextVeh.button();

    $nextVeh.click(function () {
        veh.find('#trVeh' + nextVehId).show('fade');
        nextVehId++;
        if (nextVehId > 4) {
            $nextVeh.hide('fade');
        }
    });

}

function setupRate(data) {

    var reserve = {};
    if ($('#btnFapp').length > 0) {

        $("#faDialog").dialog({
            autoOpen: false,
            resizable: true,
            width: 650,
            modal: true,
            title: 'Income Chooser',
            close: function () {$('div#submitButtons').show();},
            open: function () {$('div#submitButtons').hide();},
            buttons: {
                Save: function() {
                    $.post('ws_ckin.php', $('#formf').serialize() + '&cmd=savefap' + '&rid=' + data.rid, function(rdata) {
                        try {
                            rdata = $.parseJSON(rdata);
                        } catch (err) {
                            alert('Bad JSON Encoding');
                            return;
                        }
                        if (rdata.gotopage) {
                            window.open(rdata.gotopage, '_self');
                        }
                        if (rdata.rstat && rdata.rstat == true) {
                            var selCat = $('#selRateCategory');
                            if (rdata.rcat && rdata.rcat != '' && selCat.length > 0) {
                                selCat.val(rdata.rcat);
                                selCat.change();
                            }
                        }
                    });
                    $(this).dialog("close");
                },
                "Exit": function() {
                    $(this).dialog("close");
                }
            }
        });

        $('#btnFapp').button().click(function() {
            getIncomeDiag(data.rid);
        });
    }

    reserve.rateList = data.resv.rdiv.ratelist;
    reserve.resources = data.resv.rdiv.rooms;
    reserve.visitFees = data.resv.rdiv.vfee;

    setupRates(reserve, $('#selResource').val());

    $('#selResource').change(function () {
        $('#selRateCategory').change();

        var selected = $("option:selected", this);
        selected.parent()[0].label === "Not Suitable" ? $('#hhkroomMsg').text("Not Suitable").show(): $('#hhkroomMsg').hide();
    });

}

function setupRoom(idReserv) {

    // Reservation history button
    $('.hhk-viewResvActivity').click(function () {
      $.post('ws_ckin.php', {cmd:'viewActivity', rid: $(this).data('rid')}, function(data) {
        data = $.parseJSON(data);

        if (data.error) {

            if (data.gotopage) {
                window.open(data.gotopage, '_self');
            }
            flagAlertMessage(data.error, true);
            return;
        }
         if (data.activity) {

            $('div#submitButtons').hide();
            $("#activityDialog").children().remove();
            $("#activityDialog").append($(data.activity));
            $("#activityDialog").dialog('open');
        }
        });

    });

    // Room selector update for constraints changes.
    $('input.hhk-constraintsCB').change( function () {
        updateRoomChooser(idReserv, $('#spnNumGuests').text(), $('#gstDate').val(), $('#gstCoDate').val());
    });

    // Show confirmation form button.
    $('#btnShowCnfrm').button().click(function () {
        var amount = $('#spnAmount').text();
        if (amount === '') {
            amount = 0;
        }
        $.post('ws_ckin.php', {cmd:'confrv', rid: $(this).data('rid'), amt: amount, eml: '0'}, function(data) {

            data = $.parseJSON(data);

            if (data.error) {
                if (data.gotopage) {
                    window.open(data.gotopage, '_self');
                }
                flagAlertMessage(data.error, true);
                return;
            }

             if (data.confrv) {

                $('div#submitButtons').hide();
                $("#frmConfirm").children().remove();
                $("#frmConfirm").html(data.confrv)
                    .append($('<div style="padding-top:10px;" class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><span>Email Address </span><input type="text" id="confEmail" value="'+data.email+'"/></div>'));

                $("#confirmDialog").dialog('open');
            }
        });
    });

}

function newGuestMarkup(data) {

    if (data.tblId) {

        var $famTbl = $('#' + data.tblId);

        $famTbl.append($(data.ntr)).append($(data.atr));

        $('.hhk-cbStay').checkboxradio({
            classes: {"ui-checkboxradio-label": "hhk-unselected-text" }
        });

        $('.hhk-lblStay').each(function () {
            if ($(this).data('stay') == '1') {
                $(this).click();
            }
        });

        $('.ckbdate').datepicker({
            yearRange: '-99:+00',
            changeMonth: true,
            changeYear: true,
            autoSize: true,
            maxDate: 0,
            dateFormat: 'M d, yy'
        });

        $('.hhk-togAddr').button();

    }
}

function addGuest(item, idReserv) {

    hideAlertMessage();

    // Check for guest already added.
    //

    if (item.No_Return !== undefined && item.No_Return !== '') {
        flagAlertMessage('This person is set for No Return: ' + item.No_Return + '.', true);
        return;
    }

    var resv = {};

    if (typeof item.id !== 'undefined') {
        resv.id = item.id;
    } else {
        return;
    }

    resv.rid = idReserv;
    resv.cmd = 'addThinGuest';

    getReserve(resv);

}

function familySection(data) {

    if (data.famSection === undefined) {
        return;
    }

    var fDiv = $(data.famSection.div).addClass('ui-widget-content').prop('id', 'divfamDetail');
    var expanderButton = $("<ul id='ulIcons' style='float:right;margin-left:5px;padding-top:1px;' class='ui-widget'/>")
        .append($("<li class='ui-widget-header ui-corner-all' title='Open - Close'>")
        .append($("<span id='f_drpDown' class='ui-icon ui-icon-circle-triangle-n'></span>")));
    var fHdr = $('<div id="divfamHdr" style="padding:2px; cursor:pointer;"/>')
            .append($(data.famSection.hdr))
            .append(expanderButton).append('<div style="clear:both;"/>');

    fHdr.addClass('ui-widget-header ui-state-default ui-corner-top');
    fHdr.click(function() {
        if (fDiv.css('display') === 'none') {
            fDiv.show('blind');
            fHdr.removeClass('ui-corner-all').addClass('ui-corner-top');
        } else {
            fDiv.hide('blind');
            fHdr.removeClass('ui-corner-top').addClass('ui-corner-all');
        }
    });

    $('#famSection')
            .empty()
            .append(fHdr)
            .append(fDiv)
            .show();

    $('.hhk-cbStay').checkboxradio({
        classes: {"ui-checkboxradio-label": "hhk-unselected-text" }
    });

    $('.hhk-lblStay').each(function () {
        if ($(this).data('stay') == '1') {
            $(this).click();
        }
    });

    $('.ckbdate').datepicker({
        yearRange: '-99:+00',
        changeMonth: true,
        changeYear: true,
        autoSize: true,
        maxDate: 0,
        dateFormat: 'M d, yy'
    });

    $('.hhk-togAddr').button();
    // toggle address row
    $('#divfamDetail').on('click', '.hhk-togAddr', function () {

        if ($(this).parents('tr').next('tr').css('display') === 'none') {
            $(this).parents('tr').next('tr').show();
            $(this).val('Hide');
        } else {
            $(this).parents('tr').next('tr').hide();
            $(this).val('Show');
        }
    });

    // set country and state selectors
    $('.hhk-addrPanel').find('select.bfh-countries').each(function() {
        var $countries = $(this);
        $countries.bfhcountries($countries.data());
    });

    $('.hhk-addrPanel').find('select.bfh-states').each(function() {
        var $states = $(this);
        $states.bfhstates($states.data());
    });

    $('.hhk-phemtabs').tabs();

    verifyAddrs('#divfamDetail');

    $('input.hhk-zipsearch').each(function() {
        var lastXhr;
        createZipAutoComplete($(this), 'ws_admin.php', lastXhr);
    });

    createAutoComplete($('#txtPersonSearch'), 3, {cmd: 'role', gp:'1'}, function (item) {
        addGuest(item, data.rid);
    });

}

function expectedDateRange(data) {

    $('#datesSection').children().remove();
    $('#datesSection').append($(data.expDates));

    var gstDate = $('#gstDate'),
        gstCoDate = $('#gstCoDate');

    $('#spnRangePicker').dateRangePicker(
    {
        format: 'MMM D, YYYY',
        separator : ' to ',
        minDays: 1,
        getValue: function()
        {
            if (gstDate.val() && gstCoDate.val() ) {
                return gstDate.val() + ' to ' + gstCoDate.val();
            } else {
                return '';
            }
        },
        setValue: function(s,s1,s2)
        {
            gstDate.val(s1);
            gstCoDate.val(s2);
        }
    });

    $('#datesSection').show();
}

function hospitalSection(hosp) {

    var hDiv = $(hosp.div).addClass('ui-widget-content').prop('id', 'divhospDetail').hide();
    var expanderButton = $("<ul id='ulIcons' style='float:right;margin-left:5px;padding-top:1px;' class='ui-widget'/>")
        .append($("<li class='ui-widget-header ui-corner-all' title='Open - Close'>")
        .append($("<span id='h_drpDown' class='ui-icon ui-icon-circle-triangle-n'></span>")));
    var hHdr = $('<div id="divhospHdr" style="padding:2px; cursor:pointer;"/>')
            .append($(hosp.hdr))
            .append(expanderButton).append('<div style="clear:both;"/>');

    hHdr.addClass('ui-widget-header ui-state-default ui-corner-top');

    hHdr.click(function() {
        if (hDiv.css('display') === 'none') {
            hDiv.show('blind');
            hHdr.removeClass('ui-corner-all').addClass('ui-corner-top');
        } else {
            hDiv.hide('blind');
            hHdr.removeClass('ui-corner-top').addClass('ui-corner-all');
        }
    });

    $('#hospitalSection').empty().append(hHdr).append(hDiv);

    $('#txtEntryDate, #txtExitDate').datepicker();

    if ($('#txtAgentSch').length > 0) {
        createAutoComplete($('#txtAgentSch'), 3, {cmd: 'filter', basis: 'ra'}, getAgent);
        if ($('#a_txtLastName').val() === '') {
            $('.hhk-agentInfo').hide();
        }
    }

    if ($('#txtDocSch').length > 0) {
        createAutoComplete($('#txtDocSch'), 3, {cmd: 'filter', basis: 'doc'}, getDoc);
        if ($('#d_txtLastName').val() === '') {
            $('.hhk-docInfo').hide();
        }
    }

    $('#hospitalSection').show();
    if ($('#selHospital').val() === '') {
        hHdr.click();
    }
}

function resvSection(data) {

    var resv = data.resv;
    var $rDiv, $veh, $rHdr, $expanderButton;


    $rDiv = $('<div id="divResvDetail" style="padding:2px; float:left;" class="ui-widget-content ui-corner-bottom hhk-tdbox"/>');
    $rDiv.append($(resv.rdiv.rChooser));

    // Rate section
    if (resv.rdiv.rate !== undefined) {
        $rDiv.append($(resv.rdiv.rate));
    }

    // Stat and notes sections
    $rDiv.append($(resv.rdiv.rstat)).append($(resv.rdiv.notes));

    // Vehicle section
    if (resv.rdiv.vehicle !== undefined) {
        $veh = $(resv.rdiv.vehicle)
        $rDiv.append($veh);
        setupVehicle($veh);
    }

    // Header
    $expanderButton = $("<ul id='ulIcons' style='float:right;margin-left:5px;padding-top:1px;' class='ui-widget'/>")
        .append($("<li class='ui-widget-header ui-corner-all' title='Open - Close'>")
        .append($("<span id='r_drpDown' class='ui-icon ui-icon-circle-triangle-n'></span>")));
    $rHdr = $('<div id="divResvHdr" style="padding:2px; cursor:pointer;"/>')
            .append($(resv.hdr))
            .append($expanderButton).append('<div style="clear:both;"/>');

    $rHdr.addClass('ui-widget-header ui-state-default ui-corner-top');

    $rHdr.click(function() {
        if ($rDiv.css('display') === 'none') {
            $rDiv.show('blind');
            $rHdr.removeClass('ui-corner-all').addClass('ui-corner-top');
        } else {
            $rDiv.hide('blind');
            $rHdr.removeClass('ui-corner-top').addClass('ui-corner-all');
        }
    });

    // Add to the page.
    $('#resvSection').empty().append($rHdr).append($rDiv).show();

    setupRoom(data.rid);

    if (resv.rdiv.rate !== undefined) {
        setupRate(data);
    }

}

function transferToGw(data) {

    var xferForm = $('#xform');
    xferForm.children('input').remove();
    xferForm.prop('action', data.xfer);
    if (data.paymentId && data.paymentId != '') {
        xferForm.append($('<input type="hidden" name="PaymentID" value="' + data.paymentId + '"/>'));
    } else if (data.cardId && data.cardId != '') {
        xferForm.append($('<input type="hidden" name="CardID" value="' + data.cardId + '"/>'));
    } else {
        flagAlertMessage('PaymentId and CardId are missing!', true);
        return;
    }
    xferForm.submit();
}

function resvPicker(data, $faDiag) {
    "use strict";
    var buttons = {};

    $faDiag.empty()
        .append($(data.resvChooser))
        .children().find('input:button').button();

    $faDiag.children().find('.hhk-checkinNow').click(function () {
        window.open('CheckIn.php?rid=' + $(this).data('rid') + '&gid=' + data.id, '_self');
    });

    if (data.psgChooser && data.psgChooser !== '') {
        buttons[data.patLabel + ' Chooser'] = function() {
            $(this).dialog("close");
            psgChooser(data);
        };
    }

    if (data.resvTitle) {
        buttons['New ' + data.resvTitle] = function() {
            data.rid = -1;
            data.cmd = 'getresv';
            $(this).dialog("close");
            getReserve(data);
        };
    }

    buttons['Exit'] = function() {
        $(this).dialog("close");
        $('div#guestSearch').show();
        $('#gstSearch').val('').focus();
    };

    $faDiag.dialog('option', 'buttons', buttons);
    $faDiag.dialog('option', 'title', data.resvTitle + ' Chooser For: ' + data.fullName);
    $faDiag.dialog('open');

}

function psgChooser(data) {
    "use strict";

    $('#psgDialog')
        .empty()
        .append($(data.psgChooser))
        .dialog('option', 'buttons', {
            Open: function() {
                $('#psgDialog').dialog('close');
                getReserve({idPsg: $('#psgDialog input[name=cbselpsg]:checked').val(), id: data.id, cmd: 'getresv'});
            },
            Cancel: function () {
                $('#psgDialog').dialog('close');
                $('div#guestSearch').show();
                $('#gstSearch').val('');
            }
        })
        .dialog('option', 'title', data.patLabel + ' Chooser For: ' + data.fullName)
        .dialog('open');
}

function getReserve(sdata) {

    $.post('ws_resv.php', sdata, function(data) {

        try {
            data = $.parseJSON(data);
        } catch (err) {
            flagAlertMessage(err.message, true);
            return;
        }

        if (data.gotopage) {
            window.open(data.gotopage, '_self');
        }

        if (data.error) {
            flagAlertMessage(data.error, true);
        }

        loadResv(data);
    });

    $('div#guestSearch').hide();

}

function loadResv(data) {

    if (data.xfer) {
        transferToGw(data);
    }

    if (data.resvChooser && data.resvChooser !== '') {
        resvPicker(data, $('#resDialog'));
        return;
    } else if (data.psgChooser && data.psgChooser !== '') {
        psgChooser(data);
        return;
    }

    if (data.famSection) {
        familySection(data);
    }

    // Expected Dates Control
    if (data.expDates !== undefined && data.expDates !== '') {
        expectedDateRange(data);
    }

    // Hospital
    if (data.hosp !== undefined) {
        hospitalSection(data.hosp);
    }

    // Reservation
    if (data.resv !== undefined) {
        resvSection(data);

        // String together some events
        $('#famSection').on('click', '.hhk-lblStay', function () {

        });
    }

    if (data.addPerson !== undefined) {
        newGuestMarkup(data.addPerson);
    }

}


$(document).ready(function() {
    "use strict";
    var $guestSearch = $('#gstSearch');
    var resv = $.parseJSON('<?php echo $resvObjEncoded; ?>');

    $.widget( "ui.autocomplete", $.ui.autocomplete, {
        _resizeMenu: function() {
            var ul = this.menu.element;
            ul.outerWidth( Math.max(
                    ul.width( "" ).outerWidth() + 1,
                    this.element.outerWidth()
            ) * 1.1 );
        }
    });

    $('#btnDone, #btnCkinForm, #btnDelete').button();

    $('#btnCkinForm').click(function () {
        if ($(this).data('rid') > 0) {
            window.open('ShowRegForm.php?rid=' + $(this).data('rid'), '_blank');
        }
    });

    $("#resDialog").dialog({
        autoOpen: false,
        resizable: true,
        width: 900,
        modal: true,
    });

    $('#confirmDialog').dialog({
        autoOpen: false,
        resizable: true,
        width: 850,
        modal: true,
        title: 'Confirmation Form',
        close: function () {$('div#submitButtons').show(); $("#frmConfirm").children().remove();},
        buttons: {
            'Download MS Word': function () {
                var $confForm = $("form#frmConfirm");
                $confForm.append($('<input name="hdnCfmRid" type="hidden" value="' + $('#btnShowCnfrm').data('rid') + '"/>'))
                $confForm.submit();
            },
            'Send Email': function() {
                $.post('ws_ckin.php', {cmd:'confrv', rid: $('#btnShowCnfrm').data('rid'), eml: '1', eaddr: $('#confEmail').val(), amt: $('#spnAmount').text(), notes: $('#tbCfmNotes').val()}, function(data) {
                    data = $.parseJSON(data);
                    if (data.gotopage) {
                        window.open(data.gotopage, '_self');
                    }
                    flagAlertMessage(data.mesg, true);
                });
                $(this).dialog("close");
            },
            "Cancel": function() {
                $(this).dialog("close");
            }
        }
    });

    $("#activityDialog").dialog({
        autoOpen: false,
        resizable: true,
        width: 900,
        modal: true,
        title: 'Reservation Activity Log',
        close: function () {$('div#submitButtons').show();},
        open: function () {$('div#submitButtons').hide();},
        buttons: {
            "Exit": function() {
                $(this).dialog("close");
            }
        }
    });

    $("#psgDialog").dialog({
        autoOpen: false,
        resizable: true,
        width: 500,
        modal: true,
        title: resv.patLabel + ' Chooser',
        close: function (event, ui) {$('div#submitButtons').show();},
        open: function (event, ui) {$('div#submitButtons').hide();}
    });

    function getGuest(item) {

        hideAlertMessage();
        if (item.No_Return !== undefined && item.No_Return !== '') {
            flagAlertMessage('This person is set for No Return: ' + item.No_Return + '.', true);
            return;
        }

        if (typeof item.id !== 'undefined') {
            resv.id = item.id;
        } else if (typeof item.rid !== 'undefined') {
            resv.rid = item.rid;
        } else {
            return;
        }

        resv.fullName = item.fullName;
        resv.cmd = 'getresv';

        getReserve(resv);

    }

    if (parseInt(resv.id, 10) > 0 || parseInt(resv.rid, 10) > 0) {

        resv.cmd = 'getresv';
        getReserve(resv);

    } else {

        createAutoComplete($guestSearch, 3, {cmd: 'role', gp:'1'}, getGuest);

        // Phone number search
        createAutoComplete($('#gstphSearch'), 4, {cmd: 'role', gp:'1'}, getGuest);

        $guestSearch.keypress(function(event) {
            hideAlertMessage();
            $(this).removeClass('ui-state-highlight');
        });

        $guestSearch.focus();
    }
});
        </script>
    </body>
</html>
