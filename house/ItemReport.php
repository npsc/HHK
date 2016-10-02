<?php
/**
 * ItemReport.php
 *
 *
 * @category  house
 * @package   Hospitality HouseKeeper
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2016 <nonprofitsoftwarecorp.org>
 * @license   GPL and MIT
 * @link      https://github.com/ecrane57/Hospitality-HouseKeeper
 */

require ("homeIncludes.php");
require (PMT . 'Receipt.php');


try {
    $wInit = new webInit();
} catch (Exception $exw) {
    die("arrg!  " . $exw->getMessage());
}

$dbh = $wInit->dbh;

$pageTitle = $wInit->pageTitle;

// get session instance
$uS = Session::getInstance();

$menuMarkup = $wInit->generatePageMenu();

// Load the session with member - based lookups
$wInit->sessionLoadGenLkUps();
$wInit->sessionLoadGuestLkUps();

$config = new Config_Lite(ciCFG_FILE);

// Instantiate the alert message control
$alertMsg = new alertMessage("divAlert1");
$alertMsg->set_DisplayAttr("none");
$alertMsg->set_Context(alertMessage::Success);
$alertMsg->set_iconId("alrIcon");
$alertMsg->set_styleId("alrResponse");
$alertMsg->set_txtSpanId("alrMessage");
$alertMsg->set_Text("help");

$resultMessage = $alertMsg->createMarkup();

$isGuestAdmin = ComponentAuthClass::is_Authorized('guestadmin');

function doMarkupRow($r, $isLocal, $invoice_Statuses, &$total, &$tbl, &$sml, &$reportRows, $subsidyId) {

    $amt = $r['Amount'];

    $payStatusAttr = array();
    $attr['style'] = 'text-align:right;';


    $invNumber = $r['Invoice_Number'];

    if ($invNumber != '') {

        $iAttr = array('href'=>'ShowInvoice.php?invnum=' . $r['Invoice_Number'], 'style'=>'float:left;', 'target'=>'_blank');

        if ($r['Invoice_Deleted'] > 0) {
            $iAttr['style'] .= 'color:red;';
            $iAttr['title'] = 'Invoice is Deleted.';
        } else if ($r['Balance'] != 0) {

            $iAttr['title'] = 'Partial payment.';
            $invNumber .= HTMLContainer::generateMarkup('sup', '-p');
        }

        $invNumber = HTMLContainer::generateMarkup('a', $invNumber, $iAttr)
            .HTMLContainer::generateMarkup('span','', array('class'=>'ui-icon ui-icon-comment invAction', 'id'=>'invicon'.$r['idInvoice_Line'], 'data-stat'=>'view', 'data-iid'=>$r['idInvoice'], 'style'=>'cursor:pointer;', 'title'=>'View Items'));
    }

    $invoiceMkup = HTMLContainer::generateMarkup('span', $invNumber, array("style"=>'white-space:nowrap'));

    $dateDT = new DateTime($r['Invoice_Date']);

    $invoiceStatus = '';
    if (isset($invoice_Statuses[$r['Status']])) {
        $invoiceStatus = $invoice_Statuses[$r['Status']][1];
    }

    // Names
    if ($r['Sold_To_Id'] == $subsidyId) {
        $company = $r['Company'];
        $payorFirst = HTMLTable::makeTd('');
        $payorLast = HTMLTable::makeTd('');
    } else if ($r['Billing_Agent'] == VolMemberType::BillingAgent) {
        $company = $r['Company'];
        $payorFirst = HTMLTable::makeTd($r['Name_First']);
        $payorLast = HTMLTable::makeTd($r['Name_Last']);
    } else {
        $payorLast = HTMLTable::makeTd(HTMLContainer::generateMarkup('a', $r['Name_Last'], array('href'=>'GuestEdit.php?id=' . $r['Sold_To_Id'], 'title'=>'Click to go to the Guest Edit page.')));
        $payorFirst = HTMLTable::makeTd($r['Name_First']);
        $company = '';
    }

    $total += $amt;

    if ($isLocal) {

        $tbl->addBodyTr(
                HTMLTable::makeTd($r['Order_Number'] . '-' . $r['Suborder_Number'])
                .HTMLTable::makeTd($company)
                .$payorLast
                .$payorFirst
                .HTMLTable::makeTd($dateDT->format('M j, Y'))
                .HTMLTable::makeTd($r['Description'])
                .HTMLTable::makeTd($invoiceMkup)
                .HTMLTable::makeTd($invoiceStatus, $payStatusAttr)

                .HTMLTable::makeTd(number_format($amt, 2), $attr)
                );

    } else {

        $n = 0;
        $flds = array(
            $n++ => array('type' => "s",
                'value' => $r['Order_Number'] . '-' . $r['Suborder_Number']
            ),
            $n++ => array('type' => "s",
                'value' => ($r['Billing_Agent'] == VolMemberType::BillingAgent ? $r['Company'] : '')
            ),
            $n++ => array('type' => "s",
                'value' => $r['Name_Last']
            ),
            $n++ => array('type' => "s",
                'value' => $r['Name_First']
            ),
            $n++ => array('type' => "n",
                'value' => PHPExcel_Shared_Date::PHPToExcel(strtotime($r['Invoice_Date'])),
                'style' => PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX14
            ),
             $n++ => array('type' => "s",
                'value' => $r['Description']
            ),
           $n++ => array('type' => "s",
                'value' => $r['Invoice_Number']
            ),
            $n++ => array('type' => "s",
                'value' => $invoiceStatus
            ),
            $n++ => array('type' => "n",
                'value' => $amt
            )
        );

        $reportRows = OpenXML::writeNextRow($sml, $flds, $reportRows);

    }

}

