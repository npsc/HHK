<?php
/**
 * VantivGateway.php
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2019 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */


/**
 * Description of VantivGateway
 *
 * @author Eric
 */

class VantivGateway extends PaymentGateway {

    const CARD_ID = 'CardID';
    const PAYMENT_ID = 'PaymentID';

    public static function getPaymentMethod() {
        return PaymentMethod::Charge;
    }

    public function getGatewayName() {
        return 'vantiv';
    }

    public function creditSale(\PDO $dbh, $pmp, $invoice, $postbackUrl) {

        $uS = Session::getInstance();
        $payResult = NULL;

        $guest = new Guest($dbh, '', $invoice->getSoldToId());
        $addr = $guest->getAddrObj()->get_data($guest->getAddrObj()->get_preferredCode());

        $tokenRS = CreditToken::getTokenRsFromId($dbh, $pmp->getIdToken());

        // Do we have a token?
        if (CreditToken::hasToken($tokenRS)) {

            $cpay = new CreditSaleTokenRequest();

            $cpay->setPurchaseAmount($invoice->getAmountToPay())
                    ->setTaxAmount(0)
                    ->setCustomerCode($invoice->getSoldToId())
                    ->setAddress($addr["Address_1"])
                    ->setZip($addr["Postal_Code"])
                    ->setToken($tokenRS->Token->getStoredVal())
                    ->setPartialAuth(FALSE)
                    ->setCardHolderName($tokenRS->CardHolderName->getStoredVal())
                    ->setFrequency(MpFrequencyValues::OneTime)
                    ->setInvoice($invoice->getInvoiceNumber())
                    ->setTokenId($tokenRS->idGuest_token->getStoredVal())
                    ->setMemo(MpVersion::PosVersion);

            // Run the token transaction
            $tokenResp = TokenTX::CreditSaleToken($dbh, $invoice->getSoldToId(), $invoice->getIdGroup(), $this, $cpay, $pmp->getPayNotes());

            // Analyze the result
            $payResult = $this->analyzeCredSaleResult($dbh, $tokenResp, $invoice, $pmp->getIdToken(), $this->useAVS, $this->useCVV);
        } else {

            // Initialiaze hosted payment
            $fwrder = $this->initHostedPayment($dbh, $invoice, $guest, $addr, $postbackUrl);

            $payIds = array();
            if (isset($uS->paymentIds)) {
                $payIds = $uS->paymentIds;
            }

            $payIds[$fwrder['paymentId']] = $invoice->getIdInvoice();
            $uS->paymentIds = $payIds;
            $uS->paymentNotes = $pmp->getPayNotes();

            $payResult = new PaymentResult($invoice->getIdInvoice(), $invoice->getIdGroup(), $invoice->getSoldToId());
            $payResult->setForwardHostedPayment($fwrder);
            $payResult->setDisplayMessage('Forward to Payment Page. ');
        }

        return $payResult;
    }

    public function voidReturn(\PDO $dbh, Invoice $invoice, PaymentRS $payRs, Payment_AuthRS $pAuthRs) {

        $uS = Session::getInstance();

        // find the token record
        if ($payRs->idToken->getStoredVal() > 0) {
            $tknRs = CreditToken::getTokenRsFromId($dbh, $payRs->idToken->getStoredVal());
        } else {
            return array('warning' => 'Card-on-File not found.  Unable to Void this return.  ');
        }

        if (CreditToken::hasToken($tknRs) === FALSE) {
            return array('warning' => 'Card-on-File not found.  Unable to Void this return.  ');
        }

        // Set up request
        $revRequest = new CreditVoidReturnTokenRequest();
        $revRequest->setAuthCode($pAuthRs->Approval_Code->getStoredVal())
            ->setCardHolderName($tknRs->CardHolderName->getStoredVal())
            ->setFrequency(MpFrequencyValues::OneTime)->setMemo(MpVersion::PosVersion)
            ->setInvoice($invoice->getInvoiceNumber())
            ->setPurchaseAmount($pAuthRs->Approved_Amount->getStoredVal())
            ->setRefNo($pAuthRs->Reference_Num->getStoredVal())
            ->setToken($tknRs->Token->getStoredVal())
            ->setTokenId($tknRs->idGuest_token->getStoredVal())
            ->setTitle('CreditVoidReturnToken');

        try {

            $csResp = TokenTX::creditVoidReturnToken($dbh, $payRs->idPayor->getstoredVal(), $invoice->getIdGroup(), $this, $revRequest, $payRs);

            switch ($csResp->getStatus()) {

                case CreditPayments::STATUS_APPROVED:

                    // Update invoice
                    $invoice->updateInvoiceBalance($dbh, $csResp->response->getAuthorizedAmount(), $uS->username);

                    $csResp->idVisit = $invoice->getOrderNumber();
                    $dataArray['receipt'] = HTMLContainer::generateMarkup('div', nl2br(Receipt::createVoidMarkup($dbh, $csResp, $uS->siteName, $uS->sId, 'Void Return')));
                    $dataArray['success'] = 'Return is Voided.  ';

                    break;

                case CreditPayments::STATUS_DECLINED:

                    $dataArray['success'] = 'Declined.';
                    break;

                default:

                    $dataArray['warning'] = '** Void-Return Invalid or Error. **  ' . 'Message: ' . $csResp->response->getMessage();

            }

        } catch (Hk_Exception_Payment $exPay) {

            $dataArray['warning'] = "Void-Return Error = " . $exPay->getMessage();
        }

        return $dataArray;
    }

