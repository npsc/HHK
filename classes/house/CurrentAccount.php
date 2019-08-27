<?php

/*
 * The MIT License
 *
 * Copyright 2019 Eric.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


class CurrentAccount {

    protected $numberNitesStayed;
    protected $addnlGuestNites;
    protected $visitGlideCredit;
    protected $visitStatus;
    protected $showRoomFees;
    protected $showGuestNites;
    protected $showVisitFee;

    // Charges.
    protected $lodgingTax;
    protected $additionalChargeTax;

    protected $roomCharge;
    protected $totalDiscounts;
    protected $visitFeeCharged;
    protected $additionalCharge;
    protected $unpaidMOA;

    // Visit Fee Balance
    protected $vfeeBal;

    // Room fee balance
    protected $roomFeeBalance;

    // Payments
    protected $totalPaid;

    // Pending amounts
    protected $amtPending;

    protected $dueToday;

    public function __construct($visitStatus, $showVisitFee = FALSE, $showRoomFees = FALSE, $showGuestNights = FALSE) {


        $this->visitStatus = $visitStatus;
        $this->showRoomFees = $showRoomFees;
        $this->showGuestNites = $showGuestNights;
        $this->showVisitFee = $showVisitFee;
    }

    public function load(VisitCharges $visitCharge, $taxedItems) {

        $this->numberNitesStayed = $visitCharge->getNightsStayed();

        $this->setAddnlGuestNites($visitCharge->getGuestNightsStayed() - $visitCharge->getNightsStayed());
        $this->setVisitGlideCredit($visitCharge->getGlideCredit());

        // Charges.
        $this->setRoomCharge($visitCharge->getRoomFeesCharged());
        $this->setTotalDiscounts($visitCharge->getItemInvCharges(ItemId::Discount) + $visitCharge->getItemInvCharges(ItemId::Waive));
        $this->setVisitFeeCharged($visitCharge->getVisitFeeCharged());
        $this->setAdditionalCharge($visitCharge->getItemInvCharges(ItemId::AddnlCharge));
        $this->setUnpaidMOA($visitCharge->getItemInvPending(ItemId::LodgingMOA));

        // Lodging taxes
        if (isset($taxedItems[ItemId::Lodging])) {
            $this->setLodgingTax(round(($this->getRoomCharge() + $this->getTotalDiscounts()) * $taxedItems[ItemId::Lodging] / 100, 2));
        } else {
            $this->setLodgingTax(0);
        }

        // Additional Charge taxes?
        if (isset($taxedItems[ItemId::AddnlCharge])) {
            $this->setAdditionalChargeTax(round($this->getAdditionalCharge() * $taxedItems[ItemId::AddnlCharge] / 100, 2));
        } else {
            $this->setAdditionalChargeTax(0);
        }

        // Visit Fee Balance
        if ($this->showVisitFee) {
            $this->setVfeeBal($this->getVisitFeeCharged() - $visitCharge->getVisitFeesPaid() - $visitCharge->getVisitFeesPending());
        } else {
            $this->setVisitFeeCharged(0);
        }

        // Room fee balance
        $this->setRoomFeeBalance(($this->getRoomCharge() + $visitCharge->getItemInvCharges(ItemId::Discount)) - $visitCharge->getRoomFeesPaid() - $visitCharge->getRoomFeesPending());

        // Payments
        $this->setTotalPaid($visitCharge->getRoomFeesPaid()
                + $visitCharge->getVisitFeesPaid()
                + $visitCharge->getItemInvPayments(ItemId::AddnlCharge)
                + $visitCharge->getItemInvPayments(ItemId::Waive)
                + $visitCharge->getItemInvPayments('tax'));

        // Pending amounts
        $this->setAmtPending($visitCharge->getRoomFeesPending()
                + $visitCharge->getVisitFeesPending()
                + $visitCharge->getItemInvPending(ItemId::AddnlCharge)
                + $visitCharge->getItemInvPending(ItemId::LodgingMOA)
                + $visitCharge->getItemInvPending(ItemId::Waive)
                + $visitCharge->getItemInvPending('tax'));

    }

    public function getAddnlGuestNites() {
        return $this->addnlGuestNites;
    }

    public function getVisitGlideCredit() {
        return $this->visitGlideCredit;
    }

    public function getLodgingTax() {
        return round($this->lodgingTax, 2);
    }

    public function getAdditionalChargeTax() {
        return round($this->additionalChargeTax, 2);
    }

    public function getRoomCharge() {
        return $this->roomCharge;
    }

    public function getTotalDiscounts() {
        return $this->totalDiscounts;
    }

    public function getVisitFeeCharged() {
        return $this->visitFeeCharged;
    }

    public function getAdditionalCharge() {
        return $this->additionalCharge;
    }

    public function getUnpaidMOA() {
        return $this->unpaidMOA;
    }

    public function getTotalCharged() {

        return $this->getRoomCharge() + $this->getLodgingTax()
                + $this->getAdditionalCharge() + $this->getAdditionalChargeTax()
                + $this->getUnpaidMOA()
                + $this->getTotalDiscounts()
                + $this->getVisitFeeCharged();
    }

    public function getVfeeBal() {
        return $this->vfeeBal;
    }

    public function getTotalPaid() {
        return $this->totalPaid;
    }

    public function getAmtPending() {
        return $this->amtPending;
    }

    public function getNumberNitesStayed() {
        return $this->numberNitesStayed;
    }

    public function getVisitStatus() {
        return $this->visitStatus;
    }

    public function getShowRoomFees() {
        return $this->showRoomFees;
    }

    public function getShowVisitFee() {
        return $this->showVisitFee;
    }

    public function getShowGuestNites() {
        return $this->showGuestNites;
    }

    public function getRoomFeeBalance() {
        return $this->roomFeeBalance;
    }

    public function getDueToday() {
        return $this->dueToday;
    }

    public function setDueToday() {

        $dueToday = round($this->getTotalCharged() - $this->getTotalPaid() - $this->getAmtPending(), 2);

        if (abs($dueToday) <= .01 && $this->getLodgingTax() > 0) {
            $this->setLodgingTax($this->getLodgingTax() + $dueToday);
            $dueToday = 0;
        }

        $this->dueToday = $dueToday;
        return $this;
    }


    public function setRoomFeeBalance($roomFeeBalance) {
        $this->roomFeeBalance = $roomFeeBalance;
        return $this;
    }

    public function setAddnlGuestNites($addnlGuestNites) {
        $this->addnlGuestNites = $addnlGuestNites;
        return $this;
    }

    public function setVisitGlideCredit($visitGlideCredit) {
        $this->visitGlideCredit = $visitGlideCredit;
        return $this;
    }

    public function setLodgingTax($lodgingTax) {
        $this->lodgingTax = $lodgingTax;
        return $this;
    }

    public function setAdditionalChargeTax($additionalChargeTax) {
        $this->additionalChargeTax = $additionalChargeTax;
        return $this;
    }

    public function setRoomCharge($roomCharge) {
        $this->roomCharge = $roomCharge;
        return $this;
    }

    public function setTotalDiscounts($totalDiscounts) {
        $this->totalDiscounts = $totalDiscounts;
        return $this;
    }

    public function setVisitFeeCharged($visitFeeCharged) {
        $this->visitFeeCharged = $visitFeeCharged;
        return $this;
    }

    public function setAdditionalCharge($additionalCharge) {
        $this->additionalCharge = $additionalCharge;
        return $this;
    }

    public function setUnpaidMOA($unpaidMOA) {
        $this->unpaidMOA = $unpaidMOA;
        return $this;
    }


    public function setVfeeBal($vfeeBal) {
        $this->vfeeBal = $vfeeBal;
        return $this;
    }

    public function setTotalPaid($totalPaid) {
        $this->totalPaid = $totalPaid;
        return $this;
    }

    public function setAmtPending($amtPending) {
        $this->amtPending = $amtPending;
        return $this;
    }


}