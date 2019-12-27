<?php
/**
 * PaymentResponse.php
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2019 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */


abstract class PaymentResponse {

    protected $amount;
    protected $invoiceNumber = '';
    protected $partialPaymentFlag = FALSE;
    protected $paymentDate;
    protected $payNotes = '';
    protected $refund;
    protected $result;  // vendor specific

    public $idPayor = 0;
    public $idVisit;
    public $idReservation;
    public $idRegistration;
    public $idTrans = 0;
    public $idGuestToken = 0;
    public $idPayment;

    public abstract function getStatus();

    public abstract function receiptMarkup(\PDO $dbh, &$tbl);

    // One of the PaymentMethods
    public abstract function getPaymentMethod();

    // One of the PaymentStatusCodes
    public abstract function getPaymentStatusCode();

    // Record a payment
    public function recordPayment(\PDO $dbh, $username, $attempts = 1) {

        $payRs = new PaymentRS();
        $payRs->Amount->setNewVal($this->getAmount());
        $payRs->Payment_Date->setNewVal($this->getPaymentDate());
        $payRs->idPayor->setNewVal($this->getIdPayor());
        $payRs->idTrans->setNewVal($this->getIdTrans());
        $payRs->idToken->setNewVal($this->getIdToken());
        $payRs->idPayment_Method->setNewVal($this->getPaymentMethod());
        $payRs->Result->setNewVal($this->getResult());
        $payRs->Attempt->setNewVal($attempts);
        $payRs->Status_Code->setNewVal($this->getPaymentStatusCode());
        $payRs->Created_By->setNewVal($username);
        $payRs->Notes->setNewVal($this->getPaymentNotes());
        $payRs->Is_Refund->setNewVal($this->isRefund());

        $this->idPayment = EditRS::insert($dbh, $payRs);
        $payRs->idPayment->setNewVal($this->idPayment);
        EditRS::updateStoredVals($payRs);
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getResult() {
        return $this->result;
    }

    public function getPaymentDate() {
        return $this->paymentDate;
    }

    public function setPaymentDate($d) {
        $this->paymentDate = $d;
    }

    public function getPaymentNotes() {
        return $this->payNotes;
    }

    public function getIdPayor() {
        return $this->idPayor;
    }

    public function getInvoiceNumber() {
        return $this->invoiceNumber;
    }

    public function isRefund() {
        if ($this->refund) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getIdPayment() {
        return $this->idPayment;
    }

    public function getIdToken() {
        return $this->idGuestToken;
    }

    public function getIdTrans() {
        return $this->idTrans;
    }

    public function setIdTrans($idTrans) {
        $this->idTrans = $idTrans;
        return $this;
    }
}

abstract class CreditResponse extends PaymentResponse {

    public $response;
    public $idPaymentAuth;

    public function getResult() {
        return MpStatusValues::Approved;
    }

    public function recordPaymentAuth(\PDO $dbh, $paymentGatewayName, $username) {

        if ($this->idPayment > 0) {

            //Payment Detail
            $pDetailRS = new Payment_AuthRS();
            $pDetailRS->idPayment->setNewVal($this->idPayment);
            $pDetailRS->Approved_Amount->setNewVal($this->response->getAuthorizedAmount());
            $pDetailRS->Approval_Code->setNewVal($this->response->getAuthCode());
            $pDetailRS->Status_Message->setNewVal($this->response->getResponseMessage());
            $pDetailRS->Reference_Num->setNewVal($this->response->getRefNo());
            $pDetailRS->Acct_Number->setNewVal($this->response->getMaskedAccount());
            $pDetailRS->Card_Type->setNewVal($this->response->getCardType());
            $pDetailRS->Cardholder_Name->setNewVal($this->response->getCardHolderName());
            $pDetailRS->AVS->setNewVal($this->response->getAVSResult());
            $pDetailRS->Invoice_Number->setNewVal($this->getInvoiceNumber());
            $pDetailRS->idTrans->setNewVal($this->getIdTrans());
            $pDetailRS->AcqRefData->setNewVal($this->response->getAcqRefData());
            $pDetailRS->ProcessData->setNewVal($this->response->getProcessData());
            $pDetailRS->CVV->setNewVal($this->response->getCvvResult());
            $pDetailRS->Processor->setNewVal($paymentGatewayName);
            $pDetailRS->Merchant->setNewVal($this->response->getMerchant());
            $pDetailRS->Response_Message->setNewVal($this->response->getAuthorizationText());
            $pDetailRS->Response_Code->setNewVal($this->response->getTransactionStatus());
            $pDetailRS->Customer_Id->setNewVal($this->response->getOperatorId());
            $pDetailRS->Signature_Required->setNewVal($this->response->SignatureRequired());
            $pDetailRS->PartialPayment->setNewVal($this->response->getPartialPaymentAmount() > 0 ? 1 : 0);

            $pDetailRS->Updated_By->setNewVal($username);
            $pDetailRS->Last_Updated->setNewVal(date("Y-m-d H:i:s"));
            $pDetailRS->Status_Code->setNewVal($this->getPaymentStatusCode());

            // EMV
            $pDetailRS->EMVApplicationIdentifier->setNewVal($this->response->getEMVApplicationIdentifier());
            $pDetailRS->EMVApplicationResponseCode->setNewVal($this->response->getEMVApplicationResponseCode());
            $pDetailRS->EMVIssuerApplicationData->setNewVal($this->response->getEMVIssuerApplicationData());
            $pDetailRS->EMVTerminalVerificationResults->setNewVal($this->response->getEMVTerminalVerificationResults());
            $pDetailRS->EMVTransactionStatusInformation->setNewVal($this->response->getEMVTransactionStatusInformation());


            $this->idPaymentAuth = EditRS::insert($dbh, $pDetailRS);

        }

    }

    public function isPartialPayment() {
        return $this->partialPaymentFlag;
    }

    public function setPartialPayment($v) {
        if ($v) {
            $this->partialPaymentFlag = TRUE;
        } else {
            $this->partialPaymentFlag = FALSE;
        }
    }

    public function setIdToken($idToken) {
        $this->idGuestToken = intval($idToken, 10);
    }

    public function getIdPayment() {
        return $this->idPayment;
    }

    public function getIdTrans() {
        return $this->idTrans;
    }

    public function setIdTrans($idTrans) {
        $this->idTrans = $idTrans;
        return $this;
    }

}


abstract class CheckResponse extends PaymentResponse {

    public $idInfoCheck;

    public function recordInfoCheck(\PDO $dbh) {

        if ($this->idPayment > 0) {

            // Check table
            $ckRs = new PaymentInfoCheckRS();
            $ckRs->Check_Date->setNewVal($this->getPaymentDate());
            $ckRs->Check_Number->setNewVal($this->getCheckNumber);
            $ckRs->idPayment->setNewVal($this->idPayment);

            $this->idInfoCheck = EditRS::insert($dbh, $ckRs);
        }

    }

    public function getCheckNumber() {
        return $this->checkNumber;
    }

}