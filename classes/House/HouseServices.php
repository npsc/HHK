<?php

namespace HHK\House;


use HHK\HTMLControls\{HTMLContainer, HTMLInput, HTMLTable};
use HHK\House\Visit\VisitViewer;
use HHK\SysConst\AddressPurpose;
use HHK\SysConst\GLTableNames;
use HHK\SysConst\VisitStatus;
use HHK\sec\Labels;
use HHK\sec\Session;
use HHK\Purchase\PriceModel\AbstractPriceModel;
use HHK\Purchase\VisitCharges;
use HHK\House\Reservation\Reservation_1;
use HHK\House\Room\RoomChooser;
use HHK\House\Visit\Visit;
use HHK\Note\LinkNote;
use HHK\Note\Note;
use HHK\Purchase\RateChooser;
use HHK\Payment\CreditToken;
use HHK\Payment\PaymentGateway\AbstractPaymentGateway;
use HHK\Payment\PaymentManager\PaymentManager;
use HHK\Purchase\PaymentChooser;
use HHK\House\Resource\AbstractResource;
use HHK\Payment\PaymentResult\PaymentResult;
use HHK\SysConst\ExcessPay;
use HHK\SysConst\ItemId;
use HHK\Payment\Invoice\InvoiceLine\OneTimeInvoiceLine;
use HHK\Purchase\Item;
use HHK\Payment\Invoice\Invoice;
use HHK\Purchase\ValueAddedTax;
use HHK\Payment\Invoice\InvoiceLine\TaxInvoiceLine;
use HHK\Payment\PaymentManager\PaymentManagerPayment;
use HHK\SysConst\PayType;
use HHK\SysConst\InvoiceStatus;
use HHK\Tables\Visit\VisitRS;
use HHK\Tables\EditRS;
use HHK\Exception\RuntimeException;
use HHK\TableLog\VisitLog;
use HHK\SysConst\ReservationStatus;
use HHK\SysConst\RoomState;
use HHK\Member\Role\Guest;
use HHK\Tables\Visit\StaysRS;
use HHK\Exception\PaymentException;
use HHK\sec\SecurityComponent;
use HHK\Tables\Visit\Visit_LogRS;
use HHK\House\Resource\RoomResource;

/**
 * HouseServices.php
 *
 * @author    Eric K. Crane <ecrane@nonprofitsoftwarecorp.org>
 * @copyright 2010-2017 <nonprofitsoftwarecorp.org>
 * @license   MIT
 * @link      https://github.com/NPSC/HHK
 */

class HouseServices {

    /**
     * Show visit details
     *
     * @param \PDO $dbh
     * @param int $idGuest Supply either this or the next
     * @param int $idVisit
     * @param int $span span = 'max' means load last visit span, otherwise load int value
     * @param boolean $isAdmin Administrator flag
     * @param string $action Processing code with various settings.
     *
     * @return array
     */
    public static function getVisitFees(\PDO $dbh, $idGuest, $idV, $idSpan, $isAdmin, $action = '', $coStayDates = []) {

        $uS = Session::getInstance();

        // Get labels
        $labels = Labels::getLabels();

        $idVisit = intval($idV, 10);
        $span = intval($idSpan, 10);

        if ($idVisit < 1 || $span < 0) {
            return array("error" => "A Visit is not selected: " . $idV . "-" . $idSpan);
        }

        $query = "select * from vspan_listing where idVisit = $idVisit and Span = $span;";
        $stmt1 = $dbh->query($query);
        $rows = $stmt1->fetchAll(\PDO::FETCH_ASSOC);

        $dataArray = array();


        if (count($rows) == 0) {
            return array("error" => "<span>No Data.</span>");
        }

        $r = $rows[0];

        // Hospital and association lists
        $r['Hospital'] = '';
        if (isset($uS->guestLookups[GLTableNames::Hospital][$r['idHospital']])) {
            $r['Hospital'] = $uS->guestLookups[GLTableNames::Hospital][$r['idHospital']][1];
        }

        $r['Association'] = '';
        if (isset($uS->guestLookups[GLTableNames::Hospital][$r['idAssociation']])) {
            $r['Association'] = $uS->guestLookups[GLTableNames::Hospital][$r['idAssociation']][1];
            if (trim($r['Association']) == '(None)') {
                $r['Association'] = '';
            }
        }

        if ($r['Span_End'] != '') {
            $vspanEndDT = new \DateTime($r['Span_End']);
            $vspanEndDT->sub(new \DateInterval('P1D'));
        } else {
            $vspanEndDT = new \DateTime();
        }

        $vspanEndDT->setTime(23, 59, 59);
        $vspanStartDT = new \DateTime($r['Span_Start']);

        $priceModel = AbstractPriceModel::priceModelFactory($dbh, $uS->RoomPriceModel);

        $visitCharge = new VisitCharges($r['idVisit']);
        $visitCharge->sumPayments($dbh);

        $coDate = '';
        
        if ($action == 'ref' && count($coStayDates) > 0) {
            // Visit is checking out to a different date than "today"
            
        	$coDateDT = new \DateTime('1900-01-01');
        	// find latest co date
        	foreach ($coStayDates as $c) {
        		
        		$cDT= new \DateTime($c);
        		
        		if ($cDT > $coDateDT) {
        			$coDateDT = $cDT;
        		}
        	}
        	
        	$coDate = $coDateDT->format('Y-m-d');
        	
            $visitCharge->sumDatedRoomCharge($dbh, $priceModel, $coDate, 0, TRUE);
            
            // if a previous stay checked out later than the checked in stay.
            $coDate = $visitCharge->getFinalVisitCoDate()->format('Y-m-d H:i:s');
            
        } else {
            $visitCharge->sumCurrentRoomCharge($dbh, $priceModel, 0, TRUE);
        }

        // Show adjust button?
        $showAdjust = FALSE;
        $hdArry = readGenLookupsPDO($dbh, "House_Discount");
        $adnlArray = readGenLookupsPDO($dbh, 'Addnl_Charge');

        if ($action != 'cf' && (count($hdArry) > 0 || count($adnlArray) > 0)) {
            $showAdjust = TRUE;
        }

        $mkup = HTMLInput::generateMarkup($uS->EmptyExtendLimit, array('id' =>'EmptyExtend', 'type' => 'hidden'));

        // Get main visit markup section
        $mkup .= HTMLContainer::generateMarkup('div',
                VisitViewer::createActiveMarkup(
                        $dbh,
                        $r,
                        $visitCharge,
                        $uS->KeyDeposit,
                        $uS->VisitFee,
                        $isAdmin,
                        $uS->EmptyExtendLimit,
                        $action,
                		$coDate,
                        $showAdjust)
                , array('style' => 'clear:left;margin-top:10px;'));

        // Change rooms control
        if ($action == 'cr' && $r['Status'] == VisitStatus::CheckedIn) {

            $expDepDT = new \DateTime($r['Expected_Departure']);
            $expDepDT->setTime(10, 0, 0);
            $now = new \DateTime();
            $now->setTime(10, 0, 0);

            if ($expDepDT < $now) {
                $expDepDT = $now->add(new \DateInterval('P1D'));
            }

            $reserv = Reservation_1::instantiateFromIdReserv($dbh, $r['idReservation'], $idVisit);
            $visit = new Visit($dbh, $reserv->getIdRegistration(), $idVisit);
            $roomChooser = new RoomChooser($dbh, $reserv, 0, $vspanStartDT, $expDepDT);
            $curResc = $roomChooser->getSelectedResource();
            $mkup .= $roomChooser->createChangeRoomsMarkup($dbh, $visitCharge, $idGuest, $isAdmin);
            $dataArray['resc'] = $roomChooser->makeRoomsArray();
            $dataArray['curResc'] = array(
                "maxOcc" => $curResc->getMaxOccupants(),
                "title" => $curResc->getTitle(),
                'key' => $curResc->getKeyDeposit($uS->guestLookups[GLTableNames::KeyDepositCode]),
                'status' => 'a',
                'merchant' => $curResc->getMerchant(),
            );
            $dataArray['deposit'] = $curResc->getKeyDeposit($uS->guestLookups[GLTableNames::KeyDepositCode]);
            $dataArray['idReservation'] = $r['idReservation'];
            $dataArray['cbRs'] = $reserv->getConstraints($dbh);
            $dataArray['numGuests'] = $reserv->getNumberGuests();
            $dataArray['visitStart'] = $visit->getArrivalDate();
            $dataArray['expDep'] = $expDepDT->format('c');

        // Pay fees
        } else if ($action == 'pf') {

            $mkup .= HTMLContainer::generateMarkup('div',
                    VisitViewer::createPaymentMarkup($dbh, $r, $visitCharge, $idGuest, $action), array('style' => 'min-width:600px;clear:left;'));

        } else {
            $mkup = HTMLContainer::generateMarkup('div',
            		VisitViewer::createStaysMarkup($dbh, $r['idReservation'], $idVisit, $span, $r['idPrimaryGuest'], $isAdmin, $idGuest, $labels, $action, $coStayDates) . $mkup, array('id'=>'divksStays'));

            // Show fees if not hf = hide fees.
            if ($action != 'hf') {
            	$mkup .= HTMLContainer::generateMarkup('div',
                    VisitViewer::createPaymentMarkup($dbh, $r, $visitCharge, $idGuest, $action), array('style' => 'min-width:600px;clear:left;'));
            }
        }

        $dataArray['success'] = $mkup;


        // Start and end dates for rate changer
        $dataArray['start'] = $vspanStartDT->format('c');
        $dataArray['end'] = $vspanEndDT->format('c');

        return $dataArray;
    }

