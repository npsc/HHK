<?php
/**
 * homeIncludes.php
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2017 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */

define('JSV', '?v=14');

define('HOUSE_CSS', "<link href='css/house.css" . JSV . "' rel='stylesheet' type='text/css' />");

define('RESV_MANAGER_JS', 'js/resvManager-min.js' . JSV);
define('PAYMENT_JS', "js/payments-min.js" . JSV);
define('VISIT_DIALOG_JS', "js/visitDialog-min.js" . JSV);
define('INCIDENT_REP_JS', 'js/incidentReports.min.js' . JSV);
define('DOC_UPLOAD_JS', 'js/documentUpload.min.js' . JSV);
define('GUESTLOAD_JS', 'js/guestload-min.js' . JSV);
define('REGISTER_JS', 'js/register.js' . JSV);

define('RESV_JS', "js/resv.js" . JSV);
define('INVOICE_JS', "js/invoice.js" . JSV);
define('REPORTFIELDSETS_JS', "js/reportfieldSets.js" . JSV);
define('RESERVE_JS', 'js/reserve.js' . JSV);
define('CHECKIN_JS', 'js/checkin.js' . JSV);
define('CHECKINGIN_JS', 'js/checkingIn.js' . JSV);
define('RESCBUILDER_JS', 'js/rescBuilder.js' . JSV);
define('MISSINGDEMOG_JS', 'js/missingDemog.js' . JSV);
define('GUESTTRANSFER_JS', 'js/GuestTransfer.js' . JSV);
define('INS_EMBED_JS', '<script src="https://instamedprd.cachefly.net/Content/Js/embed.js" data-displaymode="embedded" data-hostname="https://online.instamed.com/providers" data-mobiledisplaymode="embedded"></script>');

define('GRID_CSS', "<link href='css/bootstrap-grid.min.css' rel='stylesheet' type='text/css' />");

/**
 * Includes
 */
require ('../functions/commonDefines.php');
require(FUNCTIONS . 'errorHandler.php');
require (THIRD_PARTY . '/autoload.php');
require (FUNCTIONS . 'commonFunc.php');


