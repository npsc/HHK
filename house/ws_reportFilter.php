<?php

use HHK\sec\WebInit;
use HHK\sec\Session;
use HHK\SysConst\WebPageCode;
use HHK\House\Report\ReportFieldSet;

/**
 * ws_reportFilter.php
 *
 * @author    Will Ireland <wireland@nonprofitsoftwarecorp.org>
 * @copyright 2010-2020 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */
/**
 *  includes and requires
 */
require ("homeIncludes.php");


$wInit = new WebInit(WebPageCode::Service);

/* @var $dbh PDO */
$dbh = $wInit->dbh;
addslashesextended($_REQUEST);
$c = "";

// Get our command
if (isset($_REQUEST["cmd"])) {
    $c = filter_var($_REQUEST["cmd"], FILTER_SANITIZE_STRING);
}

$uS = Session::getInstance();


$events = array();
$report = '';
$idFieldSet = 0;
$title = '';
$global = FALSE;
$fields = '';

try {

    switch ($c) {
        case 'listFieldSets':
            
            if (isset($_REQUEST["report"])) {
                $report = filter_var(urldecode($_REQUEST["report"]), FILTER_SANITIZE_STRING);
            }
            
            $events = ["status"=>"success", "report"=>$report, "fieldSets"=>ReportFieldSet::listFieldSets($dbh, $report)];
            
            break;
        
        case 'getFieldSet':

            if (isset($_REQUEST["idFieldSet"])) {
            	$idFieldSet = filter_var(urldecode($_REQUEST["idFieldSet"]), FILTER_SANITIZE_NUMBER_INT);
            }
            
            $response = ReportFieldSet::getFieldSet($dbh, intval($idFieldSet));
            
            if($response){
                $events = $response;
            }else{
                $events = ["error"=>"Field set not found"];
            }
            
            break;
            
        case 'createFieldSet':
        	
            if (isset($_REQUEST["report"])) {
                $report = filter_var(urldecode($_REQUEST["report"]), FILTER_SANITIZE_STRING);
            }
            if (isset($_REQUEST["title"])) {
                $title = filter_var(urldecode($_REQUEST["title"]), FILTER_SANITIZE_STRING);
            }
            if (isset($_REQUEST["global"])) {
                $global = intval(filter_var($_REQUEST["global"], FILTER_VALIDATE_BOOLEAN));
            }
            if (isset($_REQUEST["fields"])) {
                $fields = filter_var_array($_REQUEST["fields"], FILTER_SANITIZE_STRING);
            }
            try{
                $events = ReportFieldSet::createFieldSet($dbh, $report, $title, $fields, $global);
            }catch(\Exception $e){
                $events = ['error'=>$e->getMessage()];
            }
            
            break;
            
        case 'updateFieldSet':
        	
            if (isset($_REQUEST['idFieldSet'])){
                $idFieldSet = filter_var($_REQUEST['idFieldSet'], FILTER_SANITIZE_NUMBER_INT);
            }
            if (isset($_REQUEST["title"])) {
                $title = filter_var(urldecode($_REQUEST["title"]), FILTER_SANITIZE_STRING);
            }
            if (isset($_REQUEST["fields"])) {
                $fields = filter_var_array($_REQUEST["fields"], FILTER_SANITIZE_STRING);
            }

            $events = ReportFieldSet::updateFieldSet($dbh, intval($idFieldSet), $title, $fields);
            
            break;
            
        case 'deleteFieldSet':

            if (isset($_REQUEST["idFieldSet"])){
                $idFieldSet = filter_var($_REQUEST["idFieldSet"], FILTER_SANITIZE_NUMBER_INT);
            }
            
            $events = ReportFieldSet::deleteFieldSet($dbh, intval($idFieldSet));
            
            break;
        default:
            $events = array("error" => "Bad Command: \"" . $c . "\"");
    }
} catch (\PDOException $ex) {
    $events = array("error" => "Database Error: " . $ex->getMessage());
    
} catch (\Exception $ex) {
    $events = array("error" => "Programming Error: " . $ex->getMessage());
}



if (is_array($events)) {
    echo (json_encode($events));
} else {
    echo $events;
}

exit();

?>