<?php
use HHK\sec\Login;
use HHK\sec\Labels;
use HHK\sec\Session;
use HHK\sec\ScriptAuthClass;
use HHK\sec\SecurityComponent;
use HHK\House\GuestReferral;

/**
 * GuestReferral.php
 *
 * Guest/public facing referral form
 *
 * @author    Will Ireland <wireland@nonprofitsoftwarecorp.org>
 * @copyright 2010-2020 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */
require ("homeIncludes.php");

// get session instance
$uS = Session::getInstance();

try {
    $login = new Login();
    $dbh = $login->initHhkSession(ciCFG_FILE);
    
} catch (\Exception $ex) {
    session_unset();
    http_response_code(500);
    exit ();
}

// Load the page information
try {
    $page = new ScriptAuthClass($dbh);
} catch (Exception $ex) {
    $uS->destroy(true);
    exit("Error accessing web page data table: " . $ex->getMessage());
}

$cspURL = $page->getHostName();

//header('X-Frame-Options: SAMEORIGIN');
//header("Content-Security-Policy: default-src $cspURL; style-src $cspURL 'unsafe-inline';"); // FF 23+ Chrome 25+ Safari 7+ Opera 19+
//header("X-Content-Security-Policy: default-src $cspURL; style-src $cspURL 'unsafe-inline';"); // IE 10+

if (SecurityComponent::isHTTPS()) {
    header('Strict-Transport-Security: max-age=31536000'); // FF 4 Chrome 4.0.211 Opera 12
}


$guestReferral = new GuestReferral($dbh);
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
        <title><?php echo $uS->siteName; ?></title>
        <?php echo JQ_UI_CSS; ?>
        <?php echo HOUSE_CSS; ?>
        <?php echo GRID_CSS; ?>
        <?php echo FAVICON; ?>
        <script type="text/javascript" src="<?php echo JQ_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo JQ_UI_JS; ?>"></script>
        
        <style type="text/css">
            
            table {
                width: 100%;
            }
            
            input, select:not(.ui-datepicker select) {
                width: 100% !important;
                padding: 0.5em;
            }
            
            input#submit {
                width: initial !important;
                display: block;
                margin: 0 auto;
                background: <?php echo $guestReferral->colors['header']['bg']; ?>;
                border: 1px solid <?php echo $guestReferral->colors['header']['bg']; ?>;
                border-radius: 2em;
                color: <?php echo $guestReferral->colors['header']['txt']; ?>;
                padding: 1em;
            }
            
            td {
                padding: 0.5em;
            }
            
            .ui-widget-header {
                background: <?php echo $guestReferral->colors['header']['bg']; ?>;
                border: 1px solid <?php echo $guestReferral->colors['header']['bg']; ?>;
                color: <?php echo $guestReferral->colors['header']['txt']; ?>;
            }
            
            .ui-widget-content {
                border: 1px solid <?php echo $guestReferral->colors['header']['bg']; ?>;
            }
            
            .ui-datepicker a {
                background: <?php echo $guestReferral->colors['header']['bg']; ?> !important;
                border: 1px solid #e37626 !important;
                color: <?php echo $guestReferral->colors['header']['txt']; ?> !important;
            }
            
            .ui-datepicker a.ui-state-hover {
                background-color: #85563c !important;
            }
            
            .ui-datepicker a.ui-state-highlight {
                background-color: #ffa736 !important;
            }
            
            #page, .ui-widget-content.row {
                margin: 0;
            }
            
        </style>
        
        <script type="text/javascript">
        
        	$(document).ready(function(){
        		$(".ckbdate").datepicker({
        			yearRange:"-99:+00",
        			changeMonth:!0,
        			changeYear:!0,
        			autoSize:!0,
        			maxDate:0,
        			dateFormat:"M d, yy"
        		})
        	});
        	
        
        </script>
        
    </head>
    <body>
    	<form action="">
            <div id="page" class="row">
            	<div class="col-12">
                    <div class="row my-3">
                    	<div class="col" id="patentInfo">
                    		<?php echo $guestReferral->createPatientInformationMarkup(); ?>
                    	</div>
                    </div>
                    <div class="row my-3">
                    	<div class="col-md-6" id="addressSection">
                    		<?php echo $guestReferral->createAddressMarkup(); ?>
                    	</div>
                    </div>
                    <div class="row my-3">
                    	<div class="col-md-6" id="addressSection">
                    		<div class="ui-widget-header ui-state-default ui-corner-top">
                    			<h2>Additional Guests/Caregivers: (max: 4)</h2>
                    		</div>
                    		<div class="ui-corner-bottom ui-widget-content">
                    			<table>
                    				<tbody>
                    					<tr>
                    						<td><input type="text" placeholder="Guest First"></td>
                    						<td><input type="text" placeholder="Guest Last"></td>
                    					</tr>
                    					<tr>
                    						<td><input type="text" placeholder="Guest First"></td>
                    						<td><input type="text" placeholder="Guest Last"></td>
                    					</tr>
                    					<tr>
                    						<td><input type="text" placeholder="Guest First"></td>
                    						<td><input type="text" placeholder="Guest Last"></td>
                    					</tr>
                    					<tr>
                    						<td><input type="text" placeholder="Guest First"></td>
                    						<td><input type="text" placeholder="Guest Last"></td>
                    					</tr>
                    				</tbody>
                    			</table>
                    		</div>
                    	</div>
                    </div>
                    <div class="row my-3">
                    	<div class="col-12">
                    		<input type="submit" id="submit" value="SUBMIT FORM">
                    	</div>
                    </div>
                </div>
            </div>
        </form>
    </body>
</html>