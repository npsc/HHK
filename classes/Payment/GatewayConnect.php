<?php
/**
 * Description of GatewayConnect
 *
 * @author Eric
 */
abstract class GatewayResponse {

    /**
     *
     * @var array
     */
    protected $response;
    protected $errors;

    /**
     *
     * @var array
     */
    protected $result;

    protected $tranType;

    /**
     * The child is expected to define $result.
     *
     * @param array $response
     * @throws Hk_Exception_Payment
     */
    function __construct($response) {
        if (is_array($response) || is_object($response)) {
            $this->response = $response;
        } else {
            throw new Hk_Exception_Payment('Empty response object. ');
        }

        $this->parseResponse();
    }

    // Returns Result
    protected abstract function parseResponse();

    public abstract function getResponseCode();


    public function getResultArray() {
        if (isset($this->result)) {
            return $this->result;
        }
        return array();
    }

    public function getTranType() {
        return $this->tranType;
    }


    public function getAuthorizeAmount() {
        return 0;
    }

}

class PollingResponse extends GatewayResponse {

    const WAIT = 'NEW';
    const EXPIRED = 'EXPIRED';
    const COMPLETE = 'complete';


    protected function parseResponse() {

        if (isset($this->response->GetSSOTokenStatusResponse)) {
            $this->result = $this->response->GetSSOTokenStatusResponse;
        } else {
            throw new Hk_Exception_Payment("GetSSOTokenStatusResponse is missing from the payment gateway response.  ");
        }
    }

    public function getResponseCode() {

        if (isset($this->result['GetSSOTokenStatusResult'])) {
            return $this->result['GetSSOTokenStatusResult'];
        } else {
            throw new Hk_Exception_Payment("GetSSOTokenStatusResult is missing from the payment gateway response.  ");
        }
    }

    public function isWaiting() {
        if ($this->getResponseCode() == PollingResponse::WAIT) {
            return TRUE;
        }
        return FALSE;
    }

    public function isExpired() {
        if ($this->getResponseCode() == PollingResponse::EXPIRED) {
            return TRUE;
        }
        return FALSE;
    }

    public function isComplete() {
        if ($this->getResponseCode() == PollingResponse::COMPLETE) {
            return TRUE;
        }
        return FALSE;
    }

}

class VerifyCurlResponse extends GatewayResponse {

    function __construct($response, $invoiceNumber, $amount) {

        parent::__construct($response);

        if(is_array($response)){
            $this->result = $response;
            $this->result['InvoiceNumber'] = $invoiceNumber;
            $this->result['Amount'] = $amount;
        }else{
            throw new Hk_Exception_Payment("Curl transaction response is invalid.  ");
        }
    }

    public function parseResponse(){
            return '';
    }
        //IsEMVVerifiedByPIN=false
        //isEMVTransaction=false
        //EMVCardEntryMode=Keyed
        //isSignatureRequired=false
        //cardBrand=VISA
        //cardExpirationMonth=12
        //cardExpirationYear=2021
        //cardBINNumber=411111
        //cardHolderName=
        //paymentCardType=Credit
        //lastFourDigits=1111
        //authorizationNumber=A2CDB9
        //responseCode=000
        //responseMessage=APPROVAL
        //transactionStatus=C
        //primaryTransactionID=c5a1a5a099f748c8bf16b890c8b371ec
        //authorizationText=I AGREE TO PAY THE ABOVE AMOUNT ACCORDING TO MY CARD HOLDER AGREEMENT.
        //transactionID=c5a1a5a099f748c8bf16b890c8b371ec
        //paymentPlanID=ccc366b1641444fe9e59620340d5e06c
        //transactionDate=2018-09-26T19:05:40.1666074Z

    public function getResponseCode() {
        if (isset($this->result['responseCode'])) {
            return $this->result['responseCode'];
        }
        return '';
    }

    public function getStatus() {
        if (isset($this->result['responseCode'])) {
            return $this->result['responseCode'];
        }
        return '';
    }

    public function getStatusMessage() {
        if (isset($this->result['responseMessage'])) {
            return $this->result['responseMessage'];
        }
        return '';
    }

    public function getMessage() {
        if (isset($this->result['responseMessage'])) {
            return $this->result['responseMessage'];
        }
        return '';
    }

    public function getDisplayMessage() {
        return $this->getMessage();
    }

    public function getToken() {
        return $this->getPaymentPlanID();
    }

    public function getCardType() {
        if (isset($this->result['cardBrand'])) {
            return $this->result['cardBrand'];
        }
        return '';
    }

    public function getCardUsage() {
        return '';
    }

    public function getMaskedAccount() {
        if (isset($this->result['lastFourDigits'])) {
            return $this->result['lastFourDigits'];
        }
        return '';
    }

    public function getTranType() {
        return MpTranType::Sale;
    }

    public function getPaymentIDExpired() {
        return '';
    }

    public function getCardHolderName() {
        if (isset($this->result['cardHolderName'])) {
            return $this->result['cardHolderName'];
        }
        return '';
    }

    public function getExpDate() {

        if (isset($this->result['cardExpirationMonth']) && isset($this->result['cardExpirationYear'])) {

	    if($this->result['cardExpirationMonth'] < 10){
            	$month = '0' . $this->result['cardExpirationMonth'];
            }else{
	        $month = $this->result['cardExpirationMonth'];
            }

            $year = $this->result['cardExpirationYear'];

            return $month . '/' . $year;
        }

        return '';
    }

    public function getAcqRefData() {
        return '';
    }

    public function getAuthorizeAmount() {
        if (isset($this->result['Amount'])) {
            return $this->result['Amount'];
        }
        return '';
    }

