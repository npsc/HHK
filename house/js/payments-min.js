function getApplyDiscDiag(a,b){"use strict";if(!a||""==a)return void flagAlertMessage("Order Number is missing",!0);$.post("ws_ckin.php",{cmd:"getHPay",ord:a,arrDate:$("#spanvArrDate").text()},function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}if(a.error)a.gotopage&&window.location.assign(a.gotopage),flagAlertMessage(a.error,!0);else if(a.markup){b.children().remove();var c={Save:function(){var a=parseFloat($("#housePayment").val().replace("$","").replace(",","")),b=$("#housePayment").data("vid"),c="",d=$.datepicker.formatDate("yy-mm-dd",$("#housePaymentDate").datepicker("getDate")),e=$("#housePaymentNote").val();isNaN(a)&&(a=0),c=$("#cbAdjustPmt1").prop("checked")?$("#cbAdjustPmt1").data("item"):$("#cbAdjustPmt2").data("item"),saveDiscountPayment(b,c,a,$("#selHouseDisc").val(),$("#selAddnlChg").val(),d,e),$(this).dialog("close")},Cancel:function(){$(this).dialog("close")}};b.append($('<div class="hhk-panel hhk-tdbox hhk-visitdialog" style="font-size:0.8em;"/>').append($(a.markup))),$("#cbAdjustType").buttonset(),$("#cbAdjustPmt1, #cbAdjustPmt2").change(function(){var a=$(this).data("hid"),b=$(this).data("sho");$("#"+a).val(""),$("#"+b).val(""),$("#housePayment").val(""),$(this).prop("checked")?($("#"+b).show(),$("#"+a).hide()):($("#"+a).hide(),$("#"+b).show())}),gblAdjustData.disc=a.disc,gblAdjustData.addnl=a.addnl,$("#selAddnlChg, #selHouseDisc").change(function(){var a=gblAdjustData[$(this).data("amts")];$("#housePayment").val(a[$(this).val()])}),$("#cbAdjustPmt1").length>0?($("#cbAdjustPmt1").prop("checked",!0),$("#cbAdjustPmt1").change()):($("#cbAdjustPmt2").prop("checked",!0),$("#cbAdjustPmt2").change()),b.dialog("option","buttons",c),b.dialog("option","title","Adjust Fees"),b.dialog("option","width",400),b.dialog("open")}}})}function saveDiscountPayment(a,b,c,d,e,f,g){"use strict";$.post("ws_ckin.php",{cmd:"saveHPay",ord:a,item:b,amt:c,dsc:d,chg:e,adjDate:f,notes:g},function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}a.error&&(a.gotopage&&window.location.assign(a.gotopage),flagAlertMessage(a.error,!0)),a.reply&&""!=a.reply&&(flagAlertMessage(a.reply,!1),$("#keysfees").dialog("close")),a.receipt&&""!==a.receipt&&($("#keysfees").length>0&&$("#keysfees").dialog("close"),showReceipt("#pmtRcpt",a.receipt,"Payment Receipt"))}})}function getInvoicee(a,b){"use strict";var c=parseInt(a.id,10);!1===isNaN(c)&&c>0?($("#txtInvName").val(a.value),$("#txtInvId").val(c)):($("#txtInvName").val(""),$("#txtInvId").val("")),$("#txtOrderNum").val(b),$("#txtInvSearch").val("")}function invoiceAction(a,b,c,d,e){"use strict";$.post("ws_resc.php",{cmd:"invAct",iid:a,x:c,action:b,sbt:e},function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}if(a.error)return a.gotopage&&window.location.assign(a.gotopage),void flagAlertMessage(a.error,!0);if(a.delete&&("0"==a.eid?(flagAlertMessage(a.delete,!1),$("#btnInvGo").click()):$("#"+a.eid).parents("tr").first().hide("fade")),a.markup){var b=$(a.markup);void 0!=d&&""!=d?$(d).append(b):$("body").append(b),b.position({my:"left top",at:"left bottom",of:"#"+a.eid})}}})}function amtPaid(){"use strict";var a=new payCtrls,b=0,c=0,d=0,e="",f=0,g=0,h=0,i=0,j=0,k=0,l=0,m=0,n=0,o=0,p=0,q=isCheckedOut;a.msg.text("").hide(),a.visitFeeCb.length>0&&(c=parseFloat($("#spnvfeeAmt").data("amt")),isNaN(c)||c<0||!1===a.visitFeeCb.prop("checked")?(c=0,a.visitFeeAmt.val("")):a.visitFeeAmt.val(c.toFixed(2).toString())),!q&&a.keyDepCb.length>0&&(b=parseFloat($("#spnDepAmt").data("amt")),isNaN(b)||b<0||!1===a.keyDepCb.prop("checked")?(b=0,a.keyDepAmt.val("")):a.keyDepAmt.val(b.toFixed(2).toString())),a.invoiceCb.length>0&&a.invoiceCb.each(function(){var d,a=parseInt($(this).data("invnum")),b=$("#"+a+"invPayAmt"),c=parseFloat($(this).data("invamt"));!0===$(this).prop("checked")?(b.prop("disabled",!1),""===b.val()&&b.val(c.toFixed(2).toString()),d=parseFloat(b.val().replace("$","").replace(",","")),isNaN(d)||0==d?(d=0,b.val("")):Math.abs(d)>Math.abs(c)&&(d=c,b.val(d.toFixed(2).toString())),g+=d):""!==b.val()&&(b.val(""),b.prop("disabled",!0))}),a.feePayAmt.length>0&&(e=a.feePayAmt.val().replace("$","").replace(",",""),d=parseFloat(e),(isNaN(d)||d<0)&&(a.feePayAmt.val(""),d=0)),a.feesCharges.length>0&&(f=parseFloat(a.feesCharges.val()),isNaN(f)&&(f=0)),a.guestCredit.length>0&&(o=parseFloat(a.guestCredit.val()),isNaN(o)&&(o=0)),a.depRefundAmt.length>0&&(n=parseFloat(a.depRefundAmt.val()),isNaN(n)&&(n=0)),a.heldCb.length>0&&(i=parseFloat(a.heldCb.data("amt")),(isNaN(i)||i<0)&&(i=0),a.heldCb.prop("checked")&&(h=i)),j=c+b+f+g+o+n,q||(j+=d),j>0&&h>0?h>j&&!q?(h=j,j=0):j-=h:j<0&&h>0?j-=h:0===j&&h>0&&q?j-=h:a.heldCb.length>0&&a.heldAmtTb.val(""),q?($(".hhk-minPayment").show("fade"),m=j<0?j-d:j+d,j-d<=0?($(".hhk-HouseDiscount").hide(),a.hsDiscAmt.val(""),a.finalPaymentCb.prop("checked",!1),p=0-(j-d),"r"===a.selBalTo.val()?j>=0?(d!==j&&alert("Pay Room Fees amount is reduced to: $"+j.toFixed(2).toString()),d=j,p=0,a.selBalTo.val(""),$("#txtRtnAmount").val(""),$("#divReturnPay").hide()):(d>0&&alert("Pay Room Fees amount is reduced to: $0.00"),p-=d,d=0,$("#divReturnPay").show("fade"),$("#txtRtnAmount").val(p.toFixed(2).toString())):($("#txtRtnAmount").val(""),$("#divReturnPay").hide()),m=d,p>0?$(".hhk-Overpayment").show("fade"):$(".hhk-Overpayment").hide()):($(".hhk-Overpayment").hide(),p=0,a.finalPaymentCb.prop("checked")?(l=j-d,l<=0?(l=0,a.hsDiscAmt.val("")):a.hsDiscAmt.val((0-l).toFixed(2).toString()),m=d):(a.hsDiscAmt.val(""),m=c+b+g+d),$(".hhk-HouseDiscount").show("fade"))):($(".hhk-Overpayment").hide(),$(".hhk-HouseDiscount").hide(),a.hsDiscAmt.val(""),p=0,m=j,k=c+b+g+d),m>0||m<0&&!q?($(".paySelectTbl").show("fade"),$(".hhk-minPayment").show("fade"),m<0&&!q&&$("#txtRtnAmount").val((0-m).toFixed(2).toString())):(m=0,$(".paySelectTbl").hide(),!1===q&&0===k?($(".hhk-minPayment").hide(),h=0):$(".hhk-minPayment").show("fade")),0===d&&""===e?a.feePayAmt.val(""):a.feePayAmt.val(d.toFixed(2).toString()),0===p?a.overPay.val(""):a.overPay.val(p.toFixed(2).toString()),h>0?a.heldAmtTb.val((0-h).toFixed(2).toString()):a.heldAmtTb.val(""),a.totalCharges.val(j.toFixed(2).toString()),a.totalPayment.val(m.toFixed(2).toString()),$("#spnPayAmount").text("$"+m.toFixed(2).toString()),a.cashTendered.change()}function setupPayments(a,b,c,d,e){"use strict";var f=$("#PayTypeSel"),g=$(".tblCredit");0===g.length&&(g=$(".hhk-mcred")),f.length>0&&(f.change(function(){$(".hhk-cashTndrd").hide(),$(".hhk-cknum").hide(),$("#tblInvoice").hide(),$(".hhk-transfer").hide(),$(".hhk-tfnum").hide(),g.hide(),$("#tdCashMsg").hide(),$(".paySelectNotes").show(),"cc"===$(this).val()?g.show("fade"):"ck"===$(this).val()?$(".hhk-cknum").show("fade"):"in"===$(this).val()?($("#tblInvoice").show("fade"),$(".paySelectNotes").hide()):"tf"===$(this).val()?$(".hhk-transfer").show("fade"):$(".hhk-cashTndrd").show("fade")}),f.change());var h=$("#rtnTypeSel"),i=$(".tblCreditr");0===i.length&&(i=$(".hhk-mcredr")),h.length>0&&(h.change(function(){i.hide(),$(".hhk-transferr").hide(),$(".payReturnNotes").show(),$(".hhk-cknum").hide(),"cc"===$(this).val()?i.show("fade"):"ck"===$(this).val()?$(".hhk-cknum").show("fade"):"tf"===$(this).val()?$(".hhk-transferr").show("fade"):"in"===$(this).val()&&$(".payReturnNotes").hide()}),h.change());var j=new payCtrls;j.selBalTo.length>0&&j.selBalTo.change(function(){amtPaid()}),j.finalPaymentCb.length>0&&j.finalPaymentCb.change(function(){amtPaid()}),j.keyDepCb.length>0&&j.keyDepCb.change(function(){amtPaid()}),j.heldCb.length>0&&j.heldCb.change(function(){amtPaid()}),j.invoiceCb.length>0&&(j.invoiceCb.change(function(){amtPaid()}),$(".hhk-payInvAmt").change(function(){amtPaid()})),j.visitFeeCb.length>0&&j.visitFeeCb.change(function(){amtPaid()}),j.feePayAmt.length>0&&j.feePayAmt.change(function(){$(this).removeClass("ui-state-error"),amtPaid()}),j.cashTendered.length>0&&j.cashTendered.change(function(){j.cashTendered.removeClass("ui-state-highlight"),$("#tdCashMsg").hide();var a=parseFloat(j.totalPayment.val().replace(",",""));(isNaN(a)||a<0)&&(a=0);var b=parseFloat(j.cashTendered.val().replace("$","").replace(",",""));(isNaN(b)||b<0)&&(b=0,j.cashTendered.val(""));var c=b-a;c<0&&(c=0,j.cashTendered.addClass("ui-state-highlight")),$("#txtCashChange").text("$"+c.toFixed(2).toString())}),b&&b.length>0&&(b.change(function(){$(this).removeClass("ui-state-error");var b=$(this).val();if(""==b&&(b=0),j.keyDepAmt.length>0&&a[b]&&(0===a[b].key?($("#spnDepAmt").data("amt",""),$("#spnDepAmt").text(""),j.keyDepAmt.val(""),j.keyDepCb.prop("checked",!1),$(".hhk-kdrow").hide()):($("#spnDepAmt").data("amt",a[b].key),$("#spnDepAmt").text("($"+a[b].key+")"),j.keyDepAmt.val(a[b].key),$(".hhk-kdrow").show("fade")),amtPaid()),b>0&&a[b]&&$("#myRescId").length>0){$("#rmChgMsg").text("").hide(),$("#rmDepMessage").text("").hide();var d=$("#myRescId").data("idresc"),e=$("#myRescId").data("pmdl");if(a[d].rate!==a[b].rate&&"b"===e&&$("#rmChgMsg").text("The room rate is different.").show("fade"),a[d].key!==a[b].key){var f="";$("#spnDepMsg").hide(),$("#selDepDisposition").show("fade"),0==a[b].key?"0"!=$("#kdPaid").data("amt")&&(f="There is no deposit for this room.  Set the Deposit Status (above) accordingly."):f="The deposit for this room is $"+a[b].key.toFixed(2).toString(),$("#rmDepMessage").text(f).show("fade")}else $("#selDepDisposition").hide(),$("#spnDepMsg").show("fade")}c.change()}),b.change(),$("#resvChangeDate").change(function(){$("#rbReplaceRoomnew").prop("checked",!0)})),j.adjustBtn.length>0&&(j.adjustBtn.button(),j.adjustBtn.click(function(){getApplyDiscDiag(d,e)})),$("#divPmtMkup").on("click",".invAction",function(a){a.preventDefault(),("del"!=$(this).data("stat")||confirm("Delete this Invoice?"))&&invoiceAction($(this).data("iid"),$(this).data("stat"),a.target.id,"#keysfees",!0)}),$("#txtInvSearch").length>0&&($("#txtInvSearch").keypress(function(a){var b=$(this).val();"13"==a.keyCode&&(""!=b&&isNumber(parseInt(b,10))?$.getJSON("../house/roleSearch.php",{cmd:"filter",basis:"ba",letters:b},function(a){try{a=a[0]}catch(a){return void alert("Parser error - "+a.message)}a&&a.error&&(a.gotopage&&(response(),window.open(a.gotopage)),a.value=a.error),getInvoicee(a,d)}):(alert("Don't press the return key unless you enter an Id."),a.preventDefault()))}),createAutoComplete($("#txtInvSearch"),3,{cmd:"filter",basis:"ba"},function(a){getInvoicee(a,d)},!1)),$("#daystoPay").change(function(){var a=parseInt($(this).val()),b=parseInt($(this).data("vid")),d=parseFloat($("#txtFixedRate").val());isNaN(d)&&(d=0);var e=parseFloat($("#txtadjAmount").val());if(isNaN(e)&&(e=0),isNaN(a))return void $(this).val("");if(a>0){var f={cmd:"rtcalc",vid:b,nites:a,rcat:c.val(),fxd:d,adj:e};$.post("ws_ckin.php",f,function(a){if(!a)return void alert("Bad Reply from Server");try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}if(a.error)return a.gotopage&&window.open(a.gotopage),void flagAlertMessage(a.error,!0);a.amt&&(j.feePayAmt.val(a.amt),j.feePayAmt.change())})}}),amtPaid()}function verifyBalDisp(){return""==$("#selexcpay").val()&&""!=$("#txtOverPayAmt").val()?($("#payChooserMsg").text('Set "Apply To" to the desired overpayment disposition. ').show("fade"),$("#selexcpay").addClass("ui-state-highlight"),!1):($("#payChooserMsg").text("").hide(),$("#selexcpay").removeClass("ui-state-highlight"),!0)}function verifyAmtTendrd(){"use strict";if(0===$("#PayTypeSel").length)return!0;if($("#tdCashMsg").hide("fade"),"ca"==$("#PayTypeSel").val()){var a=parseFloat($("#totalPayment").val().replace("$","").replace(",","")),b=parseFloat($("#txtCashTendered").val().replace("$","").replace(",","")),c=$("#remtotalPayment");if(c.length>0&&(a=parseFloat(c.val().replace("$","").replace(",",""))),(isNaN(a)||a<0)&&(a=0),(isNaN(b)||b<0)&&(b=0),a>0&&b<=0)return $("#tdCashMsg").text('Enter the amount paid into "Amount Tendered" ').show("fade"),!1;if(a>0&&b<a)return $("#tdCashMsg").text("Amount tendered is not enough ").show("fade"),!1}return!0}function showReceipt(a,b,c,d){var e=$(a),f=$("<div id='print_button' style='margin-left:1em;'>Print</div>"),g={mode:"popup",popClose:!1,popHt:500,popWd:400,popX:200,popY:200,popTitle:c};void 0!==d&&d||(d=550),e.children().remove(),e.append($(b).addClass("ReceiptArea").css("max-width",d+"px")),f.button(),f.click(function(){$(".ReceiptArea").printArea(g),e.dialog("close")}),e.prepend(f),e.dialog("option","title",c),e.dialog("option","buttons",{}),e.dialog("option","width",d),e.dialog("open"),g.popHt=$("#pmtRcpt").height(),g.popWd=$("#pmtRcpt").width()}function reprintReceipt(a,b){$.post("ws_ckin.php",{cmd:"getPrx",pid:a},function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}a.error&&(a.gotopage&&window.location.assign(a.gotopage),flagAlertMessage(a.error,!0)),showReceipt(b,a.receipt,"Receipt Copy")}})}function cardOnFile(a,b){var c={cmd:"cof",idGuest:a,idGrp:b,pbp:"register.php"};$("#tblupCredit").find("input").each(function(){this.checked&&(c[$(this).attr("id")]=$(this).val())}),$.post("ws_ckin.php",c,function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}if(a.error)return a.gotopage&&window.location.assign(a.gotopage),void flagAlertMessage(a.error,!0);if(a.hostedError&&flagAlertMessage(a.hostedError,!0),a.xfer){var b=$("#xform");if(b.children("input").remove(),b.prop("action",a.xfer),a.paymentId&&""!=a.paymentId)b.append($('<input type="hidden" name="PaymentID" value="'+a.paymentId+'"/>'));else{if(!a.cardId||""==a.cardId)return void flagAlertMessage("PaymentId and CardId are missing!",!0);b.append($('<input type="hidden" name="CardID" value="'+a.cardId+'"/>'))}b.submit()}a.success&&""!=a.success&&flagAlertMessage(a.success,!1)}})}function updateCredit(a,b,c,d){var e={Continue:function(){cardOnFile(a,b),$(this).dialog("close")},Cancel:function(){$(this).dialog("close")}},f="";c&&""!=c&&(f=" - "+c),$.post("ws_ckin.php",{cmd:"viewCredit",idGuest:a,reg:b},function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}if(a.error)a.gotopage&&window.location.assign(a.gotopage),flagAlertMessage(a.error,!0);else if(a.success){var b=$("#"+d);b.children().remove(),b.append($('<div class="hhk-panel hhk-tdbox hhk-visitdialog"/>').append($(a.success))),b.dialog("option","buttons",e),b.dialog("option","width",400),b.dialog("option","title","Card On File"+f),b.dialog("open")}}})}var gblAdjustData=[],payCtrls=function(){var a=this;a.keyDepAmt=$("#keyDepAmt"),a.keyDepCb=$("#keyDepRx"),a.visitFeeAmt=$("#visitFeeAmt"),a.visitFeeCb=$("#visitFeeCb"),a.feePayAmt=$("input#feesPayment"),a.feesCharges=$("#feesCharges"),a.totalPayment=$("#totalPayment"),a.totalCharges=$("#totalCharges"),a.cashTendered=$("#txtCashTendered"),a.invoiceCb=$(".hhk-payInvCb"),a.adjustBtn=$("#paymentAdjust"),a.msg=$("#payChooserMsg"),a.heldAmtTb=$("#heldAmount"),a.heldCb=$("#cbHeld"),a.hsDiscAmt=$("#HsDiscAmount"),a.depRefundAmt=$("#DepRefundAmount"),a.finalPaymentCb=$("input#cbFinalPayment"),a.overPay=$("#txtOverPayAmt"),a.guestCredit=$("#guestCredit"),a.selBalTo=$("#selexcpay")};
