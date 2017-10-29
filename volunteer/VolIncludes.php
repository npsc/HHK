<?php
/**
 * VolIncludes.php
 *
 * @category  Site
 * @package   Hospitality HouseKeeper
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2014 <nonprofitsoftwarecorp.org>
 * @license   GPL and MIT
 * @link      https://github.com/ecrane57/Hospitality-HouseKeeper
 */

define('P_BASE', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);
define('MAX_IDLE_TIME', '1800');

// FInd the vendor directory
$dirxx = '../vendor';
if (file_exists($dirxx) === FALSE) {
    $dirxx = '../' . $dirxx;
    if (file_exists($dirxx) === FALSE) {
        $dirxx = '../' . $dirxx;
        if (file_exists($dirxx) === FALSE) {
            $dirxx = '../' . $dirxx;
            if (file_exists($dirxx) === FALSE) {
                throw new Exception('Cannot find the vendor directory.');
            }
        }
    }
}

define('THIRD_PARTY', $dirxx . DS);

define('REL_BASE_DIR', ".." . DS);
define('ciCFG_FILE', REL_BASE_DIR . 'conf' . DS . 'site.cfg' );
define('LABEL_FILE', REL_BASE_DIR . 'conf' . DS . 'labels.cfg' );
define( 'ADMIN_DIR', REL_BASE_DIR . "admin" . DS);
define('REL_BASE_SITE', "../");
define('CLASSES', REL_BASE_DIR . 'classes' . DS);
define('DB_TABLES', CLASSES . 'tables' . DS);
/**
 * SEC path to security classes
 */
define('SEC', CLASSES . 'sec' . DS);
/**
 * PMT path to payment classes
 */
define('PMT', CLASSES . 'Payment' . DS);
define('FUNCTIONS', REL_BASE_DIR . 'functions' .DS);
define('MEMBER', CLASSES . 'member' . DS);

// paths
define('JQ_UI_CSS', 'css/sunny/jquery-ui.min.css');
define('JQ_DT_CSS', 'css/datatables.min.css');
define('JQ_UI_JS', '../js/jquery-ui.min.js');
define('JQ_JS', '../js/jquery-3.1.1.min.js');
define('JQ_DT_JS', '../js/datatables.min.js');
define('LOGIN_JS', "../js/login.js");

define('FULLC_JS', '../js/fullcalendar.min.js');
define('PAG_JS', '../js/pag.js');
define('FULLC_CSS', 'css/fullcalendar.css');

define('PRINT_AREA_JS', "../js/jquery.PrintArea.js");
define('PUBLIC_CSS', "<link href='css/publicStyle.css' rel='stylesheet' type='text/css' />");

date_default_timezone_set('America/Chicago');

require (FUNCTIONS . 'commonFunc.php');
require (CLASSES . 'config'. DS . 'Lite.php');
require (SEC . 'sessionClass.php');
require (CLASSES . 'alertMessage.php');
require (CLASSES . 'Exception_hk/Hk_Exception.php');
require (CLASSES . 'HTML_Controls.php');
require (SEC . 'SecurityComponent.php');
require (SEC . 'ScriptAuthClass.php');
require (CLASSES . 'SysConst.php');
require (SEC . 'webInit.php');
require (CLASSES . 'Purchase/PriceModel.php');
require (CLASSES . 'PDOdata.php');
require (DB_TABLES . 'HouseRS.php');

