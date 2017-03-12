<?php
/**
 * index.php  (admin)
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2017 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */
require_once ("AdminIncludes.php");

require_once (SEC . 'UserClass.php');
require_once(SEC . 'ChallengeGenerator.php');
require_once(SEC . 'Login.php');

// get session instance
$uS = Session::getInstance();

// Logout command?
if (isset($_GET["log"])) {
    $log = filter_var($_GET["log"], FILTER_SANITIZE_STRING);
    if ($log == "lo") {

        $uS->destroy(true);
        header('location:index.php');
        exit();
    }
}


try {

    $login = new Login();
    $config = $login->initializeSession(ciCFG_FILE);

} catch (PDOException $pex) {
    exit ("<h3>Database Error.  </h3>");

} catch (Exception $ex) {
    echo ("<h3>Server Error</h3>" . $ex->getMessage());
}


// define db connection obj
$dbh = initPDO(TRUE);

// Load the page information
try {
    $page = new ScriptAuthClass($dbh);
} catch (Exception $ex) {
    $uS->destroy(true);
    exit("Error accessing web page data table: " . $ex->getMessage());
}


if (isset($_POST['txtUname'])) {
    $events = $login->checkPost($dbh, $_POST, $page->get_Default_Page());
    echo json_encode($events);
    exit();
}


$volSiteURL = $config->getString("site", 'Volunteer_URL', '');
$houseSiteUrl = $config->getString("site", 'House_URL', '');
$tutorialSiteURL = $config->getString('site', 'Tutorial_URL'. '');
$build = 'Build:' . $config->getString('code', 'Version', '*') . '.' . $config->getString('code', 'Build', '*');

// disclamer
$disclaimer = $config->get('site', 'Disclaimer', '');

$pageTitle = $uS->siteName;

$icons = array();

foreach ($uS->siteList as $r) {

    if ($r["Site_Code"] != "r") {
        $icons[$r["Site_Code"]] = "<span class='" . $r["Class"] . "' style='float: left; margin-left:.3em;margin-top:2px;'></span>";
    }
}

$siteName = HTMLContainer::generateMarkup('h3', 'Administration Site' . $icons[$page->get_Site_Code()]);


$volLinkMkup = '';
$houseLinkMkup = '';
$tutorialMkup = '';

if ($volSiteURL != '' && isset($icons['v'])) {
    $volLinkMkup = HTMLContainer::generateMarkup('div',
        HTMLContainer::generateMarkup('p',
                'I want to go to the ' . $icons['v'] . HTMLContainer::generateMarkup('a', 'Volunteer web site', array('href'=>$volSiteURL))), array('style'=>'margin-top:30px;'));
}

if ($houseSiteUrl != '' && isset($icons['h'])) {

    $houseLinkMkup = HTMLContainer::generateMarkup('div',
        HTMLContainer::generateMarkup('p',
                'I want to go to the ' . $icons['h'] . HTMLContainer::generateMarkup('a', 'Guest Tracking web site', array('href'=>$houseSiteUrl))), array('style'=>'margin-top:30px;'));
}

if ($tutorialSiteURL != '') {
    $tutorialMkup = HTMLContainer::generateMarkup('div',
            HTMLContainer::generateMarkup('h3', 'Tutorial Videos')
            . HTMLContainer::generateMarkup('div', HTMLContainer::generateMarkup('a', 'You Tube Videos', array('href'=>$tutorialSiteURL)), array('style'=>'margin-left:15px;')), array('style'=>'margin-top:35px;'));
}

$copyYear = date('Y');

$loginMkup = $login->loginForm();
$cspURL = $uS->siteList[$page->get_Site_Code()]['HTTP_Host'];

header('X-Frame-Options: SAMEORIGIN');
header("Content-Security-Policy: default-src $cspURL; style-src $cspURL 'unsafe-inline';"); // FF 23+ Chrome 25+ Safari 7+ Opera 19+
header("X-Content-Security-Policy: default-src $cspURL; style-src $cspURL 'unsafe-inline';"); // IE 10+

$isHttps = !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off';
if ($isHttps)
{
  header('Strict-Transport-Security: max-age=31536000'); // FF 4 Chrome 4.0.211 Opera 12
}

?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo $pageTitle; ?></title>
        <link href="<?php echo JQ_UI_CSS; ?>" rel="stylesheet" type="text/css" />
        <?php echo DEFAULT_CSS; ?>
        <script type="text/javascript" src="../js/md5-min.js"></script>
        <script type="text/javascript" src="<?php echo $uS->resourceURL; ?><?php echo JQ_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo $uS->resourceURL; ?><?php echo JQ_UI_JS; ?>"></script>
        <script type="text/javascript" src="<?php echo $uS->resourceURL; ?>js/login.js"></script>
    </head>
    <body <?php if ($uS->testVersion) {echo "class='testbody'";} ?> >
        <div id="page">
            <div class='pageSpacer'>
                <h2 style="color:white;"><?php echo $pageTitle; ?></h2></div>
            <div id="content">
                    <a href="http://hospitalityhousekeeper.org/" target="blank"><img width="250" alt='Hospitality HouseKeeper Logo' src="../images/hhkLogo.png"></a>
                    <div style="clear:left; margin-bottom: 20px;"></div>
                <div id="formlogin" style="float:left;" >
                    <div><?php echo $siteName; ?>
                        <p style="margin-left:6px; width: 50%;"><?php echo $disclaimer ?></p>
                    </div>
                    <?php echo $loginMkup; ?><?php echo $volLinkMkup; ?><?php echo $houseLinkMkup; ?>
                    <?php echo $tutorialMkup; ?>
                </div>
            </div>
                <div style="clear:left;"></div>
                <div style="margin-top: 70px;width:500px;">
                    <hr>
                    <div><a href ="http://nonprofitsoftwarecorp.org" ><div class="nplogo"></div></a></div>
                    <div style="float:right;font-size: smaller; margin-top:5px;margin-right:.3em;">&copy; <?php echo $copyYear; ?> Non Profit Software</div>
                </div>
        </div>
    </body>
</html>

