<?php
/**
 * HostedPayments.php
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2017 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */



/**
 * CardInfo - Create markup for  the Hosted CC portal.
 *
 * @author Eric
 */
class CardInfo {

    public static function sendToPortal(\PDO $dbh, $gw, $idPayor, $idGroup, InitCiRequest $initCi) {

        $dataArray = array();
        $trace = FALSE;

        if (strtolower($gw) == 'test') {
            $initCi->setOperatorID('test');
            $trace = TRUE;
        }

        $ciResponse = $initCi->submit(Gateway::getGateway($dbh, $gw), $trace);

        // Save raw transaction in the db.
        try {
            PaymentGateway::logGwTx($dbh, $ciResponse->getResponseCode(), json_encode($initCi->getFieldsArray()), json_encode($ciResponse->getResultArray()), 'CardInfoInit');
        } catch(Exception $ex) {
            // Do Nothing
        }

        if ($ciResponse->getResponseCode() == 0) {

            // Save the CardID in the database indexed by the guest id.
            $ciq = "replace into `card_id` (`idName`, `idGroup`, `Transaction`, `CardID`, `Init_Date`, `Frequency`, `ResponseCode`)"
                . " values ($idPayor, $idGroup, 'cof', '" . $ciResponse->getCardId() . "', now(), 'OneTime', '" . $ciResponse->getResponseCode() . "')";

            $dbh->exec($ciq);

            $dataArray = array('xfer' => $ciResponse->getCardInfoUrl(), 'cardId' => $ciResponse->getCardId());

        } else {

            // The initialization failed.
            throw new Hk_Exception_Payment("Card-On-File Gateway Error: " . $ciResponse->getResponseText());

        }

        return $dataArray;
    }


    public static function portalReply(\PDO $dbh, $gw, $cardId, $post) {

        $cidInfo = PaymentSvcs::getInfoFromCardId($dbh, $cardId);

        $verify = new VerifyCIRequest();
        $verify->setCardId($cardId);

        $trace = FALSE;

        if (strtolower($gw) == 'test') {
            $trace = TRUE;
        }

        // Verify request
        $verifyResponse = $verify->submit(Gateway::getGateway($dbh, $gw), $trace);
        $vr = new CardInfoResponse($verifyResponse, $cidInfo['idName'], $cidInfo['idGroup']);

        // Save raw transaction in the db.
        try {
            PaymentGateway::logGwTx($dbh, $vr->response->getStatus(), json_encode($verify->getFieldsArray()), json_encode($vr->response->getResultArray()), 'CardInfoVerify');
        } catch(Exception $ex) {
            // Do Nothing
        }


        if ($vr->response->getResponseCode() == 0 && $vr->response->getStatus() == MpStatusValues::Approved) {

            if ($vr->response->getToken() != '') {

                try {
                    $vr->idToken = CreditToken::storeToken($dbh, $vr->idRegistration, $vr->idPayor, $vr->response);
                } catch(Exception $ex) {
                    $vr->idToken = 0;
                }

            } else {
                $vr->idToken = 0;
            }
        }

        return $vr;

    }
}

class CardInfoResponse extends PaymentResponse {


    function __construct(VerifyCiResponse $verifyCiResponse, $idPayor, $idGroup) {
        $this->response = $verifyCiResponse;
        $this->idPayor = $idPayor;
        $this->idRegistration = $idGroup;
        $this->expDate = $verifyCiResponse->getExpDate();
        $this->cardNum = str_ireplace('x', '', $verifyCiResponse->getMaskedAccount());
    }

    public function getStatus() {

        switch ($this->response->getStatus()) {

            case MpStatusValues::Approved:
                $pr = CreditPayments::STATUS_APPROVED;
                break;

            case MpStatusValues::Declined:
                $pr = CreditPayments::STATUS_DECLINED;
                break;

            default:
                $pr = CreditPayments::STATUS_DECLINED;
        }

        return $pr;
    }

    public function receiptMarkup(\PDO $dbh, &$tbl) {
        return array('error'=>'Receipts not available.');
    }

}


class HostedCheckout {

