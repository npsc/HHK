<?php

use HHK\sec\{Session, WebInit};
use HHK\SysConst\WebPageCode;
use HHK\Member\MemberSearch;
use HHK\Member\Relation\AbstractRelation;
use HHK\sec\UserClass;


/**
 * ws_admin.php
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2020 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */

require ("homeIncludes.php");



// Set page type for AdminPageCommon
$wInit = new webInit(WebPageCode::Service);

$dbh = $wInit->dbh;


// get session instance
$uS = Session::getInstance();


if (isset($_REQUEST["cmd"])) {
    $c = filter_var($_REQUEST["cmd"], FILTER_SANITIZE_STRING);
}

$events = array();
try {


switch ($c) {

    case "visitlog" :

        $id = filter_var(urldecode($_REQUEST["uid"]), FILTER_VALIDATE_INT);
        $idPsg = filter_var(urldecode($_REQUEST["psg"]), FILTER_VALIDATE_INT);

        $events = visitLog($dbh, $id, $idPsg, $_GET);
        break;

    case "delRel":

        $id = 0;
        $rId = 0;

        if (isset($_POST['id'])) {
            $id = intval(filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT), 10);
        }
        if (isset($_POST['rId'])) {
            $rId = intval(filter_var($_POST['rId'], FILTER_SANITIZE_NUMBER_INT), 10);
        }

        if (isset($_POST['rc'])) {
            $rc = filter_var($_POST['rc'], FILTER_SANITIZE_STRING);
        }

        $events = deleteRelationLink($dbh, $id, $rId, $rc);
        break;

    case "newRel":

        $id = 0;
        $rId = 0;

        if (isset($_POST['id'])) {
            $id = intval(filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT), 10);
        }
        if (isset($_POST['rId'])) {
            $rId = intval(filter_var($_POST['rId'], FILTER_SANITIZE_NUMBER_INT), 10);
        }

        if (isset($_POST['rc'])) {
            $rc = filter_var($_POST['rc'], FILTER_SANITIZE_STRING);
        }

        $events = newRelationLink($dbh, $id, $rId, $rc);
        break;

    case "addcareof":

        $id = 0;
        $rId = 0;

        if (isset($_POST['id'])) {
            $id = intval(filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT), 10);
        }
        if (isset($_POST['rId'])) {
            $rId = intval(filter_var($_POST['rId'], FILTER_SANITIZE_NUMBER_INT), 10);
        }

        if (isset($_POST['rc'])) {
            $rc = filter_var($_POST['rc'], FILTER_SANITIZE_STRING);
        }

        $events = changeCareOfFlag($dbh, $id, $rId, $rc, TRUE);
        break;

    case "delcareof":

        $id = 0;
        $rId = 0;

        if (isset($_POST['id'])) {
            $id = intval(filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT), 10);
        }
        if (isset($_POST['rId'])) {
            $rId = intval(filter_var($_POST['rId'], FILTER_SANITIZE_NUMBER_INT), 10);
        }

        if (isset($_POST['rc'])) {
            $rc = filter_var($_POST['rc'], FILTER_SANITIZE_STRING);
        }

        $events = changeCareOfFlag($dbh, $id, $rId, $rc, FALSE);
        break;

    case 'srchName':

        if (isset($_POST['md'])) {
            $md = filter_var($_POST['md'], FILTER_SANITIZE_STRING);
            $nameLast = (isset($_POST['nl']) ? filter_var($_POST['nl'], FILTER_SANITIZE_STRING) : '');
            $nameFirst = (isset($_POST['nf']) ? filter_var($_POST['nf'], FILTER_SANITIZE_STRING) : '');
            $email = (isset($_POST['em']) ? filter_var($_POST['em'], FILTER_SANITIZE_STRING) : '');
            $indx = (isset($_POST['indx']) ? filter_var($_POST['indx'], FILTER_SANITIZE_STRING) : '');

            // Check for duplicate member records
            $dups = MemberSearch::searchName($dbh, $md, $nameLast, $nameFirst, $email);

            if (count($dups) > 0) {
                $events = array(
                    'success'=>'Returned '. count($dups) . ' duplicates',
                    'dups' => MemberSearch::createDuplicatesDiv($dups),
                    'indx' => $indx);
            }

        } else {
            $events = array('error' => 'Search Names: must supply a member designation.  ');
        }

        break;

    case 'schzip':

        if (isset($_GET['zip'])) {
            $zip = filter_var($_GET['zip'], FILTER_SANITIZE_NUMBER_INT);
            $events = searchZip($dbh, $zip);
        }
        break;
        
    case 'getcounties':
        
        if(isset($_GET['state'])) {
            $state = filter_var($_GET['state'], FILTER_SANITIZE_STRING);
            $events = getCounties($dbh, $state);
        }
        break;

    case "chgpw":

        $oldPw = ''; $newPw = '';

        if (isset($_POST["old"])) {
            $oldPw = filter_var($_POST["old"], FILTER_SANITIZE_STRING);
        }
        if (isset($_POST["newer"])) {
            $newPw = filter_var($_POST["newer"], FILTER_SANITIZE_STRING);
        }

        $events = changePW($dbh, $oldPw, $newPw, $uS->username, $uS->uid);

        break;

    case "chgquestions":
        $questions = array();
        
        if (isset($_POST["q1"]) && isset($_POST["a1"]) && isset($_POST["aid1"])) {
            $questions[] = [
                'idQuestion'=>filter_var($_POST["q1"], FILTER_SANITIZE_STRING),
                'idAnswer'=>filter_var($_POST["aid1"], FILTER_SANITIZE_STRING),
                'Answer'=>filter_var($_POST["a1"], FILTER_SANITIZE_STRING)
            ];
        }
        
        if (isset($_POST["q2"]) && isset($_POST["a2"]) && isset($_POST["aid2"])) {
            $questions[] = [
                'idQuestion'=>filter_var($_POST["q2"], FILTER_SANITIZE_STRING),
                'idAnswer'=>filter_var($_POST["aid2"], FILTER_SANITIZE_STRING),
                'Answer'=>filter_var($_POST["a2"], FILTER_SANITIZE_STRING)
            ];
        }
        
        if (isset($_POST["q3"]) && isset($_POST["a3"]) && isset($_POST["aid3"])) {
            $questions[] = [
                'idQuestion'=>filter_var($_POST["q3"], FILTER_SANITIZE_STRING),
                'idAnswer'=>filter_var($_POST["aid3"], FILTER_SANITIZE_STRING),
                'Answer'=>filter_var($_POST["a3"], FILTER_SANITIZE_STRING)
            ];
        }
        
        $events = changeQuestions($dbh, $questions);
        
        break;
        
    default:
        $events = array("error" => "Bad Command");
}

} catch (PDOException $ex) {

    $events = array("error" => "Database Error" . $ex->getMessage());

} catch (Exception $ex) {

    $events = array("error" => "HouseKeeper Error" . $ex->getMessage());
}