    public static function saveFees(\PDO $dbh, $idVisit, $span, $isGuestAdmin, array $post, $postbackPage, $returnCkdIn = FALSE) {

        $uS = Session::getInstance();
        $dataArray = array();
        $creditCheckOut = array();
        $reply = '';
        $warning = '';
        $returnReserv = FALSE;
        $returntoVisit = FALSE;

        if ($idVisit == 0) {
            return array("error" => "Neither Guest or Visit was selected.");
        }

        // Remove any indicated visit stays
        if (isset($post['removeCb'])) {

            foreach ($post['removeCb'] as $r => $v) {
                $idStay = intval(filter_var($r, FILTER_SANITIZE_NUMBER_INT), 10);
                $reply .= VisitViewer::removeStay($dbh, $idVisit, $span, $idStay, $uS->username);
            }
        }

        // instantiate current visit
        $visit = new Visit($dbh, 0, $idVisit, NULL, NULL, NULL, $uS->username, $span);


        // Notes
        if (isset($post["taNewVNote"])) {

            $notes = filter_var($post["taNewVNote"], FILTER_SANITIZE_STRING);

            if ($notes != '' && $idVisit > 0) {
                LinkNote::save($dbh, $notes, $idVisit, Note::VisitLink, $uS->username, $uS->ConcatVisitNotes);
            }
        }


        // Change room rate
        if ($isGuestAdmin && isset($post['rateChgCB']) && isset($post['extendCb']) === FALSE) {
            $rateChooser = new RateChooser($dbh);
            $reply .= $rateChooser->changeRoomRate($dbh, $visit, $post);
            $returnCkdIn = TRUE;
        }

        // Change Visit Fee
        if (isset($post['selVisitFee'])) {

            $visitFeeOption = filter_var($post['selVisitFee'], FILTER_SANITIZE_STRING);

            $vFees = readGenLookupsPDO($dbh, 'Visit_Fee_Code');

            if (isset($vFees[$visitFeeOption])) {

                $resv = Reservation_1::instantiateFromIdReserv($dbh, $visit->getReservationId());

                if ($resv->isNew() === FALSE) {

                    if ($resv->getVisitFee() != $vFees[$visitFeeOption][2]) {
                        // visit fee is updated.

                        $visitCharge = new VisitCharges($idVisit);
                        $visitCharge->sumPayments($dbh);

                        if ($visitCharge->getVisitFeesPaid() > 0) {
                            // Change to no visit fee, already paid fee
                            $reply .= ' Return Cleaning Fee Payment and delete the invoice before changing it.  ';

                        } else {

                            $resv->setVisitFee($vFees[$visitFeeOption][2]);
                            $resv->saveReservation($dbh, $visit->getIdRegistration(), $uS->username);

                            $reply .= 'Cleaning Fee Setting Updated.  ';
                        }
                    }
                }
            }
        }

        // Change STAY Checkin date
        if (isset($post['stayCkInDate'])) {
            $reply .= $visit->checkStayStartDates($dbh, $post['stayCkInDate']);
        }


        // Undo checkout
        if (isset($post['undoCkout']) && $visit->getVisitStatus() == VisitStatus::CheckedOut) {

            // Get the new expected co date.
            if (isset($post['txtUndoDate'])) {

                $newExpectedDT = new \DateTime(filter_var($post['txtUndoDate'], FILTER_SANITIZE_STRING));

            } else {

                $newExpectedDT = new \DateTime($visit->getActualDeparture());
            }

            $reply .= self::undoCheckout($dbh, $visit, $newExpectedDT, $uS->username);
            $returnCkdIn = TRUE;

        // Not undoing checkout...
        } else {

            // Instantiate a payment manager payment container.
            $paymentManger = new PaymentManager(PaymentChooser::readPostedPayment($dbh, $post));


            // Process a checked in guest
            if ($visit->getVisitStatus() == VisitStatus::CheckedIn) {

                // Change any expected checkout dates
                if (isset($post['stayExpCkOut'])) {

                    $replyArray = $visit->changeExpectedCheckoutDates($dbh, $post['stayExpCkOut'], $uS->MaxExpected, $uS->username);

                    if (isset($replyArray['isChanged']) && $replyArray['isChanged']) {
                        $returnCkdIn = TRUE;
                        $returnReserv = TRUE;
                    }

                    $reply .= $replyArray['message'];
                }


                // Begin visit leave?
                if (isset($post['extendCb']) && $uS->EmptyExtendLimit > 0) {

                    $extendStartDate = '';
                    if (isset($post['txtWStart']) && $post['txtWStart'] != '') {
                        $extendStartDate = filter_var($post['txtWStart'], FILTER_SANITIZE_STRING);
                    }

                    $extDays = 0;
                    if (isset($post['extendDays'])) {
                        $extDays = intval(filter_var($post['extendDays'], FILTER_SANITIZE_NUMBER_INT), 10);
                    }

                    $noCharge = FALSE;
                    if (isset($post['noChargeCb'])) {
                        $noCharge = TRUE;
                    }

                    $reply .= $visit->beginLeave($dbh, $extendStartDate, $extDays, $noCharge);
                    $returnCkdIn = TRUE;
                }


                // Return from leave?
                if (isset($post['leaveRetCb']) && $uS->EmptyExtendLimit > 0) {

                    $extendReturnDate = '';
                    if (isset($post['txtWRetDate']) && $post['txtWRetDate'] != '') {
                        $extendReturnDate = filter_var($post['txtWRetDate'], FILTER_SANITIZE_STRING);
                    }

                    $returning = TRUE;
                    if (isset($post['noReturnRb'])) {
                        $returning = FALSE;
                    }

                    $reply .= $visit->endLeave($dbh, $returning, $extendReturnDate);
                    $returnCkdIn = TRUE;
                }


                // Change Rooms?
                if (isset($post['selResource'])) {

                    $newRescId = intval(filter_var($post['selResource'], FILTER_SANITIZE_NUMBER_INT), 10);

                    if ($newRescId != 0 && $newRescId != $visit->getidResource()) {

                        $resc = AbstractResource::getResourceObj($dbh, $newRescId);

                        $now = new \DateTime();

                        if (isset($post['rbReplaceRoomrpl'])) {

                            $chRoomDT = new \DateTime($visit->getSpanStart());

                        } else {

                            if (isset($post['resvChangeDate']) && $post['resvChangeDate'] != '') {

                                $chDT = new \DateTime(filter_var($post['resvChangeDate'], FILTER_SANITIZE_STRING));
                                $chRoomDT = new \DateTime($chDT->format('Y-m-d') . ' ' . $now->format('H:i:s'));

                            } else {
                                $chRoomDT = $now;
                            }
                        }

                        //if deposit needs to be paid
                        $curRescId = $visit->getidResource();
                        $curResc = AbstractResource::getResourceObj($dbh, $curRescId);
                        if($curResc->getKeyDeposit($uS->guestLookups[GLTableNames::KeyDepositCode]) < $resc->getKeyDeposit($uS->guestLookups[GLTableNames::KeyDepositCode])){
                            $returntoVisit = TRUE;
                        }
                        
                        $departDT = new \DateTime($visit->getExpectedDeparture());
                        $departDT->setTime($uS->CheckOutTime, 0, 0);
                        $now2 = new \DateTime();
                        $now2->setTime($uS->CheckOutTime, 0, 0);

                        if ($departDT < $now2) {
                            $departDT = $now2;
                        }

                        $arriveDT = new \DateTime($visit->getSpanStart());

                        if ($chRoomDT < $arriveDT || $chRoomDT > $now) {

                            $reply .= "The change room date must be within the visit timeframe, between " . $arriveDT->format('M j, Y') . ' and ' . $now->format('M j, Y');

                        } else {

                            $reply .= $visit->changeRooms($dbh, $resc, $uS->username, $chRoomDT, $isGuestAdmin);
                            $returnCkdIn = TRUE;
                            $returnReserv = TRUE;
                        }
                    }
                }


                // Change primary guest?
                if (isset($post['rbPriGuest'])) {
                    $newPg = intval(filter_var($post['rbPriGuest'], FILTER_SANITIZE_NUMBER_INT), 10);

                    if ($newPg > 0 && $newPg != $visit->getPrimaryGuestId()) {

                        $visit->setPrimaryGuestId($newPg);
                        $visit->updateVisitRecord($dbh, $uS->username);

                        $resv = Reservation_1::instantiateFromIdReserv($dbh, $visit->getReservationId());
                        $resv->setIdGuest($newPg);
                        $resv->saveReservation($dbh, $resv->getIdRegistration(), $uS->username);

                        $reply .= Labels::getString('MemberType', 'primaryGuest', 'Primary Guest') . ' Id updated.  ';
                    }
                }

                // Check-out any guests.
                if (isset($post['stayActionCb'])) {

                    // See whose checking out
                    foreach ($post['stayActionCb'] as $idr => $v) {

                        $id = intval(filter_var($idr, FILTER_SANITIZE_NUMBER_INT));
                        if ($id < 1) {
                            continue;
                        }

                        // Check out Date
                        $coDate = date('Y-m-d');
                        if (isset($post['stayCkOutDate'][$id]) && $post['stayCkOutDate'][$id] != '') {
                            $coDate = filter_var($post['stayCkOutDate'][$id], FILTER_SANITIZE_STRING);
                        }

                        $coHour = intval(date('H'), 10);
                        $coMin = intval(date('i'), 10);

                        if (isset($post['stayCkOutHour'][$id]) && $post['stayCkOutHour'][$id] != '') {
                            $coHour = intval(filter_var($post['stayCkOutHour'][$id], FILTER_SANITIZE_NUMBER_INT), 10);

                            if ($coHour < 0) {
                                $coHour = 0;
                            } else if ($coHour > 23) {
                                $coHour = 23;
                            }
                        }

                        $coDT = new \DateTime($coDate);
                        $coDT->setTimezone(new \DateTimeZone($uS->tz));

                        $coDT->setTime($coHour, $coMin, 0);

                        $visit->checkOutGuest($dbh, $id, $coDT->format('Y-m-d H:i:s'), '', TRUE);
                        
                        $reply .= $visit->getInfoMessage();
                        $warning .= $visit->getErrorMessage();
                        
                        $returnCkdIn = TRUE;

                    }
                }
            }


            // Make guest payment
            $payResult = self::processPayments($dbh, $paymentManger, $visit, $postbackPage, $visit->getPrimaryGuestId());

            if (is_null($payResult) === FALSE) {

                $reply .= $payResult->getReplyMessage();

                if ($payResult->getStatus() == PaymentResult::FORWARDED) {
                    $creditCheckOut = $payResult->getForwardHostedPayment();
                }

                // Receipt
                if (is_null($payResult->getReceiptMarkup()) === FALSE && $payResult->getReceiptMarkup() != '') {
                    $dataArray['receipt'] = HTMLContainer::generateMarkup('div', $payResult->getReceiptMarkup());

                    Registration::updatePrefTokenId($dbh, $visit->getIdRegistration(), $payResult->getIdToken());
                }

                // New Invoice
                if (is_null($payResult->getInvoiceNumber()) === FALSE && $payResult->getInvoiceNumber() != '') {
                    $dataArray['invoiceNumber'] = $payResult->getInvoiceNumber();
                }
            }
        }

        // Undo Room Change
        if (isset($post['undoRmChg']) && $visit->getVisitStatus() == VisitStatus::NewSpan) {

            $reply .= self::undoRoomChange($dbh, $visit, $uS->username);
            $returnCkdIn = TRUE;

        }


        // divert to credit payment site.
        if (count($creditCheckOut) > 0) {
            return $creditCheckOut;
        }

        // Return checked in guests markup?
        if ($returnCkdIn) {
            $dataArray['curres'] = 'y';
        }

        if ($returnReserv) {
            $dataArray['reservs'] = 'y';
            $dataArray['waitlist'] = 'y';

            if ($uS->ShowUncfrmdStatusTab) {
                $dataArray['unreserv'] = 'y';
            }
        }
        
        if($returntoVisit){
            $dataArray['openvisitviewer'] = 'y';
        }


        $dataArray['success'] = $reply;
        $dataArray['warning'] = $warning;

        return $dataArray;
    }

