<?php
/**
 * ChallengeGenerator.php
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2017 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */

class ChallengeGenerator{

  function __ChallengeGenerator($clearSession=true){

    if($clearSession){
        $ssn = Session::getInstance();
        unset($ssn->challenge);
        unset($ssn->Challtries);
    }
  }

  function setChallengeVar(){
    // register session variable
    $ssn = Session::getInstance();
    $ssn->challenge = $this->getRandomString();
    return $ssn->challenge;
  }

  function incrementTries() {
    $ssn = Session::getInstance();
    if (isset($ssn->Challtries) === FALSE) {
        $ssn->Challtries = 0;
    }
    $ssn->Challtries++;
    return $ssn->Challtries;
  }

  function testTries($max = 3) {
      $ssn = Session::getInstance();
      if (isset($ssn->Challtries) && $ssn->Challtries > $max) {
          return FALSE;
      }
      return TRUE;
  }

  function getChallengeVar(){
    $ssn = Session::getInstance();
    return $ssn->challenge;
  }

  function deleteChallengeVar(){
    $ssn = Session::getInstance();
    if($ssn->challenge){
        unset($ssn->challenge);
    }
  }
  // private method "getRandomString()"
  static function getRandomString($length=40){
    if(!is_int($length)||$length<1){
      $length = 40;
    }
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $randstring = '';
    $maxvalue = strlen($chars) - 1;
    for($i=0; $i<$length; $i++){
      $randstring .= substr($chars, rand(0,$maxvalue), 1);
    }
    return $randstring;
  }
}
