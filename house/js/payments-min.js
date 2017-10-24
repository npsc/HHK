function getApplyDiscDiag(e,t){"use strict";e&&""!=e?$.post("ws_ckin.php",{cmd:"getHPay",ord:e,arrDate:$("#spanvArrDate").text()},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)e.gotopage&&window.location.assign(e.gotopage),flagAlertMessage(e.error,!0);else if(e.markup){t.children().remove();var a={Save:function(){var e=parseFloat($("#housePayment").val().replace("$","").replace(",","")),t=$("#housePayment").data("vid"),a=$.datepicker.formatDate("yy-mm-dd",$("#housePaymentDate").datepicker("getDate")),i=$("#housePaymentNote").val();isNaN(e)&&(e=0),saveDiscountPayment(t,$("#cbAdjustPmt1").prop("checked")?$("#cbAdjustPmt1").data("item"):$("#cbAdjustPmt2").data("item"),e,$("#selHouseDisc").val(),$("#selAddnlChg").val(),a,i),$(this).dialog("close")},Cancel:function(){$(this).dialog("close")}};t.append($('<div class="hhk-panel hhk-tdbox hhk-visitdialog" style="font-size:0.8em;"/>').append($(e.markup))),$("#cbAdjustType").buttonset(),$("#cbAdjustPmt1, #cbAdjustPmt2").change(function(){var e=$(this).data("hid"),t=$(this).data("sho");$("#"+e).val(""),$("#"+t).val(""),$("#housePayment").val(""),$(this).prop("checked")?($("#"+t).show(),$("#"+e).hide()):($("#"+e).hide(),$("#"+t).show())}),gblAdjustData.disc=e.disc,gblAdjustData.addnl=e.addnl,$("#selAddnlChg, #selHouseDisc").change(function(){var e=gblAdjustData[$(this).data("amts")];$("#housePayment").val(e[$(this).val()])}),$("#cbAdjustPmt1").length>0?($("#cbAdjustPmt1").prop("checked",!0),$("#cbAdjustPmt1").change()):($("#cbAdjustPmt2").prop("checked",!0),$("#cbAdjustPmt2").change()),t.dialog("option","buttons",a),t.dialog("option","title","Adjust Fees"),t.dialog("option","width",400),t.dialog("open")}}}):flagAlertMessage("Order Number is missing",!0)}function saveDiscountPayment(e,t,a,i,s,r,n){"use strict";$.post("ws_ckin.php",{cmd:"saveHPay",ord:e,item:t,amt:a,dsc:i,chg:s,adjDate:r,notes:n},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}e.error&&(e.gotopage&&window.location.assign(e.gotopage),flagAlertMessage(e.error,!0)),e.reply&&""!=e.reply&&(flagAlertMessage(e.reply,!1),$("#keysfees").dialog("close")),e.receipt&&""!==e.receipt&&($("#keysfees").length>0&&$("#keysfees").dialog("close"),showReceipt("#pmtRcpt",e.receipt,"Payment Receipt"))}})}function getInvoicee(e,t){"use strict";var a=parseInt(e.id,10);!1===isNaN(a)&&a>0?($("#txtInvName").val(e.value),$("#txtInvId").val(a)):($("#txtInvName").val(""),$("#txtInvId").val("")),$("#txtOrderNum").val(t),$("#txtInvSearch").val("")}function invoiceAction(e,t,a,i,s){"use strict";$.post("ws_resc.php",{cmd:"invAct",iid:e,x:a,action:t,sbt:s},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage(e.error,!0);if(e.delete&&("0"==e.eid?(flagAlertMessage(e.delete,!1),$("#btnInvGo").click()):$("#"+e.eid).parents("tr").first().hide("fade")),e.markup){var t=$(e.markup);void 0!=i&&""!=i?$(i).append(t):$("body").append(t),t.position({my:"left top",at:"left bottom",of:"#"+e.eid})}}})}function sendVoidReturn(e,t,a,i){var s={pid:a,bid:e};t&&"v"===t?s.cmd="void":t&&"rv"===t?s.cmd="revpmt":t&&"r"===t?(s.cmd="rtn",s.amt=i):t&&"vr"===t?s.cmd="voidret":t&&"d"===t&&(s.cmd="delWaive",s.iid=i),$.post("ws_ckin.php",s,function(e){var t="";if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.bid&&$("#"+e.bid).remove(),e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage(e.error,!0);if(e.reversal&&""!==e.reversal&&(t=e.reversal),e.warning)return void flagAlertMessage(t+e.warning,!0);e.success&&flagAlertMessage(t+e.success,!1),e.receipt&&showReceipt("#pmtRcpt",e.receipt,"Receipt")}})}function amtPaid(){"use strict";var e=new payCtrls,t=0,a=0,i=0,s="",r=0,n=0,o=0,d=0,l=0,h=0,c=0,p=0,g=0,v=0,m=0,u=isCheckedOut;e.msg.text("").hide(),e.visitFeeCb.length>0&&(a=parseFloat($("#spnvfeeAmt").data("amt")),isNaN(a)||a<0||!1===e.visitFeeCb.prop("checked")?(a=0,e.visitFeeAmt.val("")):e.visitFeeAmt.val(a.toFixed(2).toString())),!u&&e.keyDepCb.length>0&&(t=parseFloat($("#spnDepAmt").data("amt")),isNaN(t)||t<0||!1===e.keyDepCb.prop("checked")?(t=0,e.keyDepAmt.val("")):e.keyDepAmt.val(t.toFixed(2).toString())),e.invoiceCb.length>0&&e.invoiceCb.each(function(){var e,t=parseInt($(this).data("invnum")),a=$("#"+t+"invPayAmt"),i=parseFloat($(this).data("invamt"));!0===$(this).prop("checked")?(a.prop("disabled",!1),""===a.val()&&a.val(i.toFixed(2).toString()),e=parseFloat(a.val().replace("$","").replace(",","")),isNaN(e)||0==e?(e=0,a.val("")):Math.abs(e)>Math.abs(i)&&(e=i,a.val(e.toFixed(2).toString())),n+=e):""!==a.val()&&(a.val(""),a.prop("disabled",!0))}),e.feePayAmt.length>0&&(s=e.feePayAmt.val().replace("$","").replace(",",""),i=parseFloat(s),(isNaN(i)||i<0)&&(e.feePayAmt.val(""),i=0)),e.feesCharges.length>0&&(r=parseFloat(e.feesCharges.val()),isNaN(r)&&(r=0)),e.guestCredit.length>0&&(v=parseFloat(e.guestCredit.val()),isNaN(v)&&(v=0)),e.depRefundAmt.length>0&&(g=parseFloat(e.depRefundAmt.val()),isNaN(g)&&(g=0)),e.heldCb.length>0&&(d=parseFloat(e.heldCb.data("amt")),(isNaN(d)||d<0)&&(d=0),e.heldCb.prop("checked")&&(o=d)),l=a+t+r+n+v+g,u||(l+=i),l>0&&o>0?o>l&&!u?(o=l,l=0):l-=o:l<0&&o>0?l-=o:0===l&&o>0&&u?l-=o:e.heldCb.length>0&&e.heldAmtTb.val(""),u?($(".hhk-minPayment").show("fade"),p=l<0?l-i:l+i,l-i<=0?($(".hhk-HouseDiscount").hide(),e.hsDiscAmt.val(""),e.finalPaymentCb.prop("checked",!1),m=0-(l-i),"r"===e.selBalTo.val()?l>=0?(i!==l&&alert("Pay Room Fees amount is reduced to: $"+l.toFixed(2).toString()),i=l,m=0,e.selBalTo.val(""),$("#txtRtnAmount").val(""),$("#divReturnPay").hide()):(i>0&&alert("Pay Room Fees amount is reduced to: $0.00"),m-=i,i=0,$("#divReturnPay").show("fade"),$("#txtRtnAmount").val(m.toFixed(2).toString())):($("#txtRtnAmount").val(""),$("#divReturnPay").hide()),p=i,m>0?$(".hhk-Overpayment").show("fade"):$(".hhk-Overpayment").hide()):($(".hhk-Overpayment").hide(),m=0,e.finalPaymentCb.prop("checked")?((c=l-i)<=0?(c=0,e.hsDiscAmt.val("")):e.hsDiscAmt.val((0-c).toFixed(2).toString()),p=i):(e.hsDiscAmt.val(""),p=a+t+n+i),$(".hhk-HouseDiscount").show("fade"))):($(".hhk-Overpayment").hide(),$(".hhk-HouseDiscount").hide(),e.hsDiscAmt.val(""),m=0,p=l,h=a+t+n+i),p>0||p<0&&!u?($(".paySelectTbl").show("fade"),$(".hhk-minPayment").show("fade"),p<0&&!u&&$("#txtRtnAmount").val((0-p).toFixed(2).toString())):(p=0,$(".paySelectTbl").hide(),!1===u&&0===h?($(".hhk-minPayment").hide(),o=0):$(".hhk-minPayment").show("fade")),0===i&&""===s?e.feePayAmt.val(""):e.feePayAmt.val(i.toFixed(2).toString()),0===m?e.overPay.val(""):e.overPay.val(m.toFixed(2).toString()),o>0?e.heldAmtTb.val((0-o).toFixed(2).toString()):e.heldAmtTb.val(""),e.totalCharges.val(l.toFixed(2).toString()),e.totalPayment.val(p.toFixed(2).toString()),$("#spnPayAmount").text("$"+p.toFixed(2).toString()),e.cashTendered.change()}function setupPayments(e,t,a,i,s){"use strict";var r=$("#PayTypeSel"),n=$(".tblCredit");0===n.length&&(n=$(".hhk-mcred")),r.length>0&&(r.change(function(){$(".hhk-cashTndrd").hide(),$(".hhk-cknum").hide(),$("#tblInvoice").hide(),$(".hhk-transfer").hide(),$(".hhk-tfnum").hide(),n.hide(),$("#tdCashMsg").hide(),$(".paySelectNotes").show(),"cc"===$(this).val()?n.show("fade"):"ck"===$(this).val()?$(".hhk-cknum").show("fade"):"in"===$(this).val()?($("#tblInvoice").show("fade"),$(".paySelectNotes").hide()):"tf"===$(this).val()?$(".hhk-transfer").show("fade"):$(".hhk-cashTndrd").show("fade")}),r.change());var o=$("#rtnTypeSel"),d=$(".tblCreditr");0===d.length&&(d=$(".hhk-mcredr")),o.length>0&&(o.change(function(){d.hide(),$(".hhk-transferr").hide(),$(".payReturnNotes").show(),$(".hhk-cknum").hide(),"cc"===$(this).val()?d.show("fade"):"ck"===$(this).val()?$(".hhk-cknum").show("fade"):"tf"===$(this).val()?$(".hhk-transferr").show("fade"):"in"===$(this).val()&&$(".payReturnNotes").hide()}),o.change());var l=new payCtrls;l.selBalTo.length>0&&l.selBalTo.change(function(){amtPaid()}),l.finalPaymentCb.length>0&&l.finalPaymentCb.change(function(){amtPaid()}),l.keyDepCb.length>0&&l.keyDepCb.change(function(){amtPaid()}),l.heldCb.length>0&&l.heldCb.change(function(){amtPaid()}),l.invoiceCb.length>0&&(l.invoiceCb.change(function(){amtPaid()}),$(".hhk-payInvAmt").change(function(){amtPaid()})),l.visitFeeCb.length>0&&l.visitFeeCb.change(function(){amtPaid()}),l.feePayAmt.length>0&&l.feePayAmt.change(function(){$(this).removeClass("ui-state-error"),amtPaid()}),l.cashTendered.length>0&&l.cashTendered.change(function(){l.cashTendered.removeClass("ui-state-highlight"),$("#tdCashMsg").hide();var e=parseFloat(l.totalPayment.val().replace(",",""));(isNaN(e)||e<0)&&(e=0);var t=parseFloat(l.cashTendered.val().replace("$","").replace(",",""));(isNaN(t)||t<0)&&(t=0,l.cashTendered.val(""));var a=t-e;a<0&&(a=0,l.cashTendered.addClass("ui-state-highlight")),$("#txtCashChange").text("$"+a.toFixed(2).toString())}),e&&t&&t.length>0&&(t.change(function(){$(this).removeClass("ui-state-error");var t=$(this).val();if(""==t&&(t=0),l.keyDepAmt.length>0&&e[t]&&(0===e[t].key?($("#spnDepAmt").data("amt",""),$("#spnDepAmt").text(""),l.keyDepAmt.val(""),l.keyDepCb.prop("checked",!1),$(".hhk-kdrow").hide()):($("#spnDepAmt").data("amt",e[t].key),$("#spnDepAmt").text("($"+e[t].key+")"),l.keyDepAmt.val(e[t].key),$(".hhk-kdrow").show("fade")),amtPaid()),t>0&&e[t]&&$("#myRescId").length>0){$("#rmChgMsg").text("").hide(),$("#rmDepMessage").text("").hide();var i=$("#myRescId").data("idresc"),s=$("#myRescId").data("pmdl");if(e[i].rate!==e[t].rate&&"b"===s&&$("#rmChgMsg").text("The room rate is different.").show("fade"),e[i].key!==e[t].key){var r="";$("#spnDepMsg").hide(),$("#selDepDisposition").show("fade"),0==e[t].key?"0"!=$("#kdPaid").data("amt")&&(r="There is no deposit for this room.  Set the Deposit Status (above) accordingly."):r="The deposit for this room is $"+e[t].key.toFixed(2).toString(),$("#rmDepMessage").text(r).show("fade")}else $("#selDepDisposition").hide(),$("#spnDepMsg").show("fade")}a.change()}),t.change(),$("#resvChangeDate").change(function(){$("#rbReplaceRoomnew").prop("checked",!0)})),l.adjustBtn.length>0&&(l.adjustBtn.button(),l.adjustBtn.click(function(){getApplyDiscDiag(i,s)})),$("#divPmtMkup").on("click",".invAction",function(e){e.preventDefault(),("del"!=$(this).data("stat")||confirm("Delete this Invoice?"))&&invoiceAction($(this).data("iid"),$(this).data("stat"),e.target.id,"#keysfees",!0)}),$("#txtInvSearch").length>0&&($("#txtInvSearch").keypress(function(e){var t=$(this).val();"13"==e.keyCode&&(""!=t&&isNumber(parseInt(t,10))?$.getJSON("../house/roleSearch.php",{cmd:"filter",basis:"ba",letters:t},function(e){try{e=e[0]}catch(e){return void alert("Parser error - "+e.message)}e&&e.error&&(e.gotopage&&(response(),window.open(e.gotopage)),e.value=e.error),getInvoicee(e,i)}):(alert("Don't press the return key unless you enter an Id."),e.preventDefault()))}),createAutoComplete($("#txtInvSearch"),3,{cmd:"filter",basis:"ba"},function(e){getInvoicee(e,i)},!1)),$("#daystoPay").change(function(){var e=parseInt($(this).val()),t=parseInt($(this).data("vid")),i=parseFloat($("#txtFixedRate").val()),s=parseInt($("#spnNumGuests").text());isNaN(s)&&(s=1),isNaN(i)&&(i=0);var r=parseFloat($("#txtadjAmount").val());if(isNaN(r)&&(r=0),isNaN(e))$(this).val("");else if(e>0){var n={cmd:"rtcalc",vid:t,nites:e,rcat:a.val(),fxd:i,adj:r,gsts:s};$.post("ws_ckin.php",n,function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.open(e.gotopage),void flagAlertMessage(e.error,!0);e.amt&&(l.feePayAmt.val(e.amt),l.feePayAmt.change())}else alert("Bad Reply from Server")})}}),amtPaid()}function verifyBalDisp(){return""==$("#selexcpay").val()&&""!=$("#txtOverPayAmt").val()?($("#payChooserMsg").text('Set "Apply To" to the desired overpayment disposition. ').show("fade"),$("#selexcpay").addClass("ui-state-highlight"),!1):($("#payChooserMsg").text("").hide(),$("#selexcpay").removeClass("ui-state-highlight"),!0)}function verifyAmtTendrd(){"use strict";if(0===$("#PayTypeSel").length)return!0;if($("#tdCashMsg").hide("fade"),$("#tdInvceeMsg").text("").hide(),"ca"===$("#PayTypeSel").val()){var e=parseFloat($("#totalPayment").val().replace("$","").replace(",","")),t=parseFloat($("#txtCashTendered").val().replace("$","").replace(",","")),a=$("#remtotalPayment");if(a.length>0&&(e=parseFloat(a.val().replace("$","").replace(",",""))),(isNaN(e)||e<0)&&(e=0),(isNaN(t)||t<0)&&(t=0),e>0&&t<=0)return $("#tdCashMsg").text('Enter the amount paid into "Amount Tendered" ').show("fade"),!1;if(e>0&&t<e)return $("#tdCashMsg").text("Amount tendered is not enough ").show("fade"),!1}else if("in"===$("#PayTypeSel").val()){var i=parseInt($("#txtInvId").val(),10);if(isNaN(i)||i<1)return $("#tdInvceeMsg").text("The Invoicee is missing. ").show("fade"),!1}return!0}function showReceipt(e,t,a,i){var s=$(e),r=$("<div id='print_button' style='margin-left:1em;'>Print</div>"),n={mode:"popup",popClose:!1,popHt:500,popWd:400,popX:200,popY:200,popTitle:a};void 0!==i&&i||(i=550),s.children().remove(),s.append($(t).addClass("ReceiptArea").css("max-width",i+"px")),r.button(),r.click(function(){$(".ReceiptArea").printArea(n),s.dialog("close")}),s.prepend(r),s.dialog("option","title",a),s.dialog("option","buttons",{}),s.dialog("option","width",i),s.dialog("open"),n.popHt=$("#pmtRcpt").height(),n.popWd=$("#pmtRcpt").width()}function reprintReceipt(e,t){$.post("ws_ckin.php",{cmd:"getPrx",pid:e},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}e.error&&(e.gotopage&&window.location.assign(e.gotopage),flagAlertMessage(e.error,!0)),showReceipt(t,e.receipt,"Receipt Copy")}})}function cardOnFile(e,t,a){var i={cmd:"cof",idGuest:e,idGrp:t,pbp:a};$("#tblupCredit").find("input").each(function(){this.checked&&(i[$(this).attr("id")]=$(this).val())}),$.post("ws_ckin.php",i,function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage(e.error,!0);if(e.hostedError&&flagAlertMessage(e.hostedError,!0),e.xfer){var t=$("#xform");if(t.children("input").remove(),t.prop("action",e.xfer),e.paymentId&&""!=e.paymentId)t.append($('<input type="hidden" name="PaymentID" value="'+e.paymentId+'"/>'));else{if(!e.cardId||""==e.cardId)return void flagAlertMessage("PaymentId and CardId are missing!",!0);t.append($('<input type="hidden" name="CardID" value="'+e.cardId+'"/>'))}t.submit()}e.success&&""!=e.success&&flagAlertMessage(e.success,!1)}})}function updateCredit(e,t,a,i){var s={Continue:function(){cardOnFile(e,t),$(this).dialog("close")},Cancel:function(){$(this).dialog("close")}},r="";a&&""!=a&&(r=" - "+a),$.post("ws_ckin.php",{cmd:"viewCredit",idGuest:e,reg:t},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)e.gotopage&&window.location.assign(e.gotopage),flagAlertMessage(e.error,!0);else if(e.success){var t=$("#"+i);t.children().remove(),t.append($('<div class="hhk-panel hhk-tdbox hhk-visitdialog"/>').append($(e.success))),t.dialog("option","buttons",s),t.dialog("option","width",400),t.dialog("option","title","Card On File"+r),t.dialog("open")}}})}var gblAdjustData=[],payCtrls=function(){var e=this;e.keyDepAmt=$("#keyDepAmt"),e.keyDepCb=$("#keyDepRx"),e.visitFeeAmt=$("#visitFeeAmt"),e.visitFeeCb=$("#visitFeeCb"),e.feePayAmt=$("input#feesPayment"),e.feesCharges=$("#feesCharges"),e.totalPayment=$("#totalPayment"),e.totalCharges=$("#totalCharges"),e.cashTendered=$("#txtCashTendered"),e.invoiceCb=$(".hhk-payInvCb"),e.adjustBtn=$("#paymentAdjust"),e.msg=$("#payChooserMsg"),e.heldAmtTb=$("#heldAmount"),e.heldCb=$("#cbHeld"),e.hsDiscAmt=$("#HsDiscAmount"),e.depRefundAmt=$("#DepRefundAmount"),e.finalPaymentCb=$("input#cbFinalPayment"),e.overPay=$("#txtOverPayAmt"),e.guestCredit=$("#guestCredit"),e.selBalTo=$("#selexcpay")};