    public static function showPayInvoice(\PDO $dbh, $id, $iid) {

        $mkup = HTMLContainer::generateMarkup('div', PaymentChooser::createPayInvMarkup($dbh, $id, $iid), array('style' => 'min-width:600px;clear:left;'));

        return array('mkup'=>$mkup);
    }

    public static function payInvoice(\PDO $dbh, $idPayor, array $post) {

        $reply = 'Uh-oh, Payment NOT made.';
        $postbackPage = '';
        $dataArray = array();
        $creditCheckOut = array();

        if (isset($post['pbp'])) {
            $postbackPage = filter_var($post['pbp'], FILTER_SANITIZE_STRING);
        }

        // Instantiate a payment manager payment container.
        $paymentManager = new PaymentManager(PaymentChooser::readPostedPayment($dbh, $post));

        // Is it a return payment?
        if (is_null($paymentManager->pmp) === FALSE && $paymentManager->pmp->getTotalPayment() < 0) {

            // Here is where we look for the reimbursment pay type.
            $paymentManager->pmp->setRtnPayType($paymentManager->pmp->getPayType());
            $paymentManager->pmp->setRtnCheckNumber($paymentManager->pmp->getCheckNumber());
            $paymentManager->pmp->setRtnTransferAcct($paymentManager->pmp->getTransferAcct());
            $paymentManager->pmp->setBalWith(ExcessPay::Refund);
        }

        // Make payment
        $payResult = self::processPayments($dbh, $paymentManager, NULL, $postbackPage, $idPayor);

        if (is_null($payResult) === FALSE) {

            $reply = $payResult->getReplyMessage();

            if ($payResult->getStatus() == PaymentResult::FORWARDED) {
                $creditCheckOut = $payResult->getForwardHostedPayment();
            }

            // Receipt
            if (is_null($payResult->getReceiptMarkup()) === FALSE && $payResult->getReceiptMarkup() != '') {
                $dataArray['receipt'] = HTMLContainer::generateMarkup('div', $payResult->getReceiptMarkup());
            }

            // New Invoice
            if (is_null($payResult->getInvoiceNumber()) === FALSE && $payResult->getInvoiceNumber() != '') {
                $dataArray['invoiceNumber'] = $payResult->getInvoiceNumber();
            }
        }

        // divert to credit payment site.
        if (count($creditCheckOut) > 0) {
            return $creditCheckOut;
        }

        $dataArray['success'] = $reply;

        return $dataArray;
    }