    public function reverseSale(\PDO $dbh, PaymentRS $payRs, Invoice $invoice, $bid, $paymentNotes) {

        $uS = Session::getInstance();

        // find the token record
        if ($payRs->idToken->getStoredVal() > 0) {
            $tknRs = CreditToken::getTokenRsFromId($dbh, $payRs->idToken->getStoredVal());
        } else {
            return array('warning' => 'Payment Token Id not found.  Unable to Reverse this purchase.  ', 'bid' => $bid);
        }

        if (CreditToken::hasToken($tknRs) === FALSE) {
            return array('warning' => 'Payment Token not found.  Unable to Reverse this purchase.  ', 'bid' => $bid);
        }

        // Find hte detail record.
        $stmt = $dbh->query("Select * from payment_auth where idPayment = " . $payRs->idPayment->getStoredVal() . " order by idPayment_auth");
        $arows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($arows) < 1) {
            return array('warning' => 'Payment Detail record not found.  Unable to Reverse this purchase. ', 'bid' => $bid);
        }

        $pAuthRs = new Payment_AuthRS();
        EditRS::loadRow(array_pop($arows), $pAuthRs);

        if ($pAuthRs->Status_Code->getStoredVal() == PaymentStatusCode::Paid || $pAuthRs->Status_Code->getStoredVal() == PaymentStatusCode::VoidReturn) {

            // Set up request
            $revRequest = new CreditReversalTokenRequest();
            $revRequest->setAuthCode($pAuthRs->Approval_Code->getStoredVal())
                    ->setCardHolderName($tknRs->CardHolderName->getStoredVal())
                    ->setFrequency(MpFrequencyValues::OneTime)->setMemo(MpVersion::PosVersion)
                    ->setInvoice($invoice->getInvoiceNumber())
                    ->setPurchaseAmount($pAuthRs->Approved_Amount->getStoredVal())
                    ->setRefNo($pAuthRs->Reference_Num->getStoredVal())
                    ->setProcessData($pAuthRs->ProcessData->getStoredVal())
                    ->setAcqRefData($pAuthRs->AcqRefData->getStoredVal())
                    ->setToken($tknRs->Token->getStoredVal())
                    ->setTokenId($tknRs->idGuest_token->getStoredVal())
                    ->setTitle('CreditReversalToken');

            try {

                $csResp = TokenTX::creditReverseToken($dbh, $payRs->idPayor->getstoredVal(), $invoice->getIdGroup(), $this, $revRequest, $payRs, $paymentNotes);

                switch ($csResp->response->getStatus()) {

                    case MpStatusValues::Approved:

                        // Update invoice
                        $invoice->updateInvoiceBalance($dbh, 0 - $csResp->response->getAuthorizedAmount(), $uS->username);


                        $csResp->idVisit = $invoice->getOrderNumber();
                        $dataArray['receipt'] = HTMLContainer::generateMarkup('div', nl2br(Receipt::createVoidMarkup($dbh, $csResp, $uS->siteName, $uS->sId, 'Reverse Sale')));
                        $dataArray['success'] = 'Transaction Reversed.  ';

                        break;

                    case MpStatusValues::Declined:

                        // Try Void
                        $dataArray = self::sendVoid($dbh, $payRs, $pAuthRs, $tknRs, $invoice, $paymentNotes);
                        $dataArray['reversal'] = 'Reversal Declined, trying Void.  ';

                        break;

                    default:

                        $dataArray['warning'] = '** Reversal Invalid or Error. **  ' . 'Message: ' . $csResp->response->getMessage();
                }
            } catch (Hk_Exception_Payment $exPay) {

                $dataArray['warning'] = "Reversal Error = " . $exPay->getMessage();
            }

            return $dataArray;
        }