    public function getAuthCode() {

        if (isset($this->result['authorizationNumber'])) {
            return $this->result['authorizationNumber'];
        }
        return '';
    }

    public function getAVSAddress() {
        return '';
    }

    public function getAVSResult() {
        //AddressVerificationResponseCode
        return '';
    }

    public function getAVSZip() {
        return '';
    }

    public function getCvvResult() {
        //CardVerificationResponseCode
        return '';
    }

    public function getInvoice() {
        if (isset($this->result['InvoiceNumber'])) {
            return $this->result['InvoiceNumber'];
        }

        return '';
    }

    public function getMemo() {
        return '';
    }

    public function getPaymentPlanID() {
        if (isset($this->result['paymentPlanID'])) {
            return $this->result['paymentPlanID'];
        }
        return '';
    }

    public function getPrimaryTransactionID() {
        if (isset($this->result['primaryTransactionID'])) {
            return $this->result['primaryTransactionID'];
        }
        return '';
    }

    public function getProcessData() {
        return $this->getPrimaryTransactionID();
    }

    public function getRefNo() {
        return $this->getTransactionId();
    }

    public function getTransactionId() {
        if (isset($this->result['transactionID'])) {
            return $this->result['transactionID'];
        }
        return '';
    }
    public function getTransactionStatus() {
        if (isset($this->result['transactionStatus'])) {
            return $this->result['transactionStatus'];
        }
        return '';
    }

    public function getTaxAmount() {
        return '';
    }

    public function getAmount() {
        if (isset($this->result['Amount'])) {
            return $this->result['Amount'];
        }
        return '';
    }

    public function getTransPostTime() {
        if (isset($this->result['transactionDate'])) {
            return $this->result['transactionDate'];
        }
        return '';
    }

    public function getCustomerCode() {
        return '';
    }

    public function getOperatorID() {
        return '';
    }


}

class VerifyVoidResponse extends VerifyCurlResponse {


    // responseCode=000
    // &responseMessage=APPROVED
    // &transactionStatus=C
    // &primaryTransactionID=EE52401813A74328AAA7D93319FF4383
    // &primaryTransactionStatus=V
}

class VerifyReturnResponse extends VerifyCurlResponse {


}


class HeaderResponse extends GatewayResponse {

    protected function parseResponse() {

        //"https://online.instamed.com/providers/Form/SSO/SSOError?respCode=401&respMessage=Invalid AccountID or Password.&lightWeight=true"

        if (isset($this->response[InstamedGateway::RELAY_STATE])) {

            $qs = parse_url($this->response[InstamedGateway::RELAY_STATE], PHP_URL_QUERY);
            parse_str($qs, $this->result);

            $this->result[InstamedGateway::RELAY_STATE] = $this->response[InstamedGateway::RELAY_STATE];

        } else {
            $this->errors = 'response is missing. ';
        }

    }

    public function getRelayState() {
        return $this->result[InstamedGateway::RELAY_STATE];
    }

    public function getToken() {

        if (isset($this->result['token'])) {
            return $this->result['token'];
        }

        return '';
    }

    public function getResponseCode() {

        if (isset($this->result['respCode'])) {

            return intval($this->result['respCode'], 10);
        }

        return 0;
    }

    public function getResponseMessage() {

        if (isset($this->result['respMessage'])) {
            return $this->result['respMessage'];
        }

        return '';
    }
}

class CurlRequest {

    protected $gateWay;

    public function submit($parmStr, $url = '', $trace = FALSE) {

        if ($url == '') {
            $url = "https://online.instamed.com/payment/NVP.aspx?";
        }

        $xaction = $this->execute($url, $parmStr);

        try {
            if ($trace) {
                file_put_contents(REL_BASE_DIR . 'patch' . DS . 'soapLog.xml', '; |new__' . $parmStr . '|||' . json_encode($xaction), FILE_APPEND);
            }

        } catch(Exception $ex) {

            throw new Hk_Exception_Payment('Trace file error:  ' . $ex->getMessage());
        }

        return $xaction;
    }

    protected function execute($url, $params) {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url . $params);
        curl_setopt($ch, CURLOPT_USERPWD, "NP.SOFTWARE.TEST:vno9cFqM");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $responseString = curl_exec($ch);
        $msg = curl_error($ch);
        curl_close($ch);

        if ( ! $responseString ) {
            throw new Hk_Exception_Payment('Network (cURL) Error: ' . $msg);
        }

        $transaction = array();
        parse_str($responseString, $transaction);

        return $transaction;
    }

}


abstract class SoapRequest {

    protected $gateWay;

    public function submit(array $req, $url, $trace = FALSE) {

        try {
            // Create the Soap, prepre the data
            $txClient = new SoapClient($url, array('trace'=>$trace));

            $xaction = $this->execute($txClient, $req);

        } catch (SoapFault $sf) {

            throw new Hk_Exception_Payment('Problem with HHK web server contacting the payment gateway:  ' . $sf->getMessage() .     ' (' . $sf->getCode() . '); ' . ' Trace: ' . $sf->getTraceAsString());
        }

        try {
            if ($trace) {
                file_put_contents(REL_BASE_DIR . 'patch' . DS . 'soapLog.xml', $txClient->__getLastRequest() . $txClient->__getLastResponse(), FILE_APPEND);
            }

        } catch(Exception $ex) {

            throw new Hk_Exception_Payment('Trace file error:  ' . $ex->getMessage());
        }

        return $xaction;
    }

    protected abstract function execute(SoapClient $sc, $data);

}