    public static function saveHousePayment(\PDO $dbh, $idItem, $ord, $amt, $discount, $addnlCharge, $adjDate, $notes) {

        $uS = Session::getInstance();
        $dataArray = array();

        if ($ord == 0 || ($discount == '' && $addnlCharge == '')) {
            return $dataArray;
        }

        $visit = new Visit($dbh, 0, $ord);
        $amount = floatval($amt);
        $invoice = NULL;

        if ($adjDate != '') {
            $invDate = date('Y-m-d H:i:s', strtotime($adjDate));
        } else {
            $invDate = date('Y-m-d H:i:s');
        }


        if ($idItem == ItemId::Discount) {

            $codes = readGenLookupsPDO($dbh, 'House_Discount');

            if (isset($codes[$discount])) {

                $amount = 0 - $amount;

                $invLine = new OneTimeInvoiceLine();
                $invLine->createNewLine(new Item($dbh, ItemId::Discount, $amount), 1, $codes[$discount][1]);
                $invoice = new Invoice($dbh);

                $invoice->newInvoice(
                    $dbh,
                    0,
                    $uS->subsidyId,
                    $visit->getIdRegistration(),
                    $visit->getIdVisit(),
                    $visit->getSpan(),
                    $notes,
                    $invDate,
                    $uS->username);

                $invoice->addLine($dbh, $invLine, $uS->username);

                // Pay the invoice
                $invoice->updateInvoiceBalance($dbh, $amount, $uS->username);

                $dataArray['reply'] = $codes[$discount][1] . ' Discount Applied.  ';

            } else {
                $dataArray['reply'] = 'Discount code not found: ' . $discount;
            }

        }

        if ($idItem == ItemId::AddnlCharge) {

            $codes = readGenLookupsPDO($dbh, 'Addnl_Charge');

            if (isset($codes[$addnlCharge])) {

                $invLine = new OneTimeInvoiceLine();
                $invLine->createNewLine(new Item($dbh, ItemId::AddnlCharge, $amount), 1, $codes[$addnlCharge][1]);

                if (is_null($invoice)) {
                    $invoice = new Invoice($dbh);

                    $invoice->newInvoice(
                        $dbh,
                        0,
                        $visit->getPrimaryGuestId(),
                        $visit->getIdRegistration(),
                        $visit->getIdVisit(),
                        $visit->getSpan(),
                        $notes,
                        $invDate,
                        $uS->username);

                }

                $invoice->addLine($dbh, $invLine, $uS->username);
                $invoice->updateInvoiceStatus($dbh, $uS->username);

                // Taxed items
                $vat = new ValueAddedTax($dbh);

                foreach ($vat->getCurrentTaxedItems($visit->getIdVisit()) as $t) {

                    if ($t->getIdTaxedItem() == ItemId::AddnlCharge) {
                        $taxInvoiceLine = new TaxInvoiceLine();
                        $taxInvoiceLine->createNewLine(new Item($dbh, $t->getIdTaxingItem(), $amount), $t->getDecimalTax(),  ' ('. $t->getTextPercentTax().')');
                        $taxInvoiceLine->setSourceItemId(ItemId::AddnlCharge);
                        $invoice->addLine($dbh, $taxInvoiceLine, $uS->username);
                    }
                }

                if ($invoice->getAmount() == 0) {

                    // We can pay it now and return a receipt.
                    $paymentManager = new PaymentManager(new PaymentManagerPayment(PayType::Cash));
                    $paymentManager->setInvoice($invoice);
                    $paymentManager->pmp->setPayDate($invDate);
                    $payResult = $paymentManager->makeHousePayment($dbh, '');

                    if (is_null($payResult->getReceiptMarkup()) === FALSE && $payResult->getReceiptMarkup() != '') {
                        $dataArray['receipt'] = HTMLContainer::generateMarkup('div', $payResult->getReceiptMarkup());
                        $dataArray['reply'] = $codes[$addnlCharge][1] . ': item recorded. ';
                    }

                } else {
                    $dataArray['reply'] = $codes[$addnlCharge][1] . ' additional charge is invoiced. ';
                }

            } else {
                $dataArray['reply'] = 'Additional Charge code not found: ' . $addnlCharge;
            }
        }

        return $dataArray;
    }

    public static function processPayments(\PDO $dbh, PaymentManager $paymentManager, $visit, $postbackPage, $idPayor) {

        $uS = Session::getInstance();
        $payResult = NULL;

        if (is_null($paymentManager->pmp)) {
            return $payResult;
        }


        // Payments - setup
        if (is_null($visit) === FALSE) {
            $paymentManager->pmp->setPriceModel(AbstractPriceModel::priceModelFactory($dbh, $uS->RoomPriceModel));
            $paymentManager->pmp->priceModel->setCreditDays($visit->getRateGlideCredit());
            $paymentManager->pmp->setVisitCharges(new VisitCharges($visit->getIdVisit()));
        }


        if ($paymentManager->pmp->getPayType() == PayType::Invoice) {
            $idPayor = $paymentManager->pmp->getIdInvoicePayor();
        }

        // Create Invoice.
        $invoice = $paymentManager->createInvoice($dbh, $visit, $idPayor, $paymentManager->pmp->getInvoiceNotes());

        if (is_null($invoice) === FALSE && $invoice->getStatus() == InvoiceStatus::Unpaid) {

            if ($invoice->getAmountToPay() >= 0) {
                // Make guest payment
                $payResult = $paymentManager->makeHousePayment($dbh, $postbackPage);

            } else if ($invoice->getAmountToPay() < 0) {
                // Make guest return
                $payResult = $paymentManager->makeHouseReturn($dbh, date('Y-m-d H:i:s'));
            }
        }

        return $payResult;

    }

    public static function changeRoomList(\PDO $dbh, $idVisit, $span, $changeDate, $rescId) {

        $dataArray = array();
        $vid = intval($idVisit, 10);
        $spanId = intval($span, 10);

        $vstmt = $dbh->query("Select idReservation, Span_Start, Expected_Departure from visit where idVisit = $vid and Span = $spanId");

        $vRows = $vstmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($vRows) == 1) {

            $now = new \DateTime();
            $now->setTime(10, 0, 0);

            // Expected Departure
            $expDepDT = new \DateTime($vRows[0]['Expected_Departure']);
            $expDepDT->setTime(10, 0, 0);

            if ($expDepDT < $now) {
                $expDepDT = new \DateTime($now->format('Y-m-d H:i:s'));
            }

            // Original Span Start Date
            $spanStartDT = new \DateTime($vRows[0]['Span_Start']);
            $spanStartDT->setTime(10, 0, 0);

            // CHange rooms start date
            $changeDT = new \DateTime($changeDate);
            $changeDT->setTime(10, 0, 0);

            // Cannot change rooms before the span start date.
            if ($changeDT < $spanStartDT) {
                $changeDT = $spanStartDT;
            }

            // Cannot change rooms after today
            if ($changeDT > $now) {
                $changeDT = $now;
            }

            $reserv = Reservation_1::instantiateFromIdReserv($dbh, $vRows[0]['idReservation'], $vid);

            $roomChooser = new RoomChooser($dbh, $reserv, 0, $changeDT, $expDepDT);

            $dataArray['sel'] = $roomChooser->createChangeRoomsSelector($dbh, TRUE);
            $dataArray['resc'] = $roomChooser->makeRoomsArray();
            $dataArray['idResc'] = $rescId;

        } else {
            // error
            $dataArray['error'] = 'Visit id is missing.  ';
        }