$mkTable = '';  // var handed to javascript to make the report table or not.
$headerTableMu = '';
$dataTable = '';

$statusSelections = array();

$itemSelections = array();
$calSelection = '19';
$showDeleted = FALSE;

$year = date('Y');
$months = array(date('n'));       // logically overloaded.
$txtStart = '';
$txtEnd = '';
$start = '';
$end = '';


$monthArray = array(
    1 => array(1, 'January'),
    2 => array(2, 'February'),
    3 => array(3, 'March'), 4 => array(4, 'April'), 5 => array(5, 'May'), 6 => array(6, 'June'),
    7 => array(7, 'July'), 8 => array(8, 'August'), 9 => array(9, 'September'), 10 => array(10, 'October'), 11 => array(11, 'November'), 12 => array(12, 'December'));

if ($uS->fy_diff_Months == 0) {
    $calOpts = array(18 => array(18, 'Dates'), 19 => array(19, 'Month'), 21 => array(21, 'Cal. Year'), 22 => array(22, 'Year to Date'));
} else {
    $calOpts = array(18 => array(18, 'Dates'), 19 => array(19, 'Month'), 20 => array(20, 'Fiscal Year'), 21 => array(21, 'Calendar Year'), 22 => array(22, 'Year to Date'));
}

$statusList = readGenLookupsPDO($dbh, 'Invoice_Status');


// Items
$addnlCharges = readGenLookupsPDO($dbh, 'Addnl_Charge');

$stmt = $dbh->query("SELECT idItem, Description from item");
$itemList = array();

while($r = $stmt->fetch(PDO::FETCH_NUM)) {

    if ($r[0] == ItemId::LodgingDonate) {
        $r[1] = "Lodging Donation";
    } else if ($r[0] == ItemId::AddnlCharge) {
        $r[1] = "Additional Charges";
    }

    if ($r[0] == ItemId::DepositRefund && $uS->KeyDeposit === FALSE) {
        continue;
    } else if ($r[0] == ItemId::KeyDeposit && $uS->KeyDeposit === FALSE) {
        continue;
    } else if ($r[0] == ItemId::VisitFee && $uS->VisitFee === FALSE) {
        continue;
    } else if ($r[0] == ItemId::AddnlCharge && count($addnlCharges) == 0) {
        continue;
    } else if ($r[0] == ItemId::InvoiceDue) {
        continue;
    }

    $itemList[$r[0]] = $r;
}



