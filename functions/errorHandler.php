<?php
$config = new Config_Lite(ciCFG_FILE);
//only interfere on live and demo sites
if ($config->getString('site', 'Mode', 'dev') != "dev") {
    register_shutdown_function("fatal_handler", $config);
}

function fatal_handler($config) {

    //get error
    $error = error_get_last();

    //split error object into vars
    if ($error !== NULL && $config->getString('site', 'Error_Report_Email', 'supportdefault@hhk.local')) {

        formHandler($error, $config);

        //check if ajax request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest") {
            returnJSON($error, $config);
        } else {
            buildPage($error);
        }

        die();
    }
}

function formHandler($error, $config) {
    $sec = new SecurityComponent;

    //if post data exists, send email
    if ($_POST && isset($_POST['name'], $_POST['email'], $_POST['message'])) {

        $name = $_POST['name'];
        $email = $_POST['email'];
        $message = "New bug report received from " . getSiteName() . "\r\n\r\n";
        $message .= "Name: " . $name . "\r\n\r\n";
        $message .= "Email: " . $email . "\r\n\r\n";
        $message .= "Message: " . $_POST['message'] . "\r\n\r\n";
        $message .= "File: " . $error["file"] . " line " . $error["line"] . "\r\n\r\n";
        $message .= "Error: " . $error["message"];


        // send email and redirect
        sendMail($message, $config);
        buildPage("", true);
        exit;
    }
}

function getSiteName(){
    $host = explode('.', $_SERVER['HTTP_HOST']);
    $requestURI = explode('/', $_SERVER['REQUEST_URI']);
    
    if(count($host) == 3){
        return $host[0]; //return subdomain if it exists
    }else if($requestURI[1] == 'demo'){
        return $requestURI[2]; //if demo, skip /demo/
    }else{
        return $requestURI[1];
    }
}

function sendMail($message, $config) {
    if ($message) {
        //get report email address
        $to = $config->getString('site', 'Error_Report_Email', 'supportdefault@hhk.local');
        $subject = "New bug report received from " . getSiteName();
        $headers = "From: BugReporter<noreply@nonprofitsoftwarecorp.org>" . "\r\n";

        mail($to, $subject, $message, $headers);
    }
}

function returnJSON($error, $config) {


    $message = "New bug report received from " . getSiteName() . "\r\n\r\n";
    $message .= "Request Type: AJAX\r\n\r\n";
    $message .= "File: " . $error["file"] . " line " . $error["line"] . "\r\n\r\n";
    $message .= "Error: " . $error["message"];

    sendMail($message, $config);

    echo json_encode(["status" => "error", "message" => "An error Occurred."]);
}

function buildPage($error, $success = false) {
    $sec = new SecurityComponent;
    ?>
    <!DOCTYPE html>
    <html lang="en">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title>HHK - Error</title>
            <meta http-equiv="x-ua-compatible" content="IE=edge">
    <?php echo JQ_UI_CSS; ?>
            <?php echo MULTISELECT_CSS; ?>
            <?php echo HOUSE_CSS; ?>
            <?php echo JQ_DT_CSS; ?>
            <?php echo NOTY_CSS; ?>
            <?php echo FAVICON; ?>

            <style>

                .errorwrapper {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-color: #fff;
                    z-index: 100000;
                    margin-top: 39px;
                    padding-top: 2em;
                }

                .container {
                    width: 1140px;
                    margin: 0 auto;
                }

                .col-6 {
                    width: 45%;
                    display: inline-block;
                }

                h1 {
                    text-align: center;
                    margin-bottom: 1em;
                }

                h2 {
                    margin: .2em;
                }

                h4,.ui-button {
                    margin: 1em;
                }

                form input,textarea {
                    display: block;
                    width: 95%;
                    padding: .5em;
                    font-size: 1em;
                    border-radius: 5px;
                    border: 1px solid #ccc;
                }

                .form-input {
                    margin: 1em;
                    text-align: center;
                }

                .logo-text {
                    width: 45%;
                    display: block;
                    margin: 1em auto;
                }
            </style>
        </head>
        <body>
            <div class="wrapper errorwrapper">
                <h1>Uh oh, something's not right!</h1>
                <div class="container">
                    <div class="col-6" style="text-align: center;">
                        <img src="<?php echo $sec->getRootURL(); ?>images/hhkLogo.png">
                        <div class="logo-text">
                            <p>Sometimes errors happen, help us stop them in their digital tracks by submitting a bug report.</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="ui-widget-header ui-state-default ui-corner-top">
                            <h2>File a bug report</h2>
                        </div>
                        <div class="ui-widget-content ui-corner-bottom hhk-tdbox">
    <?php if ($success) { ?>
                                <h4>Thanks for submitting!</h4>
                                <a href="<?php echo $sec->getRootURL(); ?>" class="ui-button ui-corner-all ui-widget">Go Home</a>
    <?php } else { ?>
                                <form action="#" method="POST">
                                    <div class="form-input">
                                        <input type="text" name="name" placeholder="Name">
                                    </div>
                                    <div class="form-input">
                                        <input type="email" name="email" placeholder="Email Address">
                                    </div>
                                    <div class="form-input">
                                        <textarea name="message" placeholder="What were you doing before you got here?"></textarea>
                                    </div>
                                    <div class="form-input">
                                        <input type="submit" class="ui-button ui-corner-all ui-widget" value="Submit" style="width: initial;">
                                    </div>
                                </form>
    <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </body>
    </html>

    <?php
}
?>