        return $dataArray;
    }

    public static function undoRoomChange(\PDO $dbh, Visit $visit, $uname) {

        // Reservation
        $resv = Reservation_1::instantiateFromIdReserv($dbh, $visit->getReservationId(), $visit->getIdVisit());

        if ($resv->isNew() === TRUE) {
            return '';
        }

        // Get next visit
        $nextVisitRs = new VisitRS();
        $nextVisitRs->idVisit->setStoredVal($visit->getIdVisit());
        $nextVisitRs->Span->setStoredVal($visit->getSpan() + 1);
        $vRows = EditRS::select($dbh, $nextVisitRs, array($nextVisitRs->idVisit, $nextVisitRs->Span));

        if (count($vRows) != 1) {
            return 'Next Visit Span not found.  ';
        }

        EditRS::loadRow($vRows[0], $nextVisitRs);

        // Next visit must be the last.
        if ($nextVisitRs->Status->getStoredVal() != VisitStatus::CheckedIn && $nextVisitRs->Status->getStoredVal() != VisitStatus::CheckedOut) {
            return 'Next Visit Span must be Checked-in or Checked-out.  ';
        }

        // Dates
        if ($nextVisitRs->Span_End->getStoredVal() == '') {

            $expDepDT = new \DateTime($nextVisitRs->Expected_Departure->getStoredVal());
            $now = new \DateTime();

            if ($now > $expDepDT) {
                $expDepDT = $now;
            }

        } else {
            $expDepDT = new \DateTime($nextVisitRs->Span_End->getStoredVal());
        }

        $startDt = date('Y-m-d 23:59:59', strtotime($visit->getSpanStart()));


        // Number of occupants

        // Load ALL of next visit's stays
        $nextStays = Visit::loadStaysStatic($dbh, $nextVisitRs->idVisit->getStoredVal(), $nextVisitRs->Span->getStoredVal(), VisitStatus::CheckedIn);

        // Load this visit's stays that were still checked in.
        $stays = Visit::loadStaysStatic($dbh, $visit->getIdVisit(), $visit->getSpan(), VisitStatus::NewSpan);

        $numOccupants = max( array(count($nextStays), count($visit->stays)) );

        // Check room availability
        if ($resv->isResourceOpen($dbh, $visit->getidResource(), $startDt, $expDepDT->format('Y-m-d 01:00:00'), $numOccupants, array('room', 'rmtroom', 'part'), TRUE, TRUE) === FALSE) {
            return 'The room is unavailable. ';
        }

        // Get new room cleaning status to copy to original room
        $resc = AbstractResource::getResourceObj($dbh, $nextVisitRs->idResource->getStoredVal());
        $rooms = $resc->getRooms();
        $room = array_shift($rooms);
        $roomStatus = $room->getStatus();

        // Undo visit checkout
        $visit->visitRS->Actual_Departure->setNewVal($nextVisitRs->Actual_Departure->getStoredVal());
        $visit->visitRS->Span_End->setNewVal($nextVisitRs->Span_End->getStoredVal());
        $visit->visitRS->Expected_Departure->setNewVal($expDepDT->format('Y-m-d 10:00:00'));
        $visit->visitRS->Status->setNewVal($nextVisitRs->Status->getStoredVal());

        $updateCounter = $visit->updateVisitRecord($dbh, $uname);

        if ($updateCounter != 1) {
            throw new RuntimeException('Visit table update failed. Checkout is not undone.');
        }

        // update Reservation
        $resv->setIdResource($visit->getidResource());
        $resv->saveReservation($dbh, $resv->getIdRegistration(), $uname);

        // remove the next visit
        EditRS::delete($dbh, $nextVisitRs, array($nextVisitRs->idVisit, $nextVisitRs->Span));
        $logDelText = VisitLog::getDeleteText($nextVisitRs, $nextVisitRs->idVisit->getStoredVal());
        VisitLog::logVisit($dbh, $nextVisitRs->idVisit->getStoredVal(), $nextVisitRs->Span->getStoredVal(), $nextVisitRs->idResource->getStoredVal(), $nextVisitRs->idRegistration->getStoredVal(), $logDelText, "delete", $uname);


        // original stays with status = changeRoom.
        $visit->loadStays($dbh, VisitStatus::NewSpan);
        foreach ($visit->stays as $s) {

            // Check status in next stay
            foreach ($nextStays as $ns) {

                if ($s->idName->getStoredVal() == $ns->idName->getStoredVal()
                        && date('Y-m-d', strtotime($s->Span_End_Date->getStoredVal())) == date('Y-m-d', strtotime($ns->Span_Start_Date->getStoredVal()))) {

                    $s->Checkout_Date->setNewVal($ns->Checkout_Date->getStoredVal());
                    $s->Span_End_Date->setNewVal($ns->Span_End_Date->getStoredVal());
                    $s->Status->setNewVal($ns->Status->getStoredVal());
                    $s->Expected_Co_Date->setNewVal($ns->Expected_Co_Date->getStoredVal());
                    $s->Last_Updated->setNewVal(date('Y-m-d H:i:s'));
                    $s->Updated_By->setNewVal($uname);

                    $cnt = EditRS::update($dbh, $s, array($s->idStays));
                    if ($cnt > 0) {
                        $logText = VisitLog::getUpdateText($s);
                        EditRS::updateStoredVals($s);
                        VisitLog::logStay($dbh, $s->idVisit->getStoredVal(), $s->Visit_Span->getStoredVal(), $s->idRoom->getStoredVal(), $s->idStays->getStoredVal(), $s->idName->getStoredVal(), $visit->getIdRegistration(), $logText, "update", $uname);
                    }

                    break;
                }
            }
        }


        // Next stays that are not in original stays
        foreach ($nextStays as $ns) {

            // remove the known stays
            foreach ($stays as $s) {

                if ($s->idName->getStoredVal() == $ns->idName->getStoredVal()
                        && date('Y-m-d', strtotime($s->Span_End_Date->getStoredVal())) == date('Y-m-d', strtotime($ns->Span_Start_Date->getStoredVal()))) {

                    EditRS::delete($dbh, $ns, array($ns->idStays));
                    continue 2;
                }
            }

            // just change the span.
            $ns->Visit_Span->setNewVal($visit->getSpan());

            $ns->Last_Updated->setNewVal(date('Y-m-d H:i:s'));
            $ns->Updated_By->setNewVal($uname);

            $cnt = EditRS::update($dbh, $ns, array($ns->idStays));
            if ($cnt > 0) {
                $logText = VisitLog::getUpdateText($ns);
                EditRS::updateStoredVals($ns);
                VisitLog::logStay($dbh, $ns->idVisit->getStoredVal(), $ns->Visit_Span->getStoredVal(), $ns->idRoom->getStoredVal(), $ns->idStays->getStoredVal(), $ns->idName->getStoredVal(), $visit->getIdRegistration(), $logText, "update", $uname);
            }

        }

        // Update room cleaning status of this room
        $resc2 = AbstractResource::getResourceObj($dbh, $visit->getidResource());
        $rooms2 = $resc2->getRooms();
        $room2 = array_shift($rooms2);
        $room2->setStatus($roomStatus);
        $room2->saveRoom($dbh, $uname, TRUE, $roomStatus);

        // Update invoices
        $dbh->exec("Update invoice set Suborder_Number = " . $visit->getSpan() . " where Order_Number = " . $visit->getIdVisit() . " and Suborder_Number != ". $visit->getSpan());

        return "Change Rooms is undone.  ";

    }

    public static function undoCheckout(\PDO $dbh, Visit $visit, \DateTime $newExpectedDT, $uname) {

        $reply = '';
        $uS = Session::getInstance();

        if ($visit->getVisitStatus() != VisitStatus::CheckedOut) {
            return 'Cannot undo checkout, visit continues in another room or at another rate.  ';
        }

        $actDeptDT = new \DateTime($visit->getActualDeparture());

        $resv = Reservation_1::instantiateFromIdReserv($dbh, $visit->getReservationId());

        $startDT = new \DateTime($visit->getSpanStart());
        $startDT->setTime(23, 59, 59);


        // Check room availability
//        $availResc = $resv->isResourceOpen($dbh, $visit->getidResource(), $startDT->format('Y-m-d H:i:s'), $newExpectedDT->format('Y-m-d 01:00:00'), 1, array('room', 'rmtroom', 'part'), TRUE, TRUE);
//
//        if ($availResc === FALSE) {
//            $reply .= 'Cannot undo checkout, the room is not available.  ';
//            return $reply;
//        }
//
//        $idPsg = $resv->getIdPsg($dbh);
//
//        // Check for pending reservations
//        $resvs = ReservationSvcs::getCurrentReservations($dbh, $resv->getIdReservation(), 0, $idPsg, $startDT, $newExpectedDT);
//
//        //if (count($resvs) >= $uS->RoomsPerPatient)
//        $roomsUsed = array($visit->getidResource() => 'y');  // this room
//
//        foreach ($resvs as $rv) {
//
//            // another concurrent reservation already there
//            if ($rv['idPsg'] == $idPsg) {
//                $roomsUsed[$rv['idResource']] = 'y';
//            }
//        }
//
//        if (count($roomsUsed) > $uS->RoomsPerPatient) {
//            return ('Cannot undo the checkout, the maximum rooms per patient would be exceeded.');
//        }

        // Undo reservation termination
        $resv->setActualDeparture('');
        $resv->setExpectedDeparture($newExpectedDT->format('Y-m-d '. $uS->CheckOutTime . ':00:00'));
        $resv->setStatus(ReservationStatus::Staying);

        $resv->saveReservation($dbh, $resv->getIdRegistration(), $uname);


        // Undo visit checkout
        $visit->visitRS->Actual_Departure->setNewVal('');
        $visit->visitRS->Span_End->setNewVal('');
        $visit->visitRS->Expected_Departure->setNewVal($newExpectedDT->format('Y-m-d '. $uS->CheckOutTime . ':00:00'));
        $visit->visitRS->Status->setNewVal(VisitStatus::CheckedIn);
        $visit->visitRS->Last_Updated->setNewVal(date('Y-m-d H:i:s'));
        $visit->visitRS->Updated_By->setNewVal($uname);

        $updateCounter = EditRS::update($dbh, $visit->visitRS, array($visit->visitRS->idVisit, $visit->visitRS->Span));

        if ($updateCounter != 1) {
            throw new RuntimeException('Visit table update failed. Checkout is not undone.');
        }

        $logText = VisitLog::getUpdateText($visit->visitRS);
        EditRS::updateStoredVals($visit->visitRS);
        VisitLog::logVisit($dbh, $visit->getIdVisit(), $visit->visitRS->Span->getStoredVal(), $visit->visitRS->idResource->getStoredVal(), $visit->visitRS->idRegistration->getStoredVal(), $logText, "update", $uname);
        $reply .= "Checkout is undone.  ";

        // Update stays
        $visit->loadStays($dbh, VisitStatus::CheckedOut);
        foreach ($visit->stays as $s) {

            if (date('Y-m-d', strtotime($s->Span_End_Date->getStoredVal())) == $actDeptDT->format('Y-m-d')) {

                // Undo stay checkout
                $s->Checkout_Date->setNewVal('');
                $s->Span_End_Date->setNewVal('');
                $s->Status->setNewVal(VisitStatus::CheckedIn);
                $s->Expected_Co_Date->setNewVal($newExpectedDT->format('Y-m-d '. $uS->CheckOutTime . ':00:00'));

                // cancel on-leave
                $s->On_Leave->setNewVal(0);

                $s->Last_Updated->setNewVal(date('Y-m-d H:i:s'));
                $s->Updated_By->setNewVal($uname);

                $cnt = EditRS::update($dbh, $s, array($s->idStays, $s->Visit_Span));
                if ($cnt > 0) {
                    $logText = VisitLog::getUpdateText($s);
                    EditRS::updateStoredVals($s);
                    VisitLog::logStay($dbh, $s->idVisit->getStoredVal(), $s->Visit_Span->getStoredVal(), $s->idRoom->getStoredVal(), $s->idStays->getStoredVal(), $s->idName->getStoredVal(), $visit->getIdRegistration(), $logText, "update", $uname);
                }
            }
        }


        // Update room cleaning status of this room
        $resc2 = AbstractResource::getResourceObj($dbh, $visit->getidResource());
        $rooms2 = $resc2->getRooms();
        $room2 = array_shift($rooms2);
        $room2->setStatus(RoomState::Clean);
        $room2->saveRoom($dbh, $uname, TRUE, RoomState::Clean);

        return $reply;
    }

    public static function addVisitStay(\PDO $dbh, $idVisit, $visitSpan, $idGuest, $post) {

        $uS = Session::getInstance();
        $dataArray = array();
        $prefix = 'q';

        if ($idVisit < 1 || $visitSpan < 0) {
            return array("error" => "Visit not selected.  ");
        }

        $visitRs = new VisitRs();
        $visitRs->idVisit->setStoredVal($idVisit);
        $visitRs->Span->setStoredVal($visitSpan);

        $visits = EditRS::select($dbh, $visitRs, array($visitRs->idVisit, $visitRs->Span));

        if (count($visits) != 1) {
            return array("error" => "Visit not found.  ");
        }

        EditRS::loadRow($visits[0], $visitRs);

        if ($visitRs->Status->getStoredVal() == VisitStatus::CheckedIn) {
            return array("error" => "Cannot add guest here.  ");
        }

        $guest = new Guest($dbh, $prefix, $idGuest);


        // Arrival Date
        $spanArrDate = new \DateTime($visitRs->Span_Start->getStoredVal());

        // Departure Date
        if ($visitRs->Span_End->getStoredVal() != '') {
            $spanDepDate = new \DateTime($visitRs->Span_End->getStoredVal());
        } else {
            return array("error" => "End date missing.  ");
        }

        if ($spanArrDate >= $spanDepDate) {
            return array("error" => "Visit Dates not suitable.  arrive: " . $spanArrDate->format('Y-m-d H:i:s') . ", depart: " . $spanDepDate->format('Y-m-d H:i:s'));
        }

        $reg = new Registration($dbh, 0, $visitRs->idRegistration->getStoredVal());
        $psg = new PSG($dbh, $reg->getIdPsg());

        //Decide what to send back
        if (isset($post[$prefix.'txtLastName'])) {

            // Get labels
            $labels = Labels::getLabels();

            // save the guest
            $guest->save($dbh, $post, $uS->username);
            $nameObj = $guest->getRoleMember();

            // Attach to PSG if not
            if (isset($psg->psgMembers[$guest->getIdName()]) === FALSE) {
                $psg->setNewMember($guest->getIdName(), $guest->getPatientRelationshipCode());
                $psg->savePSG($dbh, $psg->getIdPatient(), $uS->username);
            }

            // Get the resource
            $resource = null;
            if ($visitRs->idResource->getStoredVal() > 0) {
                $resource = AbstractResource::getResourceObj($dbh, $visitRs->idResource->getStoredVal());
            } else {
                return array('error' => 'Room not found.  ');
            }

            // Verify dates
            $ckinDT = $guest->getCheckinDT();
            $ckinDate = $ckinDT->format('Y-m-d H:m:s');
            $ckinDT->setTime(0,0,0);

            $ckoutDT = $guest->getExpectedCheckOutDT();
            $ckoutDT->setTime(0,0,0);

            if ($ckinDT < $spanArrDate || $ckinDT > $spanDepDate) {
                $ckinDT = $spanArrDate;
            }

            if ($ckoutDT <= $ckinDT || $ckoutDT > $spanDepDate) {
                $ckoutDT = $spanDepDate;
            }


            // get stays
            $staysRs = new StaysRS();

            $staysRs->idVisit->setStoredVal($idVisit);
            $staysRs->Visit_Span->setStoredVal($visitSpan);
            $existingStays = EditRS::select($dbh, $staysRs, array($staysRs->idVisit, $staysRs->Visit_Span));

            $rooms = $resource->getRooms();
            $numGuests = 0;

            foreach ($existingStays as $s) {
                $sRs = new StaysRS();
                EditRS::loadRow($s, $sRs);

                // Only count rooms assigned to the resoource of the visit.
                if (array_key_exists($sRs->idRoom->getStoredVal(), $rooms)) {

                    // Only during the dates of the new stay
                    $stayStrt = new \DateTime($sRs->Span_Start_Date->getStoredVal());
                    $stayStrt->setTime(0,0,0);

                    if ($sRs->Span_End_Date->getStoredVal() != '') {
                        $stayEnd = new \DateTime($sRs->Span_End_Date->getStoredVal());
                    } else {
                        $stayEnd = new \DateTime($sRs->Expected_Co_Date->getStoredVal());
                        $today = new \DateTime();
                        if ($stayEnd < $today) {
                            $stayEnd = $today;
                        }
                    }

                    $stayEnd->setTime(0, 0, 0);

                    if ($ckinDT < $stayEnd && $ckoutDT > $stayStrt) {
                        // This person is staying.

                        if ($guest->getIdName() == $sRs->idName->getStoredVal()) {
                            return array('error' => $nameObj->get_fullName() . ' is already staying during a part of the indicated check-in and check-out dates.  ');
                        }

                        $numGuests++;
                    }
                }
            }

            if ($numGuests >= $resource->getMaxOccupants()) {
                return array("error" => "Room is full during a part of the indicated check-in and check-out dates.  ");
            }


            $ckoutDate = $ckoutDT->format('Y-m-d 10:00:00');
            $room = reset($rooms);

            // is the guest somewhere else in the house?
            $stmt = $dbh->query("Select count(idName) from stays "
                    . "where idName = " . $guest->getIdName() . " and DATEDIFF(ifnull(Span_End_Date, Expected_Co_Date), Span_Start_Date) != 0 "
                    . " and DATE('" . $ckinDT->format('Y-m-d H:m:s') . "') < ifnull(DATE(Span_End_Date), DATE(Expected_Co_Date)) and DATE('$ckoutDate') > DATE(Span_Start_Date)");
            $rows = $stmt->fetchAll(\PDO::FETCH_NUM);

            if (isset($rows[0][0]) && $rows[0][0] > 0) {
                return array('error' => $nameObj->get_fullName() . ' is already included in a different visit.  ');
            }

            // Add the stay
            $stayRS = new StaysRS();

            $stayRS->idName->setNewVal($guest->getIdName());
            $stayRS->idRoom->setNewVal($room->getIdRoom());
            $stayRS->Checkin_Date->setNewVal($ckinDate);
            $stayRS->Expected_Co_Date->setNewVal($ckoutDate);
            $stayRS->Span_Start_Date->setNewVal($ckinDate);

            if ($visitRs->Status->getStoredVal() != VisitStatus::CheckedIn) {

                $stayRS->Checkout_Date->setNewVal($ckoutDate);
                $stayRS->Span_End_Date->setNewVal($ckoutDate);
            }

            $stayRS->Status->setNewVal($visitRs->Status->getStoredVal());
            $stayRS->idVisit->setNewVal($idVisit);
            $stayRS->Visit_Span->setNewVal($visitSpan);
            $stayRS->Updated_By->setNewVal($uS->username);
            $stayRS->Last_Updated->setNewVal(date("Y-m-d H:i:s"));

            $idStays = EditRS::insert($dbh, $stayRS);
            $stayRS->idStays->setNewVal($idStays);

            $logText = VisitLog::getInsertText($stayRS);
            VisitLog::logStay($dbh, $idVisit, $visitSpan, $stayRS->idRoom->getNewVal(), $idStays, $guest->getIdName(), $visitRs->idRegistration->getStoredVal(), $logText, "insert", $uS->username);

            $dataArray['stays'] = VisitViewer::createStaysMarkup($dbh, $idVisit, $visitSpan, $visitRs->idPrimaryGuest->getStoredVal(), FALSE, $guest->getIdName(), $labels);

        } else {
            // send back a guest dialog to collect name, address, etc.

            $guest->setCheckinDate($spanArrDate->format('M j, Y'));
            $guest->setExpectedCheckOut($spanDepDate->format('M j, Y'));

            if (isset($psg->psgMembers[$guest->getIdName()])) {
                $guest->setPatientRelationshipCode($psg->psgMembers[$guest->getIdName()]->Relationship_Code->getStoredVal());
            }

            $dataArray['addtguest'] = $guest->createMarkup($dbh);
            $dataArray['addr'] = self::createAddrObj($dbh, $visitRs->idPrimaryGuest->getStoredVal());
        }

        return $dataArray;
    }

    public static function createAddrObj(\PDO $dbh, $idName) {

        $guest = new Guest($dbh, '', $idName);
        $addrObj = $guest->getAddrObj();
        $addr = $addrObj->get_Data(AddressPurpose::Home);

        $adrArray = array(
            'adraddress1' => $addr['Address_1'],
            'adraddress2' => $addr['Address_2'],
            'adrcity' => $addr['City'],
            'adrcounty' => $addr['County'],
            'adrstate' => $addr['State_Province'],
            'adrcountry' => $addr['Country_Code'],
            'adrzip' => $addr['Postal_Code']
        );

        return $adrArray;
    }

    // Just credit cards with delete checkboxes.
    public static function guestEditCreditTable(\PDO $dbh, $idRegistration, $idGuest, $index, $defaultMerchant = '') {

        $uS = Session::getInstance();

        $tbl = new HTMLTable();

        $tkRsArray = CreditToken::getRegTokenRSs($dbh, $idRegistration, '', $idGuest);

        $tbl->addBodyTr(HTMLTable::makeTh("X") . HTMLTable::makeTh("Card on File") . HTMLTable::makeTh("Name"));

        // List any valid stored cards on file
        foreach ($tkRsArray as $tkRs) {

            $merchant = ' (' . ucfirst($tkRs->Merchant->getStoredVal()) . ')';
            if (strtolower($tkRs->Merchant->getStoredVal()) == 'local' || $tkRs->Merchant->getStoredVal() == '' || strtolower($tkRs->Merchant->getStoredVal()) == 'production') {
                $merchant = '';
            }

            $tbl->addBodyTr(
                    HTMLTable::makeTd(HTMLInput::generateMarkup('', array('type'=>'checkbox', 'name'=>'cbDelCard'.$index. '_'.$tkRs->idGuest_token->getStoredVal())))
                    . HTMLTable::makeTd($tkRs->CardType->getStoredVal() . ' - ' . $tkRs->MaskedAccount->getStoredVal() . $merchant)
                    . HTMLTable::makeTd($tkRs->CardHolderName->getStoredVal())
                );

        }

        // New card.
        $gateway = AbstractPaymentGateway::factory($dbh, $uS->PaymentGateway, AbstractPaymentGateway::getCreditGatewayNames($dbh, 0, 0, 0));
        
        if ($gateway->hasCofService()) {
        	
        	$attr = array('type'=>'checkbox', 'name'=>'rbUseCard'.$index);
        	if (count($tkRsArray) == 0) {
	        	$attr['checked'] = 'checked';
	        } else {
	        	unset($attr['checked']);
	        }
	
	        $tbl->addBodyTr(HTMLTable::makeTd(HTMLContainer::generateMarkup('label', 'New', array('for'=>'rbUseCard'.$index, 'style'=>'margin-right: .5em;'))
	        		.  HTMLInput::generateMarkup('', $attr), array('style'=>'text-align:right;', 'colspan'=> '3'))
	        );
	
	        $tbl->addBodyTr( HTMLTable::makeTd('', array('id'=>'tdChargeMsg' . $index, 'colspan'=>'4', 'style'=>'color:red; display:none;')));
	
	        $gateway->setCheckManualEntryCheckbox(TRUE);
	
	        $gwTbl = new HTMLTable();
	        $gateway->selectPaymentMarkup($dbh, $gwTbl, $index, $defaultMerchant);
	        $tbl->addBodyTr(HTMLTable::makeTd($gwTbl->generateMarkup(array('style'=>'width:100%;')), array('colspan'=>'4', 'style'=>'padding:0;')));
        }
        
        return $tbl->generateMarkup(array('id' => 'tblupCredit'.$index, 'class'=>'igrs'));

    }

    /**
     * Return from View Credit Table; delete any indicated cards and send out COF transaction to Gateway
     *
     * @param \PDO $dbh
     * @param integer $idGuest
     * @param integer $idGroup
     * @param array $post
     * @param string $postBackPage
     * @return array
     */
    public static function cardOnFile(\PDO $dbh, $idGuest, $idGroup, $post, $postBackPage, $idx) {
        // Credit card processing
        $uS = Session::getInstance();

        $dataArray = array();

        $keys = array_keys($post);
        $msg = '';


        // Delete any credit tokens
        foreach ($keys as $k) {

            $parts = explode('_', $k);

            if (count($parts) > 1 && $parts[0] == 'cbDelCard'.$idx) {

                $idGt = intval(filter_var($parts[1], FILTER_SANITIZE_NUMBER_INT), 10);

                if ($idGt > 0) {

                    $cnt = $dbh->exec("update guest_token set Token = '' where idGuest_token = " . $idGt);

                    if ($cnt > 0) {
                        $gtRs = CreditToken::getTokenRsFromId($dbh, $idGt);
                        $msg .= 'Card ' . $gtRs->MaskedAccount->getStoredVal() . ', Name ' . $gtRs->CardHolderName->getStoredVal() . ' deleted.  ';
                    }
                }
            }
        }

        $dataArray['success'] = $msg;

        // Add a new card
        if (isset($post['rbUseCard'.$idx])) {

            $manualKey = FALSE;
            $selGw = '';
            $cardType = '';
            $chargeAcct = '';

            $guest = new Guest($dbh, '', $idGuest);
            $newCardHolderName = $guest->getRoleMember()->get_fullName();


            if (isset($post['btnvrKeyNumber'.$idx])) {
            	$manualKey = TRUE;
            }
            
            if (isset($post['txtvdNewCardName'.$idx]) && $post['txtvdNewCardName'.$idx] != '') {
            	$newCardHolderName = strtoupper(filter_var($post['txtvdNewCardName'.$idx], FILTER_SANITIZE_STRING));
            }
            
            // For mulitple merchants
            if (isset($post['selccgw'.$idx])) {
            	$selGw = strtolower(filter_var($post['selccgw'.$idx], FILTER_SANITIZE_STRING));
            }
            
            if (isset($post['selChargeType'.$idx])) {
            	$cardType = filter_var($post['selChargeType'.$idx], FILTER_SANITIZE_STRING);
            }
            
            if (isset($post['txtChargeAcct'.$idx])) {
            	
            	$chargeAcct = filter_var($post['txtChargeAcct'.$idx], FILTER_SANITIZE_STRING);
            	
            	if (strlen($chargeAcct) > 4) {
            		$chargeAcct = substr($chargeAcct, -4, 4);
            	}

            }
            
            try {

                $gateway = AbstractPaymentGateway::factory($dbh, $uS->PaymentGateway, $selGw);
                
                if ($gateway->hasCofService()) {
                	$dataArray = $gateway->initCardOnFile($dbh, $uS->siteName, $idGuest, $idGroup, $manualKey, $newCardHolderName, $postBackPage, $cardType, $chargeAcct, $idx);
                }

            } catch (PaymentException $ex) {

                $dataArray['error'] = $ex->getMessage();
            }
        } else {
            // return new form
            $dataArray['COFmkup'] = self::guestEditCreditTable($dbh, $idGroup, $idGuest, $idx);
        }

        return $dataArray;
    }

    public static function changeExpectedDepartureDate(\PDO $dbh, $idGuest, $idVisit, $newDate) {

        if ($newDate == '' || $idGuest < 1 || $idVisit < 1) {
            return array('error' => 'Parameters not specified.   ');
        }

        $visit = new Visit($dbh, 0, $idVisit);
        $guestDates[$idGuest] = $newDate;
        $uS = Session::getInstance();

        $result = $visit->changeExpectedCheckoutDates($dbh, $guestDates, $uS->MaxExpected, $uS->username);

        return array('success' => $result['message'], 'isChanged' => $result['isChanged']);
    }

    /** Move a visit temporally by so many days
     *
     * @param \PDO $dbh
     * @param int $idVisit
     * @param int $dayDelta
     * @return array
     */
    public static function moveVisit(\PDO $dbh, $idVisit, $span, $startDelta, $endDelta) {

        $uS = Session::getInstance();
        $dataArray = array();

        if ($idVisit == 0) {
            return array("error" => "Visit not specified.");
        }

        if (SecurityComponent::is_Authorized('guestadmin') === FALSE) {
            return array("error" => "User not authorized to move visits.");
        }

        // save the visit info
        $reply = VisitViewer::moveVisit($dbh, $idVisit, $span, $startDelta, $endDelta, $uS->username);

        if ($reply === FALSE) {

            $reply = 'Warning:  Visit not moved.';

        } else {

            // Return checked in guests markup?
            $dataArray['curres'] = 'y';
        }

        $dataArray['success'] = $reply;

        return $dataArray;
    }

    public static function visitChangeLogMarkup(\PDO $dbh, $idReg) {

        $lTable = new HTMLTable();
        if ($idReg > 0) {
            $visLogRS = new Visit_LogRS();
            $visLogRS->idRegistration->setStoredVal($idReg);
            $rows = EditRS::select($dbh, $visLogRS, array($visLogRS->idRegistration), 'and', array($visLogRS->Timestamp), FALSE);
            $cnt = 0;
            foreach ($rows as $r) {
                $vlRS = new Visit_LogRS();
                EditRS::loadRow($r, $vlRS);
                // only show my id or table visit.
                if ($vlRS->Log_Type->getStoredVal() == 'visit') {

                    $lTable->addBodyTr(
                            HTMLTable::makeTd(date('m/d/Y H:i:s', strtotime($vlRS->Timestamp->getStoredVal())))
                            . HTMLTable::makeTd($vlRS->Sub_Type->getStoredVal())
                            . HTMLTable::makeTd($vlRS->User_Name->getStoredVal())
                            . HTMLTable::makeTd($vlRS->idVisit->getStoredVal())
                            . HTMLTable::makeTd($vlRS->Span->getStoredVal())
                            . HTMLTable::makeTd($vlRS->idRr->getStoredVal())
                            . HTMLTable::makeTd(VisitLog::parseLogText($vlRS->Log_Text->getStoredVal()))
                    );
                    if ($cnt++ == 47) {
                        break;
                    }
                }
            }
        }

        $lTable->addHeaderTr(
                HTMLTable::makeTh('Date')
                . HTMLTable::makeTh('Operation')
                . HTMLTable::makeTh('User')
                . HTMLTable::makeTh('Visit Id')
                . HTMLTable::makeTh('Span')
                . HTMLTable::makeTh('Resource Id')
                . HTMLTable::makeTh('Message'));

        return array('vlog' => $lTable->generateMarkup());
    }

}