if (isset($_POST['btnHere']) || isset($_POST['btnExcel'])) {

    $headerTable = new HTMLTable();
    $headerTable->addBodyTr(HTMLTable::makeTd('Report Generated: ', array('class'=>'tdlabel')) . HTMLTable::makeTd(date('M j, Y')));

    $local = TRUE;
    if (isset($_POST['btnExcel'])) {
        $local = FALSE;
    }

    if (isset($_POST['cbShoDel'])) {
        $showDeleted = TRUE;
    }


    if (isset($_POST['selIntMonth'])) {
        $months = filter_var_array($_POST['selIntMonth'], FILTER_SANITIZE_NUMBER_INT);
    }

    if (isset($_POST['selCalendar'])) {
        $calSelection = intval(filter_var($_POST['selCalendar'], FILTER_SANITIZE_NUMBER_INT), 10);
    }

    if (isset($_POST['selIntYear'])) {
        $year = intval(filter_var($_POST['selIntYear'], FILTER_SANITIZE_NUMBER_INT), 10);
    }

    if (isset($_POST['stDate'])) {
        $txtStart = filter_var($_POST['stDate'], FILTER_SANITIZE_STRING);
    }

    if (isset($_POST['enDate'])) {
        $txtEnd = filter_var($_POST['enDate'], FILTER_SANITIZE_STRING);
    }

    if (isset($_POST['selPayStatus'])) {
        $reqs = $_POST['selPayStatus'];
        if (is_array($reqs)) {
            $statusSelections = filter_var_array($reqs, FILTER_SANITIZE_STRING);
        }
    }

    if (isset($_POST['selItems'])) {
        $reqs = $_POST['selItems'];
        if (is_array($reqs)) {
            $itemSelections = filter_var_array($reqs, FILTER_SANITIZE_STRING);
        }
    }


    // Determine time span
    if ($calSelection == 20) {
        // fiscal year
        $adjustPeriod = new DateInterval('P' . $uS->fy_diff_Months . 'M');
        $startDT = new DateTime($year . '-01-01');

        $start = $startDT->sub($adjustPeriod)->format('Y-m-d 00:00:00');

        $endDT = new DateTime(($year + 1) . '-01-01');
        $end = $endDT->sub($adjustPeriod)->format('Y-m-d 00:00:00');

    } else if ($calSelection == 21) {
        // Calendar year
        $startDT = new DateTime($year . '-01-01');
        $start = $startDT->format('Y-m-d 00:00:00');

        $end = ($year + 1) . '-01-01 00:00:00';

    } else if ($calSelection == 18) {
        // Dates
        if ($txtStart != '') {
            $startDT = new DateTime($txtStart);
            $start = $startDT->format('Y-m-d 00:00:00');
        }

        if ($txtEnd != '') {
            $endDT = new DateTime($txtEnd);
            $end = $endDT->format('Y-m-d 23:59:59');
        }

    } else if ($calSelection == 22) {
        // Year to date
        $start = $year . '-01-01 00:00:00';

        $endDT = new DateTime($year . date('m') . date('d'));

        $end = $endDT->add(new DateInterval('P1D'))->format('Y-m-d 00:00:00');


    } else {
        // Months
        $interval = 'P' . count($months) . 'M';
        $month = $months[0];
        $start = $year . '-' . $month . '-01 00:00:00';

        $endDate = new DateTime($start);
        $endDate->add(new DateInterval($interval));

        $end = $endDate->format('Y-m-d 00:00:00');
    }



    $whDates = " and i.Invoice_Date < '$end' and i.Invoice_Date >= '$start' ";

    $endDT = new DateTime($end);
    $endDT->sub(new DateInterval('P1D'));

    $headerTable->addBodyTr(HTMLTable::makeTd('Reporting Period: ', array('class'=>'tdlabel')) . HTMLTable::makeTd(date('M j, Y', strtotime($start)) . ' thru ' . date('M j, Y', strtotime($end))));



    $whStatus = '';
    $payStatusText = '';
    foreach ($statusSelections as $s) {
        if ($s != '') {
            // Set up query where part.
            if ($whStatus == '') {
                $whStatus = "'" . $s . "'";
            } else {
                $whStatus .= ",'".$s . "'";
            }

            if ($payStatusText == '') {
                $payStatusText = $statusList[$s][1];
            } else {
                $payStatusText .= ', ' . $statusList[$s][1];
            }
        }
    }

    if ($whStatus != '') {
        $whStatus = " and i.Status in (" . $whStatus . ") ";
    } else {
        $payStatusText = 'All';
    }

    $headerTable->addBodyTr(HTMLTable::makeTd('Invoice Statuses: ', array('class'=>'tdlabel')) . HTMLTable::makeTd($payStatusText));

    if ($showDeleted) {
        $whDeleted = ' 1=1 ';
    } else {
        $whDeleted = ' i.Deleted = 0 and il.Deleted = 0 ';
    }


    $whItem = '';
    $itemText = '';
    foreach ($itemSelections as $s) {
        if ($s != '') {
            // Set up query where part.
            if ($whItem == '') {
                $whItem = $s ;
            } else {
                $whItem .= ",".$s;
            }

            if ($itemText == '') {
                $itemText .= $itemList[$s][1];
            } else {
                $itemText .= ', ' . $itemList[$s][1];
            }
        }
    }

    if ($whItem != '') {
        $whItem = " and il.Item_Id in (" . $whItem . ") ";
    } else {
        $itemText = 'All';
    }

    $headerTable->addBodyTr(HTMLTable::makeTd('Items: ', array('class'=>'tdlabel')) . HTMLTable::makeTd($itemText));


        $query = "select
    il.idInvoice_Line,
    i.idInvoice,
    i.Invoice_Number,
    i.Delegated_Invoice_Id,
    i.Amount as `Invoice_Amount`,
    i.Sold_To_Id,
    i.idGroup,
    i.Order_Number,
    i.Suborder_Number,
    i.Invoice_Date,
    i.`Status`,
    i.Carried_Amount,
    i.Balance,
    i.Deleted as Invoice_Deleted,
    il.Price,
    il.Amount,
    il.Quantity,
    il.Description,
    il.Item_Id,
    il.Period_Start,
    il.Period_End,
    il.Deleted as Line_Deleted,

    ifnull(n.Name_Last, '') as Name_Last,
    ifnull(n.Name_First, '') as Name_First,
    ifnull(n.Company, '') as Company,
    ifnull(nv.Vol_Code, '') as `Billing_Agent`
from
    invoice_line il join invoice i ON il.Invoice_Id = i.idInvoice
    left join `name` n on i.Sold_To_Id = n.idName
    left join name_volunteer2 nv on nv.idName = n.idName and nv.Vol_Category = 'Vol_Type' and nv.Vol_Code = '" . VolMemberType::BillingAgent . "'
where $whDeleted  $whDates  $whItem  $whStatus order by i.idInvoice, il.idInvoice_Line";

    $stmt = $dbh->query($query);

    $tbl = null;
    $sml = null;
    $reportRows = 0;


    if ($local) {

        $tbl = new HTMLTable();
        $tbl->addHeaderTr(HTMLTable::makeTh('Visit Id').HTMLTable::makeTh('Organization').HTMLTable::makeTh('Last').HTMLTable::makeTh('First').HTMLTable::makeTh('Date').HTMLTable::makeTh('Item').HTMLTable::makeTh('Inv #')
                .HTMLTable::makeTh('Status').HTMLTable::makeTh('Amount'));

    } else {

        require_once CLASSES . 'OpenXML.php';

        $reportRows = 1;
        $file = 'PaymentReport';
        $sml = OpenXML::createExcel($uS->username, 'Payment Report');

        // build header
        $hdr = array();
        $n = 0;

        $hdr[$n++] = "Visit Id";
        $hdr[$n++] = "Organization";
        $hdr[$n++] = "Last";
        $hdr[$n++] = "First";
        $hdr[$n++] = "Date";
        $hdr[$n++] = "Item";
        $hdr[$n++] = "Invoice Number";
        $hdr[$n++] = "Status";
        $hdr[$n++] = "Amount";

        OpenXML::writeHeaderRow($sml, $hdr);
        $reportRows++;
    }


    $total = 0.0;


    $name_lk = $uS->nameLookups;
    $name_lk['Invoice_Status'] = $statusList;
    $uS->nameLookups = $name_lk;

    // Now the data ...
    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {

        doMarkupRow($r, $local, $statusList, $total, $tbl, $sml, $reportRows, $uS->subsidyId);

    }



    // Finalize and print.
    if ($local) {

        $tbl->addFooterTr(HTMLTable::makeTd('', array('colspan'=>'7'))
            .HTMLTable::makeTd('Total:', array('style'=>'text-align:right;font-weight:bold; border-top:2px solid black;'))
            .HTMLTable::makeTd('$'.number_format($total,2), array('style'=>'text-align:right;font-weight:bold; border-top:2px solid black;'))
            );

        $dataTable = $tbl->generateMarkup(array('id'=>'tblrpt'));
        $mkTable = 1;
        $headerTableMu = $headerTable->generateMarkup();

    } else {

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $file . '.xlsx"');
        header('Cache-Control: max-age=0');

        OpenXML::finalizeExcel($sml);
        exit();

    }

}