        return array('warning' => 'Payment is ineligable for reversal.  ', 'bid' => $bid);
    }

    // Returns a Payment
    protected function _returnPayment(\PDO $dbh, PaymentRS $payRs, Payment_AuthRS $pAuthRs, Invoice $invoice, $returnAmt, $bid) {

        $uS = Session::getInstance();

        // find the token
        if ($payRs->idToken->getStoredVal() > 0) {
            $tknRs = CreditToken::getTokenRsFromId($dbh, $payRs->idToken->getStoredVal());
        } else {
            return array('warning' => 'Return Failed.  Payment Token not found.  ', 'bid' => $bid);
        }

        // Set up request
        $returnRequest = new CreditReturnTokenRequest();
        $returnRequest->setCardHolderName($tknRs->CardHolderName->getStoredVal());
        $returnRequest->setFrequency(MpFrequencyValues::OneTime)->setMemo(MpVersion::PosVersion);
        $returnRequest->setInvoice($invoice->getInvoiceNumber());

        // Determine amount to return
        $returnRequest->setPurchaseAmount($returnAmt);

        $returnRequest->setToken($tknRs->Token->getStoredVal());
        $returnRequest->setTokenId($tknRs->idGuest_token->getStoredVal());

        $dataArray = array('bid' => $bid);

        try {

            $csResp = TokenTX::creditReturnToken($dbh, $payRs->idPayor->getstoredVal(), $invoice->getIdGroup(), $this, $returnRequest, $payRs);

            switch ($csResp->response->getStatus()) {

                case MpStatusValues::Approved:


                    // Update invoice
                    $invoice->updateInvoiceBalance($dbh, 0 - $csResp->response->getAuthorizedAmount(), $uS->username);

                    $csResp->idVisit = $invoice->getOrderNumber();
                    $dataArray['receipt'] = HTMLContainer::generateMarkup('div', nl2br(Receipt::createReturnMarkup($dbh, $csResp, $uS->siteName, $uS->sId)));

                    break;

                case MpStatusValues::Declined:

                    return array('warning' => $csResp->response->getMessage(), 'bid' => $bid);

                    break;

                default:

                    return array('warning' => '** Return Invalid or Error. **  ', 'bid' => $bid);
            }

        } catch (Hk_Exception_Payment $exPay) {

            return array('warning' => "Payment Error = " . $exPay->getMessage(), 'bid' => $bid);
        }

        return $dataArray;
    }

    public function returnAmount(\PDO $dbh, Invoice $invoice, $rtnToken, $paymentNotes = '') {

        $uS = Session::getInstance();
        $rtnResult = NULL;
        $tokenRS = CreditToken::getTokenRsFromId($dbh, $rtnToken);
        $amount = abs($invoice->getAmount());

        // Do we have a token?
        if (CreditToken::hasToken($tokenRS)) {

            if ($tokenRS->Running_Total->getStoredVal() < $amount) {
                throw new Hk_Exception_Payment('Return Failed.  Maximum return for this card is: $' . number_format($tokenRS->Running_Total->getStoredVal(), 2));
            }

            // Set up request
            $returnRequest = new CreditReturnTokenRequest();
            $returnRequest->setCardHolderName($tokenRS->CardHolderName->getStoredVal());
            $returnRequest->setFrequency(MpFrequencyValues::OneTime)->setMemo(MpVersion::PosVersion);
            $returnRequest->setInvoice($invoice->getInvoiceNumber());
            $returnRequest->setPurchaseAmount($amount);

            $returnRequest->setToken($tokenRS->Token->getStoredVal());
            $returnRequest->setTokenId($tokenRS->idGuest_token->getStoredVal());


            $tokenResp = TokenTX::creditReturnToken($dbh, $invoice->getSoldToId(), $invoice->getIdGroup(), $this, $returnRequest, NULL, $paymentNotes);

            // Analyze the result
            $rtnResult = new ReturnResult($invoice->getIdInvoice(), $invoice->getIdGroup(), $invoice->getSoldToId(), $tokenRS->idGuest_token->getStoredVal());

            switch ($tokenResp->getStatus()) {

                case CreditPayments::STATUS_APPROVED:

                    // Update invoice
                    $invoice->updateInvoiceBalance($dbh, 0 - $tokenResp->response->getAuthorizedAmount(), $uS->username);

                    $rtnResult->feePaymentAccepted($dbh, $uS, $tokenResp, $invoice);
                    $rtnResult->setDisplayMessage('Refund by Credit Card.  ');

                    break;

                case CreditPayments::STATUS_DECLINED:

                    $rtnResult->setStatus(PaymentResult::DENIED);
                    $rtnResult->feePaymentRejected($dbh, $uS, $tokenResp, $invoice);
                    $rtnResult->setDisplayMessage('** The Return is Declined. **  Message: ' . $tokenResp->response->getResponseMessage());

                    break;

                default:

                    $rtnResult->setStatus(PaymentResult::ERROR);
                    $rtnResult->feePaymentError($dbh, $uS);
                    $rtnResult->setDisplayMessage('** Return Invalid or Error **  Message: ' . $tokenResp->response->getResponseMessage());
            }

        } else {
            throw new Hk_Exception_Payment('Return Failed.  Credit card token not found.  ');
        }

        return $rtnResult;
    }

    Protected function initHostedPayment(\PDO $dbh, Invoice $invoice, Guest $guest, $addr, $postbackUrl) {

        $uS = Session::getInstance();

        // Do a hosted payment.
        $secure = new SecurityComponent();

        $houseUrl = $secure->getSiteURL();
        $siteUrl = $secure->getRootURL();
        $logo = $uS->PmtPageLogoUrl;

        if ($houseUrl == '' || $siteUrl == '') {
            throw new Hk_Exception_Runtime("The site/house URL is missing.  ");
        }

        if ($invoice->getSoldToId() < 1 || $invoice->getIdGroup() < 1) {
            throw new Hk_Exception_Runtime("Card Holder information is missing.  ");
        }

        $pay = new InitCkOutRequest($uS->siteName, 'Custom');


        // Card reader?
        if ($uS->CardSwipe) {
            $pay->setDefaultSwipe('Swipe')
                    ->setCardEntryMethod('Both')
                    ->setPaymentPageCode('Checkout_Url');
        } else {
            $pay->setPaymentPageCode('Checkout_Url');
        }

        // Set CC Gateway name
        $uS->ccgw = $this->getGatewayType();

        $pay->setPartialAuth(TRUE);

        $pay->setAVSZip($addr["Postal_Code"])
                ->setAVSAddress($addr['Address_1'])
                ->setCardHolderName($guest->getRoleMember()->get_fullName())
                ->setFrequency(MpFrequencyValues::OneTime)
                ->setInvoice($invoice->getInvoiceNumber())
                ->setMemo(MpVersion::PosVersion)
                ->setTaxAmount(0)
                ->setTotalAmount($invoice->getAmountToPay())
                ->setCompleteURL($houseUrl . $postbackUrl)
                ->setReturnURL($houseUrl . $postbackUrl)
                ->setTranType(MpTranType::Sale)
                ->setLogoUrl($siteUrl . $logo)
                ->setCVV('on')
                ->setAVSFields('both');

        $CreditCheckOut = HostedCheckout::sendToPortal($dbh, $this, $invoice->getSoldToId(), $invoice->getIdGroup(), $invoice->getInvoiceNumber(), $pay);

        return $CreditCheckOut;
    }

    public function initCardOnFile(\PDO $dbh, $pageTitle, $idGuest, $idGroup, $manualKey, $cardHolderName, $postBackPage) {

        $uS = Session::getInstance();

        $secure = new SecurityComponent();
        $config = new Config_Lite(ciCFG_FILE);

        $houseUrl = $secure->getSiteURL();
        $siteUrl = $secure->getRootURL();
        $logo = $config->getString('financial', 'PmtPageLogoUrl', '');

        if ($houseUrl == '' || $siteUrl == '') {
            throw new Hk_Exception_Runtime("The site/house URL is missing.  ");
        }

        if ($idGuest < 1 || $idGroup < 1) {
            throw new Hk_Exception_Runtime("Card Holder information is missing.  ");
        }


        $initCi = new InitCiRequest($pageTitle, 'Custom');

        // Card reader?
        if ($uS->CardSwipe) {
            $initCi->setDefaultSwipe('Swipe')
                    ->setCardEntryMethod('Both')
                    ->setPaymentPageCode('CardInfo_Url');
        } else {
            $initCi->setPaymentPageCode('CardInfo_Url');
        }

        // Set CC Gateway name
        $uS->ccgw = $this->getGatewayType();


        $initCi->setCardHolderName($cardHolderName)
                ->setFrequency(MpFrequencyValues::OneTime)
                ->setCompleteURL($houseUrl . $postBackPage)
                ->setReturnURL($houseUrl . $postBackPage)
                ->setLogoUrl($siteUrl . $logo);


        return CardInfo::sendToPortal($dbh, $this, $idGuest, $idGroup, $initCi);
    }

    public function processHostedReply(\PDO $dbh, $post, $ssoToken, $idInv, $payNotes) {

        $payResult = NULL;
        $rtnCode = '';
        $rtnMessage = '';

        if (isset($post['ReturnCode'])) {
            $rtnCode = intval(filter_var($post['ReturnCode'], FILTER_SANITIZE_NUMBER_INT), 10);
        }

        if (isset($post['ReturnMessage'])) {
            $rtnMessage = filter_var($post['ReturnMessage'], FILTER_SANITIZE_STRING);
        }


        if (isset($post[VantivGateway::CARD_ID])) {

            $cardId = filter_var($post[VantivGateway::CARD_ID], FILTER_SANITIZE_STRING);
            $cidInfo = $this->getInfoFromCardId($dbh, $cardId);

            // Save postback in the db.
            try {
                self::logGwTx($dbh, $rtnCode, '', json_encode($post), 'CardInfoPostBack');
            } catch (Exception $ex) {
                // Do nothing
            }

            if ($rtnCode > 0) {

                $payResult = new cofResult($rtnMessage, PaymentResult::ERROR, 0, 0);
                return $payResult;
            }

            try {

                $vr = CardInfo::portalReply($dbh, $this, $cidInfo);

                $payResult = new CofResult($vr->response->getDisplayMessage(), $vr->response->getStatus(), $vr->idPayor, $vr->idRegistration);

            } catch (Hk_Exception_Payment $hex) {
                $payResult = new cofResult($hex->getMessage(), PaymentResult::ERROR, 0, 0);
            }

        } else if (isset($post[VantivGateway::PAYMENT_ID])) {

            $paymentId = filter_var($post[VantivGateway::PAYMENT_ID], FILTER_SANITIZE_STRING);

            $cidInfo = $this->getInfoFromCardId($dbh, $paymentId);

            try {
                self::logGwTx($dbh, $rtnCode, '', json_encode($post), 'HostedCoPostBack');
            } catch (Exception $ex) {
                // Do nothing
            }

            if ($rtnCode > 0) {

                $payResult = new PaymentResult($idInv, $cidInfo['idGroup'], $cidInfo['idName']);
                $payResult->setStatus(PaymentResult::ERROR);
                $payResult->setDisplayMessage($rtnMessage);
                return $payResult;
            }

            try {

                $csResp = HostedCheckout::portalReply($dbh, $this, $cidInfo, $payNotes);

                if ($csResp->getInvoiceNumber() != '') {

                    $invoice = new Invoice($dbh, $csResp->getInvoiceNumber());

                    // Analyze the result
                    $payResult = $this->analyzeCredSaleResult($dbh, $csResp, $invoice, 0, $this->useAVS, $this->useCVV);

                } else {

                    $payResult = new PaymentResult($idInv, $cidInfo['idGroup'], $cidInfo['idName']);
                    $payResult->setStatus(PaymentResult::ERROR);
                    $payResult->setDisplayMessage('Invoice Not Found!  ');
                }
            } catch (Hk_Exception_Payment $hex) {

                $payResult = new PaymentResult($idInv, $cidInfo['idGroup'], $cidInfo['idName']);
                $payResult->setStatus(PaymentResult::ERROR);
                $payResult->setDisplayMessage($hex->getMessage());
            }
        }

        return $payResult;
    }

    protected function _voidSale(\PDO $dbh, PaymentRS $payRs, Payment_AuthRS $pAuthRs, Invoice $invoice, $paymentNotes = '', $bid = '') {

        $uS = Session::getInstance();
        $dataArray = array();

        // find the token record
        if ($payRs->idToken->getStoredVal() > 0) {
            $tknRs = CreditToken::getTokenRsFromId($dbh, $payRs->idToken->getStoredVal());
        } else {
            return array('warning' => 'Payment Token Id not found.  Unable to Void this purchase.  ', 'bid' => $bid);
        }

        if (CreditToken::hasToken($tknRs) === FALSE) {
            return array('warning' => 'Payment Token not found.  Unable to Void this purchase.  ', 'bid' => $bid);
        }

        // Set up request
        $voidRequest = new CreditVoidSaleTokenRequest();
        $voidRequest->setAuthCode($pAuthRs->Approval_Code->getStoredVal());
        $voidRequest->setCardHolderName($tknRs->CardHolderName->getStoredVal());
        $voidRequest->setFrequency(MpFrequencyValues::OneTime)->setMemo(MpVersion::PosVersion);
        $voidRequest->setInvoice($invoice->getInvoiceNumber());
        $voidRequest->setPurchaseAmount($pAuthRs->Approved_Amount->getStoredVal());
        $voidRequest->setRefNo($pAuthRs->Reference_Num->getStoredVal());
        $voidRequest->setToken($tknRs->Token->getStoredVal());
        $voidRequest->setTokenId($tknRs->idGuest_token->getStoredVal());

        try {

            $csResp = TokenTX::creditVoidSaleToken($dbh, $payRs->idPayor->getstoredVal(), $invoice->getIdGroup(), $this, $voidRequest, $payRs, $paymentNotes);

            switch ($csResp->response->getStatus()) {

                case MpStatusValues::Approved:

                    // Update invoice
                    $invoice->updateInvoiceBalance($dbh, 0 - $csResp->response->getAuthorizedAmount(), $uS->username);

                    $csResp->idVisit = $invoice->getOrderNumber();
                    $dataArray['receipt'] = HTMLContainer::generateMarkup('div', nl2br(Receipt::createVoidMarkup($dbh, $csResp, $uS->siteName, $uS->sId)));
                    $dataArray['success'] = 'Payment is void.  ';

                    break;

                case MpStatusValues::Declined:

                    if (strtoupper($csResp->response->getMessage()) == 'ITEM VOIDED') {

                        // Update invoice
                        $invoice->updateInvoiceBalance($dbh, 0 - $csResp->response->getAuthorizedAmount(), $uS->username);

                        $csResp->idVisit = $invoice->getOrderNumber();
                        $dataArray['receipt'] = HTMLContainer::generateMarkup('div', nl2br(Receipt::createVoidMarkup($dbh, $csResp, $uS->siteName, $uS->sId)));
                        $dataArray['success'] = 'Payment is void.  ';
                    } else {

                        $dataArray['warning'] = $csResp->response->getMessage();
                    }

                    break;

                default:

                    $dataArray['warning'] = '** Void Invalid or Error. **  ' . 'Message: ' . $csResp->response->getMessage();
            }
        } catch (Hk_Exception_Payment $exPay) {

            $dataArray['warning'] = "Void Error = " . $exPay->getMessage();
        }

        return $dataArray;
    }

    public function analyzeCredSaleResult(\PDO $dbh, PaymentResponse $payResp, \Invoice $invoice, $idToken, $useAVS, $useCVV) {

        $uS = Session::getInstance();

        $payResult = new PaymentResult($invoice->getIdInvoice(), $invoice->getIdGroup(), $invoice->getSoldToId(), $idToken);


        switch ($payResp->getStatus()) {

            case CreditPayments::STATUS_APPROVED:

                // Update invoice
                $invoice->updateInvoiceBalance($dbh, $payResp->response->getAuthorizedAmount(), $uS->username);

                $payResult->feePaymentAccepted($dbh, $uS, $payResp, $invoice);
                $payResult->setDisplayMessage('Paid by Credit Card.  ');

                if ($payResp->isPartialPayment()) {
                    $payResult->setDisplayMessage('** Partially Approved Amount: ' . number_format($payResp->response->getAuthorizedAmount(), 2) . ' (Remaining Balance Due: ' . number_format($invoice->getBalance(), 2) . ').  ');
                }

                if ($useAVS) {
                    $avsResult = new AVSResult($payResp->response->getAVSResult());

                    if ($avsResult->isZipMatch() === FALSE) {
                        $payResult->setDisplayMessage($avsResult->getResultMessage() . '  ');
                    }
                }

                if ($useCVV) {
                    $cvvResult = new CVVResult($payResp->response->getCvvResult());
                    if ($cvvResult->isCvvMatch() === FALSE && $uS->CardSwipe === FALSE) {
                        $payResult->setDisplayMessage($cvvResult->getResultMessage() . '  ');
                    }
                }

                break;

            case CreditPayments::STATUS_DECLINED:

                $payResult->setStatus(PaymentResult::DENIED);
                $payResult->feePaymentRejected($dbh, $uS, $payResp, $invoice);

                $msg = '** The Payment is Declined. **';
                if ($payResp->response->getResponseMessage() != '') {
                    $msg .= 'Message: ' . $payResp->response->getResponseMessage();
                }
                $payResult->setDisplayMessage($msg);

                break;

            default:

                $payResult->setStatus(PaymentResult::ERROR);
                $payResult->feePaymentError($dbh, $uS);
                $payResult->setDisplayMessage('** Payment Invalid or Error **  Message: ' . $payResp->response->getResponseMessage());
        }

        return $payResult;
    }

    public function getPaymentResponseObj(iGatewayResponse $creditTokenResponse, $idPayor, $idGroup, $invoiceNumber, $idToken = 0, $payNotes = '') {
        return new TokenResponse($creditTokenResponse, $idPayor, $idToken, $payNotes);
    }

    public function getCofResponseObj(iGatewayResponse $verifyCiResponse, $idPayor, $idGroup) {
        return new CardInfoResponse($verifyCiResponse, $idPayor, $idGroup);
    }

    protected function loadGateway(\PDO $dbh) {

        $query = "select * from `cc_hosted_gateway` where cc_name = :ccn and Gateway_Name = :gwname";
        $stmt = $dbh->prepare($query);
        $stmt->execute(array(':ccn' => $this->getGatewayType(), ':gwname'=>$this->getGatewayName()));

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) < 1) {
            $rows[0] = array();
        }

        if (isset($rows[0]['Password']) && $rows[0]['Password'] != '') {
            $rows[0]['Password'] = decryptMessage($rows[0]['Password']);
        }

        $gwRs = new Cc_Hosted_GatewayRS();
        EditRS::loadRow($rows[0], $gwRs);

        $this->useAVS = filter_var($gwRs->Use_AVS_Flag->getStoredVal(), FILTER_VALIDATE_BOOLEAN);
        $this->useCVV = filter_var($gwRs->Use_Ccv_Flag->getStoredVal(), FILTER_VALIDATE_BOOLEAN);

        return $rows[0];
    }

    protected function setCredentials($gwRow) {

        $this->credentials = $gwRow;
    }

    public function selectPaymentMarkup(\PDO $dbh, &$payTbl) {

        $myGwType = '';

        $stmt = $dbh->query("Select DISTINCT CC_Gateway, Title from location where Status = 'a'");
        $gwRows = $stmt->fetchAll();

        $selArray = array('id'=>'selccgw', 'size'=>count($gwRows));

        if (is_array($this->gwType)) {
            // Show choice of gateway

            $sel = HTMLSelector::doOptionsMkup($gwRows, $myGwType, FALSE);

            $payTbl->addBodyTr(
                    HTMLTable::makeTh('Select House:')
                    .HTMLTable::makeTd(
                            HTMLSelector::generateMarkup($sel, $selArray)
                            , array('colspan'=>'2')
                    )
                    , array('id'=>'trvdCHName'));

        } else {

            $payTbl->addBodyTr(
                    HTMLTable::makeTh('Selected House:')
                    .HTMLTable::makeTd(ucfirst($this->gwType), array('colspan'=>'2'))
                    );
        }

    }

    protected static function _createEditMarkup(\PDO $dbh, $gatewayName, $resultMessage = '') {

        $gwRs = new Cc_Hosted_GatewayRS();
        $gwRs->Gateway_Name->setStoredVal($gatewayName);
        $rows = EditRS::select($dbh, $gwRs, array($gwRs->Gateway_Name));

        $opts = array(
            array(0, 'False'),
            array(1, 'True'),
        );

        $usePOS = SysConfig::getKeyValue($dbh, 'sys_config', 'CardSwipe');

        if ($usePOS) {
            $pos = 1;
        } else {
            $pos = 0;
        }

        $tbl = new HTMLTable();

        // Use Care Swipe
        $tbl->addBodyTr(
                HTMLTable::makeTh('Use Card Swiper', array('style' => 'border-top:2px solid black;'))
                .HTMLTable::makeTd(HTMLSelector::generateMarkup(HTMLSelector::doOptionsMkup($opts, $pos, FALSE), array('name' => 'selCardSwipe')))
                , array('style' => 'border-top:2px solid black;')
        );

        // hosted co page image
        $tbl->addBodyTr(
                HTMLTable::makeTh('Payment Page Logo URL', array())
                .HTMLTable::makeTd(HTMLInput::generateMarkup(SysConfig::getKeyValue($dbh, 'sys_config', 'PmtPageLogoUrl'), array('name' => 'txtppUrl', 'size' => '80')))
        );

        // Spacer
        $tbl->addBodyTr(HTMLTable::makeTd('&nbsp', array('colspan'=>'2')));

        foreach ($rows as $r) {

            $gwRs = new Cc_Hosted_GatewayRS();
            EditRS::loadRow($r, $gwRs);

            $indx = $gwRs->idcc_gateway->getStoredVal();

            $tbl->addBodyTr(
                    HTMLTable::makeTh('Name', array('style' => 'border-top:2px solid black;'))
                    . HTMLTable::makeTd($gwRs->cc_name->getStoredVal(), array('style' => 'border-top:2px solid black;'))
            );

            $tbl->addBodyTr(
                    HTMLTable::makeTh('Merchant Id', array())
                    . HTMLTable::makeTd(HTMLInput::generateMarkup($gwRs->Merchant_Id->getStoredVal(), array('name' => $indx . '_txtuid', 'size' => '50')))
            );
            $tbl->addBodyTr(
                    HTMLTable::makeTh('Password', array())
                    . HTMLTable::makeTd(HTMLInput::generateMarkup($gwRs->Password->getStoredVal(), array('name' => $indx . '_txtpwd', 'size' => '80')) . ' (Obfuscated)')
            );
            $tbl->addBodyTr(
                    HTMLTable::makeTh('Credit URL', array())
                    . HTMLTable::makeTd(HTMLInput::generateMarkup($gwRs->Credit_Url->getStoredVal(), array('name' => $indx . '_txtcrdurl', 'size' => '90')))
            );
            $tbl->addBodyTr(
                    HTMLTable::makeTh('Token Trans URL', array())
                    . HTMLTable::makeTd(HTMLInput::generateMarkup($gwRs->Trans_Url->getStoredVal(), array('name' => $indx . '_txttransurl', 'size' => '90')))
            );

            if ($usePOS) {
                $tbl->addBodyTr(
                    HTMLTable::makeTh('CheckoutPOS URL', array())
                    . HTMLTable::makeTd(HTMLInput::generateMarkup($gwRs->CheckoutPOS_Url->getStoredVal(), array('name' => $indx . '_txtcoposurl', 'size' => '90')))
                );
            } else {
                $tbl->addBodyTr(
                        HTMLTable::makeTh('Checkout URL', array())
                        . HTMLTable::makeTd(HTMLInput::generateMarkup($gwRs->Checkout_Url->getStoredVal(), array('name' => $indx . '_txtckouturl', 'size' => '90')))
                );
            }

            $tbl->addBodyTr(
                    HTMLTable::makeTh('Card Info URL', array())
                    . HTMLTable::makeTd(HTMLInput::generateMarkup($gwRs->CardInfo_Url->getStoredVal(), array('name' => $indx . '_txtciurl', 'size' => '90')))
            );
            $tbl->addBodyTr(
                    HTMLTable::makeTh('Use AVS', array())
                    .HTMLTable::makeTd(HTMLSelector::generateMarkup(HTMLSelector::doOptionsMkup($opts, $gwRs->Use_AVS_Flag->getStoredVal(), FALSE), array('name' => $indx . '_txtuseAVS')))
            );

            $tbl->addBodyTr(
                    HTMLTable::makeTh('Use CCV', array())
                    .HTMLTable::makeTd(HTMLSelector::generateMarkup(HTMLSelector::doOptionsMkup($opts, $gwRs->Use_Ccv_Flag->getStoredVal(), FALSE), array('name' => $indx . '_txtuseCVV')))
            );

        }

        if ($resultMessage != '') {
            $tbl->addBodyTr(HTMLTable::makeTd($resultMessage, array('colspan' => '2', 'style' => 'font-weight:bold;')));
        }

        return $tbl->generateMarkup();
    }

    protected static function _saveEditMarkup(\PDO $dbh, $gatewayName, $post) {

        $msg = '';
        $ccRs = new Cc_Hosted_GatewayRS();
        $ccRs->Gateway_Name->setStoredVal($gatewayName);
        $rows = EditRS::select($dbh, $ccRs, array($ccRs->Gateway_Name));

        // Use POS
        if (isset($post['selCardSwipe'])) {
            SysConfig::saveKeyValue($dbh, 'sys_config', 'CardSwipe', filter_var($post['selCardSwipe'], FILTER_SANITIZE_STRING));
        }

        // host page image URL
        if (isset($post['txtppUrl'])) {
            SysConfig::saveKeyValue($dbh, 'sys_config', 'PmtPageLogoUrl', filter_var($post['txtppUrl'], FILTER_SANITIZE_STRING));
        }

        foreach ($rows as $r) {

            EditRS::loadRow($r, $ccRs);

            $indx = $ccRs->idcc_gateway->getStoredVal();

            // Merchant Id
            if (isset($post[$indx . '_txtuid'])) {
                $ccRs->Merchant_Id->setNewVal(filter_var($post[$indx . '_txtuid'], FILTER_SANITIZE_STRING));
            }

            // Credit URL
            if (isset($post[$indx . '_txtcrdurl'])) {
                $ccRs->Credit_Url->setNewVal(filter_var($post[$indx . '_txtcrdurl'], FILTER_SANITIZE_STRING));
            }

            // Transaction URL
            if (isset($post[$indx . '_txttransurl'])) {
                $ccRs->Trans_Url->setNewVal(filter_var($post[$indx . '_txttransurl'], FILTER_SANITIZE_STRING));
            }

            // Checkout URL
            if (isset($post[$indx . '_txtckouturl'])) {
                $ccRs->Checkout_Url->setNewVal(filter_var($post[$indx . '_txtckouturl'], FILTER_SANITIZE_STRING));
            }

            // Chekout POS URL
            if (isset($post[$indx . '_txtcoposurl'])) {
                $ccRs->CheckoutPOS_Url->setNewVal(filter_var($post[$indx . '_txtcoposurl'], FILTER_SANITIZE_STRING));
            }

            // Card Info URL
            if (isset($post[$indx . '_txtciurl'])) {
                $ccRs->CardInfo_Url->setNewVal(filter_var($post[$indx . '_txtciurl'], FILTER_SANITIZE_STRING));
            }

            // Use AVS
            if (isset($post[$indx . '_txtuseAVS'])) {
                $ccRs->Use_AVS_Flag->setNewVal(filter_var($post[$indx . '_txtuseAVS'], FILTER_SANITIZE_STRING));
            }

            // Use CCV
            if (isset($post[$indx . '_txtuseCVV'])) {
                $ccRs->Use_Ccv_Flag->setNewVal(filter_var($post[$indx . '_txtuseCVV'], FILTER_SANITIZE_STRING));
            }

            // Password
            if (isset($post[$indx . '_txtpwd'])) {

                $pw = filter_var($post[$indx . '_txtpwd'], FILTER_SANITIZE_STRING);

                if ($pw != '' && $ccRs->Password->getStoredVal() != $pw) {
                    $ccRs->Password->setNewVal(encryptMessage($pw));
                } else if ($pw == '') {
                    $ccRs->Password->setNewVal('');
                }
            }


            // Save record.
            $num = EditRS::update($dbh, $ccRs, array($ccRs->Gateway_Name, $ccRs->idcc_gateway));

            if ($num > 0) {
                $msg .= HTMLContainer::generateMarkup('p', $ccRs->Gateway_Name->getStoredVal() . " " . $ccRs->cc_name->getStoredVal() . " - Payment Credentials Updated.  ");
            } else {
                $msg .= HTMLContainer::generateMarkup('p', $ccRs->Gateway_Name->getStoredVal() . " " . $ccRs->cc_name->getStoredVal() . " - No changes detected.  ");
            }
        }

        return $msg;
    }

}