    public static function sendToPortal(\PDO $dbh, $gw, $idPayor, $idGroup, $invoiceNumber, InitCkOutRequest $initCoRequest) {

        $dataArray = array();
        $trace = FALSE;

        if (strtolower($gw) == 'test') {
            $initCoRequest->setAVSAddress('4')->setAVSZip('30329');
            $initCoRequest->setOperatorID('test');
            $trace = TRUE;
        }


        $ciResponse = $initCoRequest->submit(Gateway::getGateway($dbh, $gw), $trace);

        // Save raw transaction in the db.
        try {
            PaymentGateway::logGwTx($dbh, $ciResponse->getResponseCode(), json_encode($initCoRequest->getFieldsArray()), json_encode($ciResponse->getResultArray()), 'HostedCoInit');
        } catch(Exception $ex) {
            // Do Nothing
        }


        if ($ciResponse->getResponseCode() == 0) {

            // Save payment ID
            $ciq = "replace into card_id (idName, `idGroup`, `Transaction`, InvoiceNumber, CardID, Init_Date, Frequency, ResponseCode)"
                . " values ($idPayor, $idGroup, 'hco', '$invoiceNumber', '" . $ciResponse->getPaymentId() . "', now(), 'OneTime', '" . $ciResponse->getResponseCode() . "')";

            $dbh->exec($ciq);

            $dataArray = array('xfer' => $ciResponse->getCheckoutUrl(), 'paymentId' => $ciResponse->getPaymentId());

        } else {

            // The initialization failed.
            throw new Hk_Exception_Payment("Credit Payment Gateway Error: " . $ciResponse->getResponseText());

        }


        return $dataArray;
    }

    public static function portalReply(\PDO $dbh, $gw, $paymentId, $payNotes) {

        $uS = Session::getInstance();

        // Check paymentId
        $cidInfo = PaymentSvcs::getInfoFromCardId($dbh, $paymentId);

        $trace = FALSE;

        if (strtolower($gw) == 'test') {
            $trace = TRUE;
        }

        // setup the verify request
        $verify = new VerifyCkOutRequest();
        $verify->setPaymentId($paymentId);

        // Verify request
        $verifyResponse = $verify->submit(Gateway::getGateway($dbh, $gw), $trace);
        $vr = new CheckOutResponse($verifyResponse, $cidInfo['idName'], $cidInfo['idGroup'], $cidInfo['InvoiceNumber'], $payNotes);


        // Save raw transaction in the db.
        try {
            PaymentGateway::logGwTx($dbh, $vr->response->getStatus(), json_encode($verify->getFieldsArray()), json_encode($vr->response->getResultArray()), 'HostedCoVerify');
        } catch(Exception $ex) {
            // Do Nothing
        }

        // Record transaction
        try {

            if ($verifyResponse->getTranType() == MpTranType::ReturnAmt) {
                $trType = TransType::Retrn;
            } else if ($verifyResponse->getTranType() == MpTranType::Sale) {
                $trType = TransType::Sale;
            }

            $transRs = Transaction::recordTransaction($dbh, $vr, $gw, $trType, TransMethod::HostedPayment);
            $vr->setIdTrans($transRs->idTrans->getStoredVal());

        } catch(Exception $ex) {

        }

        // record payment
        return SaleReply::processReply($dbh, $vr, $uS->username);

    }

}


class CheckOutResponse extends PaymentResponse {

    public $response;
    public $idToken = '';

    function __construct($verifyCkOutResponse, $idPayor, $idGroup, $invoiceNumber, $payNotes) {
        $this->response = $verifyCkOutResponse;
        $this->paymentType = PayType::Charge;
        $this->idPayor = $idPayor;
        $this->idRegistration = $idGroup;
        $this->invoiceNumber = $invoiceNumber;
        $this->expDate = $verifyCkOutResponse->getExpDate();
        $this->cardNum = str_ireplace('x', '', $verifyCkOutResponse->getMaskedAccount());
        $this->cardType = $verifyCkOutResponse->getCardType();
        $this->cardName = $verifyCkOutResponse->getCardHolderName();
        $this->amount = $verifyCkOutResponse->getAuthorizeAmount();
        $this->payNotes = $payNotes;
    }

    public function getStatus() {

        switch ($this->response->getStatus()) {

            case MpStatusValues::Approved:
                $pr = CreditPayments::STATUS_APPROVED;
                break;

            case MpStatusValues::Declined:
                $pr = CreditPayments::STATUS_DECLINED;
                break;

            default:
                $pr = CreditPayments::STATUS_DECLINED;
        }

        return $pr;
    }

    public function receiptMarkup(\PDO $dbh, &$tbl) {

        $tbl->addBodyTr(HTMLTable::makeTd("Credit Card:", array('class'=>'tdlabel')) . HTMLTable::makeTd(number_format($this->getAmount(), 2)));
        $tbl->addBodyTr(HTMLTable::makeTd($this->cardType . ':', array('class'=>'tdlabel')) . HTMLTable::makeTd("xxxxx...". $this->cardNum));

        if ($this->cardName != '') {
            $tbl->addBodyTr(HTMLTable::makeTd("Card Holder: ", array('class'=>'tdlabel')) . HTMLTable::makeTd($this->cardName));
        }

        $tbl->addBodyTr(HTMLTable::makeTd("Sign: ", array('class'=>'tdlabel')) . HTMLTable::makeTd('', array('style'=>'height:35px; width:250px; border: solid 1px gray;')));

    }

}

