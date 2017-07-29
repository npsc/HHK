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

$resvObj = new ReserveData(array());


if (isset($_GET['id'])) {
    $idGuest = intval(filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT), 10);
}

if (isset($_GET['rid'])) {
    $idReserv = intval(filter_var($_GET['rid'], FILTER_SANITIZE_NUMBER_INT), 10);
}


if ($idReserv > 0) {

    $mk1 = "<h2>Loading...</h2>";
    $resvObj->setIdResv($idReserv);

} else if ($idGuest > 0) {

    $mk1 = "<h2>Loading...</h2>";
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
                <div id="famSection" style="font-size: .9em; clear:left; float:left; display:none; min-width: 810px; margin-bottom:.5em;" class="ui-widget hhk-visitdialog"></div>
                <div id="hospitalSection" style="font-size: .9em; padding-left:0;margin-top:0; margin-bottom:.5em; clear:left; float:left; display:none; min-width: 810px;"  class="ui-widget hhk-visitdialog"></div>

            </form>
            <div id="pmtRcpt" style="font-size: .9em; display:none;"><?php echo $receiptMarkup; ?></div>
            <div id="resDialog" class="hhk-tdbox hhk-visitdialog" style="display:none;font-size:.9em;"></div>
            <div id="psgDialog" class="hhk-tdbox hhk-visitdialog" style="display:none;"></div>

        </div>
        <form name="xform" id="xform" method="post"><input type="hidden" name="CardID" id="CardID" value=""/></form>

        <script type="text/javascript">


function getReserve(sdata) {

    sdata.cmd = 'getresv';
    $('div#guestSearch').hide();

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

        loadResv(data);
    });

}

function resvPicker(data, $faDiag) {
    "use strict";
    var resv = reserv,
        buttons = {};

    $faDiag.children().remove();
    $faDiag.append($(data.resCh));
    $faDiag.children().find('input:button').button();

    $faDiag.children().find('.hhk-checkinNow').click(function () {
        window.open('CheckIn.php?rid=' + $(this).data('rid') + '&gid=' + data.id, '_self');
    });

    if (data.psgChooser && data.psgChooser !== '') {
        buttons[data.patLabel] = function() {
            $(this).dialog("close");
            psgChooser(data);
        };
    }

    if (data.resvTitle) {
        buttons[data.resvTitle] = function() {
            resv.idReserv = -1;
            $(this).dialog("close");
            loadResv(data);
        };
    }

    buttons['Exit'] = function() {$(this).dialog("close");};

    $faDiag.dialog('option', 'buttons', buttons);
    $faDiag.dialog('option', 'title', data.title);
    $faDiag.dialog('open');

}

function psgChooser(data) {
    "use strict";

    $('#psgDialog')
        .children().remove().end().append($(data.psgChooser))
        .dialog('option', 'buttons', {
            Open: function() {
                //data.idPsg = $('#psgDialog input[name=cbselpsg]:checked').val();
                $('#psgDialog').dialog('close');
                getReserve({idPsg: $('#psgDialog input[name=cbselpsg]:checked').val(), id: data.id});
            },
            Cancel: function () {
                $('#gstSearch').val('');
                $('#psgDialog').dialog('close');
            }
        })
        .dialog('open');
}

function loadResv(data) {

    if (data.xfer) {
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

    if (data.resvChooser && data.resvChooser !== '') {
        resvPicker(data, $('resDialog'));
        return;
    } else if (data.psgChooser && data.psgChooser !== '') {
        psgChooser(data)
        return;
    }

    if (data.famSection) {

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
                .append(fHdr).append(fDiv)
                .show();


        if (data.famSection.expDates !== undefined && data.famSection.expDates !== '') {

            $('#datesSection').children().remove();
            $('#datesSection').append($(data.famSection.expDates));

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
    }

    // Hospital
    if (data.hosp !== undefined) {
        var hDiv = $(data.hosp.div).addClass('ui-widget-content').prop('id', 'divhospDetail');
        var expanderButton = $("<ul id='ulIcons' style='float:right;margin-left:5px;padding-top:1px;' class='ui-widget'/>")
            .append($("<li class='ui-widget-header ui-corner-all' title='Open - Close'>")
            .append($("<span id='h_drpDown' class='ui-icon ui-icon-circle-triangle-n'></span>")));
        var hHdr = $('<div id="divhospHdr" style="padding:2px; cursor:pointer;"/>')
                .append($(data.hosp.hdr))
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

        $('#hospitalSection').show('blind');
        if ($('#selHospital').val() !== '' && data.rvstCode && data.rvstCode !== '') {
            hHdr.click();
        }
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

    $("#psgDialog").dialog({
        autoOpen: false,
        resizable: true,
        width: 500,
        modal: true,
        title: resv.patLabel + ' Support Group Chooser',
        close: function (event, ui) {$('div#submitButtons').show();},
        open: function (event, ui) {$('div#submitButtons').hide();}
    });

    function getGuest(item) {

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

        getReserve(resv);
    }

    if (parseInt(resv.id, 10) > 0 || parseInt(resv.rid, 10) > 0) {

        getReserve(resv);

    } else {

        createAutoComplete($guestSearch, 3, {cmd: 'role', gp:'1'}, getGuest);

        // Phone number search
        createAutoComplete($('#gstphSearch'), 4, {cmd: 'role', gp:'1'}, getGuest);

        $guestSearch.keypress(function(event) {
            $(this).removeClass('ui-state-highlight');
        });

        $guestSearch.focus();
    }
});
        </script>
    </body>
</html>
