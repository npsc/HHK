var gblAdjustData=[];function getApplyDiscDiag(e,t){"use strict";e&&""!=e&&0!=e?$.post("ws_ckin.php",{cmd:"getHPay",ord:e,arrDate:$("#spanvArrDate").text()},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)e.gotopage&&window.location.assign(e.gotopage),flagAlertMessage(e.error,"error");else if(e.markup){t.children().remove();var a={Save:function(){var e=parseFloat($("#housePayment").val().replace("$","").replace(",","")),t=parseFloat($("#houseTax").val()),a=$("#housePayment").data("vid"),i=$.datepicker.formatDate("yy-mm-dd",$("#housePaymentDate").datepicker("getDate")),r=$("#housePaymentNote").val();isNaN(e)&&(e=0),isNaN(t)&&(t=0),saveDiscountPayment(a,$("#cbAdjustPmt1").prop("checked")?$("#cbAdjustPmt1").data("item"):$("#cbAdjustPmt2").data("item"),e,$("#selHouseDisc").val(),$("#selAddnlChg").val(),i,r),$(this).dialog("close")},Cancel:function(){$(this).dialog("close")}};t.append($('<div class="hhk-panel hhk-tdbox hhk-visitdialog" style="font-size:0.8em;"/>').append($(e.markup))),$("#cbAdjustType").buttonset(),$("#cbAdjustPmt1, #cbAdjustPmt2").change(function(){var e=$(this).data("hid"),t=$(this).data("sho");$("."+e).val(""),$("."+t).val(""),$("#housePayment").val(""),$("#housePayment").change(),$("."+t).show(),$("."+e).hide()}),gblAdjustData.disc=e.disc,gblAdjustData.addnl=e.addnl,$("#selAddnlChg, #selHouseDisc").change(function(){var e=parseFloat(gblAdjustData[$(this).data("amts")][$(this).val()]);$("#housePayment").val(e.toFixed(2)),$("#housePayment").change()}),$("#housePayment").change(function(){if($("#cbAdjustPmt2").prop("checked")&&$("#houseTax").length>0){var e=parseFloat($("#houseTax").data("tax")),t=parseFloat($("#housePayment").val().replace("$","").replace(",","")),a=0,i=0;isNaN(e)&&(e=0),isNaN(t)&&(t=0),i=t+(a=e*t),$("#houseTax").val(a>0?a.toFixed(2):""),$("#totalHousePayment").val(i>0?i.toFixed(2):"")}}),$("#cbAdjustPmt1").length>0?($("#cbAdjustPmt1").prop("checked",!0),$("#cbAdjustPmt1").change()):($("#cbAdjustPmt2").prop("checked",!0),$("#cbAdjustPmt2").change()),t.dialog("option","buttons",a),t.dialog("option","title","Adjust Fees"),t.dialog("option","width",430),t.dialog("open")}}}):flagAlertMessage("Order Number is missing","error")}function saveDiscountPayment(e,t,a,i,r,n,s){"use strict";$.post("ws_ckin.php",{cmd:"saveHPay",ord:e,item:t,amt:a,dsc:i,chg:r,adjDate:n,notes:s},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}e.error&&(e.gotopage&&window.location.assign(e.gotopage),flagAlertMessage(e.error,"error")),e.reply&&""!=e.reply&&(flagAlertMessage(e.reply,"success"),$("#keysfees").dialog("close")),e.receipt&&""!==e.receipt&&($("#keysfees").length>0&&$("#keysfees").dialog("close"),showReceipt("#pmtRcpt",e.receipt,"Payment Receipt"))}})}function getInvoicee(e,t,a){"use strict";var i=parseInt(e.id,10);!1===isNaN(i)&&i>0?($("#txtInvName"+a).val(e.value),$("#txtInvId"+a).val(i)):($("#txtInvName"+a).val(""),$("#txtInvId"+a).val("")),$("#txtOrderNum").val(t),$("#txtInvSearch"+a).val("")}function sendVoidReturn(e,t,a,i,r){var n={pid:a,bid:e};t&&"v"===t?n.cmd="void":t&&"rv"===t?n.cmd="revpmt":t&&"r"===t?(n.cmd="rtn",n.amt=i):t&&"ur"===t?(n.cmd="undoRtn",n.amt=i):t&&"vr"===t?n.cmd="voidret":t&&"d"===t&&(n.cmd="delWaive",n.iid=i),$.post("ws_ckin.php",n,function(e){var t="";if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage(e.error,"error");if(e.reversal&&""!==e.reversal&&(t=e.reversal),e.warning)return void flagAlertMessage(t+e.warning,"warning");e.success&&(flagAlertMessage(t+e.success,"success"),r()),e.receipt&&showReceipt("#pmtRcpt",e.receipt,"Receipt")}})}var payCtrls=function(){var e=this;e.keyDepAmt=$("#keyDepAmt"),e.keyDepCb=$("#keyDepRx"),e.depRefundCb=$("#cbDepRefundApply"),e.depRefundAmt=$("#DepRefundAmount"),e.visitFeeAmt=$("#visitFeeAmt"),e.visitFeeCb=$("#visitFeeCb"),e.feePayAmt=$("input#feesPayment"),e.feesCharges=$("#feesCharges"),e.totalPayment=$("#totalPayment"),e.totalCharges=$("#totalCharges"),e.cashTendered=$("#txtCashTendered"),e.invoiceCb=$(".hhk-payInvCb"),e.adjustBtn=$("#paymentAdjust"),e.msg=$("#payChooserMsg"),e.heldAmtTb=$("#heldAmount"),e.heldCb=$("#cbHeld"),e.reimburseVatCb=$("#cbReimburseVAT"),e.reimburseVatAmt=$("#reimburseVat"),e.hsDiscAmt=$("#HsDiscAmount"),e.finalPaymentCb=$("input#cbFinalPayment"),e.overPay=$("#txtOverPayAmt"),e.guestCredit=$("#guestCredit"),e.selBalTo=$("#selexcpay")};function roundTo(e,t){void 0===t&&(t=0);var a=Math.pow(10,t);return e=parseFloat((e*a).toFixed(11)),Math.round(e)/a}function amtPaid(){"use strict";var e,t,a=new payCtrls,i=0,r=0,n=0,s=0,o="",d=0,l=0,h=0,c=0,p=0,u=0,v=0,g=0,m=0,f=0,y=0,b=isCheckedOut,C=parseFloat($("#spnCfBalDue").data("rmbal")),k=$(".hhk-TaxingItem");if(isNaN(C)?C=0:k.each(function(){var e=parseFloat($(this).data("taxrate"));g+=roundTo(C*e,2)}),a.msg.text("").hide(),a.visitFeeCb.length>0&&(r=parseFloat($("#spnvfeeAmt").data("amt")),isNaN(r)||r<0||!1===a.visitFeeCb.prop("checked")?(r=0,a.visitFeeAmt.val("")):a.visitFeeAmt.val(r.toFixed(2).toString())),!b&&a.keyDepCb.length>0&&(i=parseFloat($("#hdnKeyDepAmt").val()),isNaN(i)||i<0||!1===a.keyDepCb.prop("checked")?(i=0,a.keyDepAmt.val("")):(a.keyDepAmt.val(i.toFixed(2).toString()),$(".hhk-kdrow").show())),a.invoiceCb.length>0&&a.invoiceCb.each(function(){var e,t=parseInt($(this).data("invnum")),a=$("#"+t+"invPayAmt"),i=parseFloat($(this).data("invamt"));!0===$(this).prop("checked")?(a.prop("disabled",!1),""===a.val()&&a.val(i.toFixed(2).toString()),e=parseFloat(a.val().replace("$","").replace(",","")),isNaN(e)||0==e?(e=0,a.val("")):Math.abs(e)>Math.abs(i)?(e=i,a.val(e.toFixed(2).toString())):a.val(e.toFixed(2).toString()),d+=e):""!==a.val()&&(a.val(""),a.prop("disabled",!0))}),a.depRefundAmt.length>0&&b&&(v=parseFloat(a.depRefundAmt.data("amt")),isNaN(v)||v<0||!1===a.depRefundCb.prop("checked")?(v=0,a.depRefundAmt.val("")):a.depRefundAmt.val((0-v).toFixed(2).toString())),a.heldCb.length>0&&(l=parseFloat(a.heldCb.data("amt")),(isNaN(l)||l<0||!1===a.heldCb.prop("checked"))&&(l=0)),a.reimburseVatCb.length>0&&(h=parseFloat(a.reimburseVatCb.data("amt")),(isNaN(h)||h<0||!1===a.reimburseVatCb.prop("checked"))&&(h=0)),e=l+v+h,k.each(function(){var t=parseFloat($(this).data("taxrate"));y+=roundTo(e/(1+t),2)}),y>g&&(y=g),t=e-y,a.feePayAmt.length>0&&(o=a.feePayAmt.val().replace("$","").replace(",",""),s=roundTo(parseFloat(o),2),"0.00"===o&&(o="0"),"0"!==o&&(o=""),(isNaN(s)||s<=0)&&(s=0),k.length>0&&(k.each(function(){var e=parseFloat($(this).data("taxrate")),t=roundTo(s*e,2);$(this).val(t.toFixed(2).toString()),m+=t}),m>g-y&&b&&(m=g-y),m<=0&&(m=0)),n=s+m),b){$(".hhk-minPayment").show("fade"),f=0,a.hsDiscAmt.val("");var x=roundTo(C+g,2);if(C>=0?(a.feesCharges.val(x.toFixed(2).toString()),$(".hhk-GuestCredit").hide(),$(".hhk-RoomCharge").show()):(a.guestCredit.val(x.toFixed(2).toString()),$(".hhk-RoomCharge").hide(),$(".hhk-GuestCredit").show(),d>0&&(x+d<=0?(x+=d,d=0):(d=x+d,x=0)),r>0&&(x+r<=0?(x+=r,r=0):(r=x+r,x=0))),(c=r+d+x-e)>=(u=r+d+n)&&c>0){var A=roundTo(r+d+C-t-s,2);if(A>0){if(a.finalPaymentCb.prop("checked")){var P=roundTo(g-(m+y),2);a.hsDiscAmt.val(A.toFixed(2).toString()),a.feesCharges.val((x-P).toFixed(2).toString()),c-=P,u=n}else a.hsDiscAmt.val("");$(".hhk-Overpayment").hide(),$(".hhk-HouseDiscount").show("fade")}else a.finalPaymentCb.prop("checked",!1),a.hsDiscAmt.val(""),$(".hhk-HouseDiscount").hide(),$(".hhk-Overpayment").hide();a.selBalTo.val(""),$("#txtRtnAmount").val(""),$("#divReturnPay").hide()}else a.finalPaymentCb.prop("checked",!1),a.hsDiscAmt.val(""),f=c>=0?u-c:r+n-c,"r"===a.selBalTo.val()?(c>=0?(s>f&&alert("Pay Room Fees amount is reduced to: $"+(s-f).toFixed(2).toString()),n=(s-=f)+m,f=0,a.selBalTo.val(""),$("#txtRtnAmount").val(""),$("#divReturnPay").hide()):(n>0&&alert("Pay Room Fees amount is reduced to: $0.00"),f-=n,s=0,m=0,k.each(function(){$(this).val("")}),n=0,$("#divReturnPay").show("fade"),$("#txtRtnAmount").val(f.toFixed(2).toString())),u=r+d+n):($("#txtRtnAmount").val(""),$("#divReturnPay").hide()),f.toFixed(2)>0?($(".hhk-Overpayment").show("fade"),$(".hhk-HouseDiscount").hide()):($(".hhk-Overpayment").hide(),$(".hhk-HouseDiscount").hide())}else(c=r+i+d+n)>0&&l>0?l>c?(l=c,c=0):c-=l:c<0&&l>0?c-=l:a.heldCb.length>0&&a.heldAmtTb.val(""),c>0&&h>0?h>c?h=0:c-=h:c<0&&h>0?c-=h:a.reimburseVatCb.length>0&&(h=0),$(".hhk-Overpayment").hide(),$(".hhk-HouseDiscount").hide(),a.hsDiscAmt.val(""),f=0,u=c,p=r+i+d+n;u>0||u<0&&!b?($(".paySelectTbl").show("fade"),$(".hhk-minPayment").show("fade"),u<0&&!b&&$("#txtRtnAmount").val((0-u).toFixed(2).toString())):(u=0,$(".paySelectTbl").hide(),$("#divReturnPay").hide(),!1===b&&0===p?($(".hhk-minPayment").hide(),l=0,h=0):$(".hhk-minPayment").show("fade")),0===n?a.feePayAmt.val(o):a.feePayAmt.val(s.toFixed(2).toString()),0==f.toFixed(2)?a.overPay.val(""):a.overPay.val(f.toFixed(2).toString()),l.toFixed(2)>0?a.heldAmtTb.val((0-l).toFixed(2).toString()):a.heldAmtTb.val(""),h>0?a.reimburseVatAmt.val((0-h).toFixed(2).toString()):a.reimburseVatAmt.val(""),a.totalCharges.val(c.toFixed(2).toString()),a.totalPayment.val(u.toFixed(2).toString()),$("#spnPayAmount").text("$"+u.toFixed(2).toString()),a.cashTendered.change()}function setupPayments(e,t,a,i){"use strict";var r=$("#PayTypeSel"),n=$(".tblCredit"),s=$("#trvdCHName"),o=new payCtrls;0===n.length&&(n=$(".hhk-mcred")),r.length>0&&(r.change(function(){$(".hhk-cashTndrd").hide(),$(".hhk-cknum").hide(),$("#tblInvoice").hide(),$(".hhk-transfer").hide(),$(".hhk-tfnum").hide(),n.hide(),s.hide(),$("#tdCashMsg").hide(),$(".paySelectNotes").show(),"cc"===$(this).val()?(n.show("fade"),0==$("input[name=rbUseCard]:checked").val()&&s.show()):"ck"===$(this).val()?$(".hhk-cknum").show("fade"):"in"===$(this).val()?($("#tblInvoice").show("fade"),$(".paySelectNotes").hide()):"tf"===$(this).val()?$(".hhk-transfer").show("fade"):$(".hhk-cashTndrd").show("fade")}),r.change()),setupCOF(s);var d=$("#rtnTypeSel"),l=$(".tblCreditr");0===l.length&&(l=$(".hhk-mcredr")),d.length>0&&(d.change(function(){l.hide(),$(".hhk-transferr").hide(),$(".payReturnNotes").show(),$(".hhk-cknumr").hide(),"cc"===$(this).val()?l.show("fade"):"ck"===$(this).val()?$(".hhk-cknumr").show("fade"):"tf"===$(this).val()&&$(".hhk-transferr").show("fade")}),d.change()),o.selBalTo.length>0&&o.selBalTo.change(function(){amtPaid()}),o.finalPaymentCb.length>0&&o.finalPaymentCb.change(function(){amtPaid()}),o.keyDepCb.length>0&&o.keyDepCb.change(function(){amtPaid()}),o.depRefundCb.length>0&&o.depRefundCb.change(function(){amtPaid()}),o.heldCb.length>0&&o.heldCb.change(function(){amtPaid()}),o.reimburseVatCb.length>0&&o.reimburseVatCb.change(function(){amtPaid()}),o.invoiceCb.length>0&&(o.invoiceCb.change(function(){amtPaid()}),$(".hhk-payInvAmt").change(function(){amtPaid()})),o.visitFeeCb.length>0&&o.visitFeeCb.change(function(){amtPaid()}),o.feePayAmt.length>0&&o.feePayAmt.change(function(){$(this).removeClass("ui-state-error"),amtPaid()}),o.cashTendered.length>0&&o.cashTendered.change(function(){o.cashTendered.removeClass("ui-state-highlight"),$("#tdCashMsg").hide();var e=parseFloat(o.totalPayment.val().replace(",",""));(isNaN(e)||e<0)&&(e=0);var t=parseFloat(o.cashTendered.val().replace("$","").replace(",",""));(isNaN(t)||t<0)&&(t=0,o.cashTendered.val(""));var a=t-e;a<0&&(a=0,o.cashTendered.addClass("ui-state-highlight")),$("#txtCashChange").text("$"+a.toFixed(2).toString())}),o.adjustBtn.length>0&&(o.adjustBtn.button(),o.adjustBtn.click(function(){getApplyDiscDiag(t,i)})),$("#divPmtMkup").on("click",".invAction",function(e){e.preventDefault(),("del"!=$(this).data("stat")||confirm("Delete this Invoice?"))&&invoiceAction($(this).data("iid"),$(this).data("stat"),e.target.id,"#keysfees",!0)}),createInvChooser(t,""),$("#daystoPay").change(function(){var t=parseInt($(this).val()),a=parseInt($(this).data("vid")),i=parseFloat($("#txtFixedRate").val()),r=parseInt($("#spnNumGuests").text()),n=o.feePayAmt,s=parseFloat($("#spnRcTax").data("tax")),d=parseFloat($("#txtadjAmount").val());$(this).val(""),isNaN(r)&&(r=1),isNaN(i)&&(i=0),isNaN(s)&&(s=0),isNaN(d)&&(d=0),isNaN(t)||t>0&&daysCalculator(t,e.val(),a,i,d,r,0,function(e){n.val(e.toFixed(2).toString()),n.change()})}),amtPaid()}function createInvChooser(e,t){$("#txtInvSearch"+t).length>0&&($("#txtInvSearch"+t).keypress(function(a){var i=$(this).val();"13"==a.keyCode&&(""!=i&&isNumber(parseInt(i,10))?$.getJSON("../house/roleSearch.php",{cmd:"filter",basis:"ba",letters:i},function(a){try{a=a[0]}catch(e){return void alert("Parser error - "+e.message)}a&&a.error&&(a.gotopage&&(response(),window.open(a.gotopage)),a.value=a.error),getInvoicee(a,e,t)}):(alert("Don't press the return key unless you enter an Id."),a.preventDefault()))}),createAutoComplete($("#txtInvSearch"+t),3,{cmd:"filter",basis:"ba"},function(a){getInvoicee(a,e,t)},!1))}function daysCalculator(e,t,a,i,r,n,s,o){if(e>0){var d={cmd:"rtcalc",vid:a,rid:s,nites:e,rcat:t,fxd:i,adj:r,gsts:n};$.post("ws_ckin.php",d,function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.open(e.gotopage),void flagAlertMessage(e.error,"error");if(e.amt){var t=parseFloat(e.amt);(isNaN(t)||t<0)&&(t=0),o(t)}}else alert("Bad Reply from Server")})}}function verifyBalDisp(){return""==$("#selexcpay").val()&&""!=$("#txtOverPayAmt").val()?($("#payChooserMsg").text('Set "Apply To" to the desired overpayment disposition. ').show(),$("#selexcpay").addClass("ui-state-highlight"),$("#pWarnings").text('Set "Apply To" to the desired overpayment disposition.').show(),!1):($("#payChooserMsg").text("").hide(),$("#selexcpay").removeClass("ui-state-highlight"),!0)}function verifyAmtTendrd(){"use strict";if(0===$("#PayTypeSel").length)return!0;var e=parseFloat($("#totalPayment").val().replace("$","").replace(",",""));if($("#tdCashMsg").hide("fade"),$("#tdInvceeMsg").text("").hide(),$("#tdChargeMsg").text("").hide(),"ca"===$("#PayTypeSel").val()){var t=parseFloat($("#txtCashTendered").val().replace("$","").replace(",","")),a=$("#remtotalPayment");if(a.length>0&&(e=parseFloat(a.val().replace("$","").replace(",",""))),(isNaN(e)||e<0)&&(e=0),(isNaN(t)||t<0)&&(t=0),e>0&&t<=0)return $("#tdCashMsg").text('Enter the amount paid into "Amount Tendered" ').show(),$("#pWarnings").text('Enter the amount paid into "Amount Tendered"').show(),!1;if(e>0&&t<e)return $("#tdCashMsg").text("Amount tendered is not enough ").show("fade"),$("#pWarnings").text("Amount tendered is not enough").show(),!1}else if("in"===$("#PayTypeSel").val()){var i=parseInt($("#txtInvId").val(),10);if((isNaN(i)||i<1)&&0!=e)return $("#tdInvceeMsg").text("The Invoicee is missing. ").show("fade"),!1}else if("cc"===$("#PayTypeSel").val()&&$("#selccgw").length>0&&""===$("#selccgw").val())return $("#tdChargeMsg").text("Select a location.").show("fade"),!1;return!0}function showReceipt(e,t,a,i){var r=$(e),n=$("<div id='print_button' style='margin-left:1em;'>Print</div>"),s={mode:"popup",popClose:!1,popHt:500,popWd:400,popX:200,popY:200,popTitle:a};void 0!==i&&i||(i=550),r.children().remove(),r.append($(t).addClass("ReceiptArea").css("max-width",i+"px")),n.button(),n.click(function(){$(".ReceiptArea").printArea(s),r.dialog("close")}),r.prepend(n),r.dialog("option","title",a),r.dialog("option","buttons",{}),r.dialog("option","width",i),r.dialog("open"),s.popHt=$("#pmtRcpt").height(),s.popWd=$("#pmtRcpt").width()}function reprintReceipt(e,t){$.post("ws_ckin.php",{cmd:"getPrx",pid:e},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}e.error&&(e.gotopage&&window.location.assign(e.gotopage),flagAlertMessage(e.error,"error")),showReceipt(t,e.receipt,"Receipt Copy")}})}function paymentRedirect(e,t){"use strict";if(e)if(e.hostedError)flagAlertMessage(e.hostedError,"error");else if(e.cvtx)window.location.assign(e.cvtx);else if(e.xfer&&t.length>0){if(t.children("input").remove(),t.prop("action",e.xfer),e.paymentId&&""!=e.paymentId)t.append($('<input type="hidden" name="PaymentID" value="'+e.paymentId+'"/>'));else{if(!e.cardId||""==e.cardId)return void flagAlertMessage("PaymentId and CardId are missing!","error");t.append($('<input type="hidden" name="CardID" value="'+e.cardId+'"/>'))}t.submit()}else e.inctx&&($("#contentDiv").empty().append($("<p>Processing Credit Payment...</p>")),InstaMed.launch(e.inctx),$("#instamed").css("visibility","visible").css("margin-top","50px;"))}function setupCOF(e,t){null==t&&(t=""),e.length>0&&($("input[name=rbUseCard"+t+"]").on("change",function(){0==$(this).val()||!0===$(this).prop("checked")&&"checkbox"===$(this).prop("type")?e.show():(e.hide(),$("#btnvrKeyNumber"+t).prop("checked",!1).change(),$("#txtvdNewCardName"+t).val("")),$("#tdChargeMsg"+t).text("").hide(),$("#selccgw"+t).removeClass("ui-state-highlight")}),($("input[name=rbUseCard"+t+"]:checked").val()>0||!1===$("input[name=rbUseCard"+t+"]").prop("checked")&&"checkbox"===$("input[name=rbUseCard"+t+"]").prop("type"))&&e.hide(),$("#btnvrKeyNumber"+t).length>0&&($("#btnvrKeyNumber"+t).change(function(){0==$("input[name=rbUseCard"+t+"]:checked").val()||!0===$("input[name=rbUseCard"+t+"]").prop("checked")&&"checkbox"===$("input[name=rbUseCard"+t+"]").prop("type")?$("#txtvdNewCardName"+t).show():($("#txtvdNewCardName"+t).hide(),$("#txtvdNewCardName"+t).val(""))}),$("#btnvrKeyNumber"+t).change()),$("#txtvdNewCardName"+t).length>0&&$("#txtvdNewCardName"+t).keydown(function(e){var t=e.which||e.keycode;return!(t>=48&&t<=57||t>=96&&t<=105)}))}function cardOnFile(e,t,a,i){if($("#tdChargeMsg"+i).text("").hide(),$("#selccgw"+i).length>0&&(0==$("input[name=rbUseCard"+i+"]:checked").val()||!0===$("input[name=rbUseCard"+i+"]").prop("checked"))&&($("#selccgw"+i).removeClass("ui-state-highlight"),0===$("#selccgw"+i+" option:selected").length))return $("#tdChargeMsg"+i).text("Select a location.").show("fade"),$("#selccgw"+i).addClass("ui-state-highlight"),!1;var r={cmd:"cof",idGuest:e,idGrp:t,pbp:a,index:i};$("#tblupCredit"+i).find("input").each(function(){"checkbox"===$(this).attr("type")?!1!==this.checked&&(r[$(this).attr("id")]="on"):"radio"===$(this).attr("type")?!1!==this.checked&&(r[$(this).attr("id")]=this.value):r[$(this).attr("id")]=this.value}),$("#selccgw"+i).length>0&&(r["selccgw"+i]=$("#selccgw"+i).val()),$("#selChargeType"+i).length>0&&(r["selChargeType"+i]=$("#selChargeType"+i).val()),$.post("ws_ckin.php",r,function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage(e.error,"error");e.hostedError&&flagAlertMessage(e.hostedError,"error"),paymentRedirect(e,$("#xform")),(e.success&&""!=e.success||e.COFmsg&&""!=e.COFmsg)&&flagAlertMessage((void 0===e.success?"":e.success)+(void 0===e.COFmsg?"":e.COFmsg),"success"),e.COFmkup&&""!==e.COFmkup&&($("#tblupCredit"+i).remove(),$("#upCreditfs").prepend($(e.COFmkup)),setupCOF($("#trvdCHName"+i),i))}})}function paymentsTable(e,t,a){$("#"+e).dataTable({columnDefs:[{targets:8,type:"date",render:function(e,t,a){return dateRender(e,t)}},{targets:9,type:"date",render:function(e,t,a){return dateRender(e,t)}}],dom:'<"top"if>rt<"bottom"lp><"clear">',displayLength:50,order:[[8,"asc"]],lengthMenu:[[25,50,-1],[25,50,"All"]]}),$("#"+t).find("input[type=button]").button(),$("#btnPayHistRef").button().click(function(){a()}),$("#"+t).on("click",".invAction",function(e){invoiceAction($(this).data("iid"),"view",e.target.id)}),$("#"+t).on("click",".hhk-voidPmt",function(){var e=$(this),t=parseFloat(e.data("amt"));"Saving..."!==e.val()&&confirm("Void/Reverse this payment for $"+t.toFixed(2).toString()+"?")&&(e.val("Saving..."),sendVoidReturn(e.attr("id"),"rv",e.data("pid"),null,a))}),$("#"+t).on("click",".hhk-voidRefundPmt",function(){var e=$(this);"Saving..."!==e.val()&&confirm("Void this Return?")&&(e.val("Saving..."),sendVoidReturn(e.attr("id"),"vr",e.data("pid"),null,a))}),$("#"+t).on("click",".hhk-returnPmt",function(){var e=$(this),t=parseFloat(e.data("amt"));"Saving..."!==e.val()&&confirm("Return this payment for $"+t.toFixed(2).toString()+"?")&&(e.val("Saving..."),sendVoidReturn(e.attr("id"),"r",e.data("pid"),t,a))}),$("#"+t).on("click",".hhk-undoReturnPmt",function(){var e=$(this),t=parseFloat(e.data("amt"));"Saving..."!==e.val()&&confirm("Undo this Return/Refund for $"+t.toFixed(2).toString()+"?")&&(e.val("Saving..."),sendVoidReturn(e.attr("id"),"ur",e.data("pid"),null,a))}),$("#"+t).on("click",".hhk-deleteWaive",function(){var e=$(this);"Deleting..."!==e.val()&&confirm("Delete this House payment?")&&(e.val("Deleting..."),sendVoidReturn(e.attr("id"),"d",e.data("ilid"),e.data("iid"),null,a))}),$("#"+t).on("click",".pmtRecpt",function(){reprintReceipt($(this).data("pid"),"#pmtRcpt")}),$("#"+t).mousedown(function(e){var t=$(e.target);"pudiv"!==t[0].id&&0===t.parents("#pudiv").length&&$("div#pudiv").remove()})}