if (is_array($events)) {
    echo (json_encode($events));
} else {
    echo $events;
}

exit();


function searchZip(PDO $dbh, $zip) {

    $query = "select * from postal_codes where Zip_Code like :zip LIMIT 10";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array(':zip'=>$zip . "%"));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = array();
    foreach ($rows as $r) {
        $ent = array();

        $ent['value'] = $r['Zip_Code'];
        $ent['label'] = $r['City'] . ', ' . $r['State'] . ', ' . $r['Zip_Code'];
        $ent['City'] = $r['City'];
        $ent['County'] = $r['County'];
        $ent['State'] = $r['State'];

        $events[] = $ent;
    }

    return $events;
}

function getCounties(PDO $dbh, $state) {
    $query = "select `County`, `State` from `postal_codes` where `State` = :state and  `County` != '' group by `County`";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array(':state'=>strtoupper($state)));
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $events = ['success'=>$rows];
    
    return $events;
}

function changeCareOfFlag(PDO $dbh, $id, $rId, $relCode, $flag) {

    $rel = AbstractRelation::instantiateRelation($dbh, $relCode, $id);

    if (is_null($rel) === FALSE) {
        $uS = Session::getInstance();
        $msh = $rel->setCareOf($dbh, $rId, $flag, $uS->username);

        $rel = AbstractRelation::instantiateRelation($dbh, $relCode, $id);

        return array('success'=>$msh, 'rc'=>$relCode, 'markup'=>$rel->createMarkup());
    }
    return array('error'=>'Relationship is Undefined.');

}

function deleteRelationLink(PDO $dbh, $id, $rId, $relCode) {

    $rel = AbstractRelation::instantiateRelation($dbh, $relCode, $id);

    if (is_null($rel) === FALSE) {

        $msh = $rel->removeRelationship($dbh, $rId);

        $rel = AbstractRelation::instantiateRelation($dbh, $relCode, $id);

        return array('success'=>$msh, 'rc'=>$relCode, 'markup'=>$rel->createMarkup());
    }
    return array('error'=>'Relationship is Undefined.');

}

function newRelationLink(PDO $dbh, $id, $rId, $relCode) {

    $uS = Session::getInstance();

    $rel = AbstractRelation::instantiateRelation($dbh, $relCode, $id);

    if (is_null($rel) === FALSE) {
        $msh = $rel->addRelationship($dbh, $rId, $uS->username);

        $rel = AbstractRelation::instantiateRelation($dbh, $relCode, $id);
        return array('success'=>$msh, 'rc'=>$relCode, 'markup'=>$rel->createMarkup());
    }

    return array('error'=>'Relationship is Undefined.');

}


function changePW(\PDO $dbh, $oldPw, $newPw, $uname, $id) {

    $event = array();

    $u = new UserClass();

    if ($u->updateDbPassword($dbh, $id, $oldPw, $newPw, $uname) === TRUE) {
        $event = array('success'=>'User Password updated.');
    } else {
        $event = array('warning'=>$u->logMessage);
    }

    return $event;
}

function changeQuestions(\PDO $dbh, array $questions) {
    
    $event = array();
    
    $u = new UserClass();
    
    if ($u->updateSecurityQuestions($dbh, $questions) === TRUE) {
        $event = array('success'=>'User Security Questions Updated.');
    } else {
        $event = array('warning'=>$u->logMessage);
    }
    
    return $event;
}
