<?php
/**
 * ShowRegForm.php
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2017 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */

require ("homeIncludes.php");


require(DB_TABLES . "visitRS.php");
require(DB_TABLES . "ReservationRS.php");
require(DB_TABLES . "registrationRS.php");

require (DB_TABLES . 'nameRS.php');
require (DB_TABLES . 'PaymentsRS.php');
require (DB_TABLES . 'MercuryRS.php');
require (DB_TABLES . 'AttributeRS.php');


require CLASSES . 'FinAssistance.php';
require (PMT . 'Payments.php');
require (PMT . 'CreditToken.php');
require (PMT . 'Receipt.php');


require (MEMBER . 'Member.php');
require (MEMBER . 'IndivMember.php');
require (MEMBER . 'OrgMember.php');
require (MEMBER . "Addresses.php");
require (MEMBER . "EmergencyContact.php");

require(HOUSE . "psg.php");
require (HOUSE . 'Registration.php');
require (HOUSE . 'RoleMember.php');
require (HOUSE . 'Role.php');
require (HOUSE . 'Guest.php');
require (HOUSE . 'Patient.php');
require (HOUSE . 'Resource.php');
require (HOUSE . 'Room.php');
require (HOUSE . 'Reservation_1.php');
require (HOUSE . 'ReservationSvcs.php');
require (HOUSE . 'Visit.php');
require (HOUSE . 'RegisterForm.php');
require (HOUSE . 'RegistrationForm.php');
require (HOUSE . 'Attributes.php');
require (HOUSE . 'Constraint.php');
require (HOUSE . 'Vehicle.php');

function getVisitFromGuest(\PDO $dbh, $guestId) {

    $stmt = $dbh->prepare("Select idVisit from stays where `Status` = :stat and idName = :id");
    $stmt->execute(array(':id' => $guestId, ':stat' => VisitStatus::Active));

    $idVisit = 0;
    $rows = $stmt->fetchAll(PDO::FETCH_NUM);

    if (count($rows) > 0) {
        $idVisit = $rows[0][0];
    }

    return $idVisit;
}

$wInit = new webInit(WebPageCode::Page);
$pageTitle = $wInit->pageTitle;

/* @var $dbh PDO */
$dbh = $wInit->dbh;

$uS = Session::getInstance();

$idVisit = 0;
$idGuest = 0;
$idResv = 0;

$regForm = '';
$sty = '';

if (isset($_GET['vid'])) {
    $idVisit = intval(filter_var($_REQUEST['vid'], FILTER_SANITIZE_STRING), 10);
}

if (isset($_GET['gid'])) {
    $idGuest = intval(filter_var($_REQUEST['gid'], FILTER_SANITIZE_STRING), 10);
}

if (isset($_GET['rid'])) {
    $idResv = intval(filter_var($_REQUEST['rid'], FILTER_SANITIZE_STRING), 10);
}

if ($idVisit == 0 && $idResv > 0) {
    $stmt = $dbh->query("Select idVisit from visit where idReservation = $idResv");
    $rows = $stmt->fetchAll(PDO::FETCH_NUM);

    if (count($rows) > 0) {
        $idVisit = $rows[0][0];
    }
}

// Generate Receipt
switch ($uS->RegForm) {

    case '1':
        // Template
        if ($idVisit == 0 && $idGuest > 0) {
            $idVisit = getVisitFromGuest($dbh, $idGuest);
        }

        if ($idVisit > 0 || $idResv > 0) {

            try {

                $regForm = RegisterForm::prepareReceipt($dbh, $idVisit, $idResv);
                $sty = RegisterForm::getStyling();

            } catch (\Hk_Exception_Runtime $hex) {
                $regForm = $hex->getMessage();
            }
        } else {
            $regForm = '<h2>Registration from available at checkin.</h2>';
        }
        break;


    case '2':
        //
        $reservArray = ReservationSvcs::generateCkinDoc($dbh, $idResv, $idVisit, '../images/registrationLogo.png', $uS->mode);

        $sty = $reservArray['style'];
        $regForm = $reservArray['doc'];
        unset($reservArray);
        break;

    default:
        $regForm = "Register form template not set in sys_config table.  ";

}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo $pageTitle; ?></title>
        <?php echo JQ_UI_CSS; ?>
        <?php echo HOUSE_CSS; ?>
        <link rel="icon" type="image/png" href="../images/hhkIcon.png" />
<!--        <style type="text/css" media="print">
            .PrintArea {margin:0; padding:0; font: 12px Arial, Helvetica,"Lucida Grande", serif; color: #000;}
            @page { margin: 1cm; }
        </style>-->
        <?php echo $sty; ?>
        <script type="text/javascript" src="<?php echo JQ_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo JQ_UI_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo PRINT_AREA_JS; ?>"></script>
        <script type='text/javascript'>
$(document).ready(function() {
    "use strict";
    var opt = {mode: 'popup',
        popClose: true,
        popHt      : $('div#PrintArea').height(),
        popWd      : 950,
        popX       : 20,
        popY       : 20,
        popTitle   : 'Guest Registration Form'};
    $('#btnPrint').button();
    $('#btnPrint').click(function() {
        $('div#PrintArea').printArea(opt);
    });
});
</script>
    </head>
    <body>
        <div style="margin:10px;">
            <input type="button" id="btnPrint" value="Print"/>
        </div>
        <div id="PrintArea">
            <?php echo $regForm; ?>
        </div>
    </body>
</html>