// Setups for the page.

$monSize = 5;

// Prepare controls

$statusSelector = HTMLSelector::generateMarkup(
                HTMLSelector::doOptionsMkup($statusList, $statusSelections), array('name' => 'selPayStatus[]', 'size' => '6', 'multiple' => 'multiple'));

$itemSelector = HTMLSelector::generateMarkup(
                HTMLSelector::doOptionsMkup($itemList, $itemSelections), array('name' => 'selItems[]', 'size' => '6', 'multiple' => 'multiple'));

$dAttrs = array('name'=>'cbShoDel', 'id'=>'cbShoDel', 'type'=>'checkbox', 'style'=>'margin-right:.3em;');

if ($showDeleted) {
    $dAttrs['checked'] = 'checked';
}
$shoDeletedCb = HTMLInput::generateMarkup('', $dAttrs)
        . HTMLContainer::generateMarkup('label', 'Show Deleted Invoices', array('for'=>'cbShoDel'));

$monthSelector = HTMLSelector::generateMarkup(HTMLSelector::doOptionsMkup($monthArray, $months, FALSE), array('name' => 'selIntMonth[]', 'size'=>$monSize, 'multiple'=>'multiple'));
$yearSelector = HTMLSelector::generateMarkup(getYearOptionsMarkup($year, $config->getString('site', 'Start_Year', '2010'), $uS->fy_diff_Months, FALSE), array('name' => 'selIntYear', 'size'=>'5'));
$calSelector = HTMLSelector::generateMarkup(HTMLSelector::doOptionsMkup($calOpts, $calSelection, FALSE), array('name' => 'selCalendar', 'size'=>'5'));

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $pageTitle; ?></title>
        <?php echo JQ_UI_CSS; ?>
        <?php echo TOP_NAV_CSS; ?>
        <?php echo HOUSE_CSS; ?>
        <?php echo JQ_DT_CSS ?>
        <link rel="icon" type="image/png" href="../images/hhkIcon.png" />
        <script type="text/javascript" src="<?php echo $wInit->resourceURL; ?><?php echo JQ_JS ?>"></script>
        <script type="text/javascript" src="<?php echo $wInit->resourceURL; ?><?php echo JQ_UI_JS ?>"></script>
        <script type="text/javascript" src="<?php echo $wInit->resourceURL; ?><?php echo JQ_DT_JS ?>"></script>
        <script type="text/javascript" src="<?php echo $wInit->resourceURL; ?><?php echo PRINT_AREA_JS ?>"></script>
<script type="text/javascript">
function invoiceAction(idInvoice, action, eid, container, show) {
    $.post('ws_resc.php', {cmd: 'invAct', iid: idInvoice, x:eid, action: action, 'sbt':show},
      function(data) {
        if (data) {
            try {
                data = $.parseJSON(data);
            } catch (err) {
                alert("Parser error - " + err.message);
                return;
            }
            if (data.error) {
                if (data.gotopage) {
                    window.location.assign(data.gotopage);
                }
                //flagAlertMessage(data.error, true);
                return;
            }
            if (data.markup) {
                var contr = $(data.markup);
                if (container != undefined && container != '') {
                    $(container).append(contr);
                } else {
                    $('body').append(contr);
                }
                $('body').append(contr);
                contr.position({
                    my: 'left top',
                    at: 'left bottom',
                    of: "#" + data.eid
                });
            }
        }
    });
}
    $(document).ready(function() {
    $('#contentDiv').css('margin-top', $('#global-nav').css('height'));
        var isGuestAdmin = '<?php echo $isGuestAdmin; ?>';
        var makeTable = '<?php echo $mkTable; ?>';
        $('#btnHere, #btnExcel').button();
        $('.ckdate').datepicker({
            yearRange: '-05:+01',
            changeMonth: true,
            changeYear: true,
            autoSize: true,
            numberOfMonths: 1,
            dateFormat: 'M d, yy'
        });
        $('#selCalendar').change(function () {
            if ($(this).val() && $(this).val() != '19') {
                $('#selIntMonth').hide();
            } else {
                $('#selIntMonth').show();
            }
            if ($(this).val() && $(this).val() != '18') {
                $('.dates').hide();
                $('#selIntYear').show();
            } else {
                $('.dates').show();
                $('#selIntYear').hide();
            }
        });
        $('#selCalendar').change();
        // disappear the pop-up room chooser.
        $(document).mousedown(function (event) {
            var target = $(event.target);
            if ($('div#pudiv').length > 0 && target[0].id !== 'pudiv' && target.parents("#" + 'pudiv').length === 0) {
                $('div#pudiv').remove();
            }
        });
        if (makeTable === '1') {
            $('div#printArea').css('display', 'block');
            try {
                listTable = $('#tblrpt').dataTable({
                    "iDisplayLength": 50,
                    "aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
                    "dom": '<"top"ilf>rt<"bottom"ilp><"clear">',
                    "order": [[ 3, 'asc' ]]
                });
            } catch (err) {

            }
            $('#printButton').button().click(function() {
                $("div#printArea").printArea();
            });
            $('#tblrpt').on('click', '.invAction', function (event) {
                invoiceAction($(this).data('iid'), 'view', event.target.id, '', true);
            });
        }
    });
 </script>
    </head>
    <body <?php if ($wInit->testVersion) echo "class='testbody'"; ?>>
        <?php echo $menuMarkup; ?>
        <div id="contentDiv">
        <h2><?php echo $wInit->pageHeading; ?></h2>
        <div id="divAlertMsg"><?php echo $resultMessage; ?></div>
            <div id="vcategory" class="ui-widget ui-widget-content ui-corner-all hhk-member-detail hhk-tdbox hhk-visitdialog" style="clear:left; min-width: 400px; padding:10px;">
                <form id="fcat" action="ItemReport.php" method="post">
                    <table style="float: left;">
                        <tr>
                            <th colspan="3">Time Period</th>
                        </tr>
                        <tr>
                            <th>Interval</th>
                            <th style="min-width:100px; ">Month</th>
                            <th>Year</th>
                        </tr>
                        <tr>
                            <td><?php echo $calSelector; ?></td>
                            <td><?php echo $monthSelector; ?></td>
                            <td><?php echo $yearSelector; ?></td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <span class="dates" style="margin-right:.3em;">Start:</span>
                                <input type="text" value="<?php echo $txtStart; ?>" name="stDate" id="stDate" class="ckdate dates" style="margin-right:.3em;"/>
                                <span class="dates" style="margin-right:.3em;">End:</span>
                                <input type="text" value="<?php echo $txtEnd; ?>" name="enDate" id="enDate" class="ckdate dates"/></td>
                        </tr>
                    </table>
                    <table style="float: left;">
                        <tr>
                            <th colspan="2">Invoice Status</th>
                        </tr>
                        <tr>
                           <td><?php echo $statusSelector; ?></td>
                        </tr>
                    </table>
                    <table style="float: left;">
                        <tr>
                            <th colspan="2">Item Filter</th>
                        </tr>
                        <tr>
                           <td><?php echo $itemSelector; ?></td>
                        </tr>
                    </table>
                    <table style="width:100%; clear:both;">
                        <tr>
                            <td style="width:50%;"><?php echo $shoDeletedCb; ?></td>
                            <td><input type="submit" name="btnHere" id="btnHere" value="Run Here"/></td>
                            <td><input type="submit" name="btnExcel" id="btnExcel" value="Download to Excel"/></td>
                        </tr>
                    </table>
                </form>
            </div>
            <div style="clear:both;"></div>
            <div id="printArea" class="ui-widget ui-widget-content hhk-tdbox" style="display:none; font-size: .9em; padding: 5px; padding-bottom:25px;">
                <div><input id="printButton" value="Print" type="button"/></div>
                <div style="margin-top:10px; margin-bottom:10px; min-width: 350px;">
                    <?php echo $headerTableMu; ?>
                </div>
                <?php echo $dataTable; ?>
            </div>
        </div>
    </body>
</html>
