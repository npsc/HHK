var gblAdjustData=[];function getApplyDiscDiag(e,a){"use strict";e&&""!=e&&0!=e?$.post("ws_ckin.php",{cmd:"getHPay",ord:e,arrDate:$("#spanvArrDate").text()},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)e.gotopage&&window.location.assign(e.gotopage),flagAlertMessage(e.error,"error");else if(e.markup){a.children().remove();var t={Save:function(){var e=parseFloat($("#housePayment").val().replace("$","").replace(",","")),t=$("#housePayment").data("vid"),a=$.datepicker.formatDate("yy-mm-dd",$("#housePaymentDate").datepicker("getDate")),s=$("#housePaymentNote").val();isNaN(e)&&(e=0),saveDiscountPayment(t,$("#cbAdjustPmt1").prop("checked")?$("#cbAdjustPmt1").data("item"):$("#cbAdjustPmt2").data("item"),e,$("#selHouseDisc").val(),$("#selAddnlChg").val(),a,s),$(this).dialog("close")},Cancel:function(){$(this).dialog("close")}};a.append($('<div class="hhk-panel hhk-tdbox hhk-visitdialog" style="font-size:0.8em;"/>').append($(e.markup))),$("#cbAdjustType").buttonset(),$("#cbAdjustPmt1, #cbAdjustPmt2").change(function(){var e=$(this).data("hid"),t=$(this).data("sho");$("#"+e).val(""),$("#"+t).val(""),$("#housePayment").val(""),$(this).prop("checked")?($("#"+t).show(),$("#"+e).hide()):($("#"+e).hide(),$("#"+t).show())}),gblAdjustData.disc=e.disc,gblAdjustData.addnl=e.addnl,$("#selAddnlChg, #selHouseDisc").change(function(){var e=gblAdjustData[$(this).data("amts")];$("#housePayment").val(e[$(this).val()])}),0<$("#cbAdjustPmt1").length?($("#cbAdjustPmt1").prop("checked",!0),$("#cbAdjustPmt1").change()):($("#cbAdjustPmt2").prop("checked",!0),$("#cbAdjustPmt2").change()),a.dialog("option","buttons",t),a.dialog("option","title","Adjust Fees"),a.dialog("option","width",400),a.dialog("open")}}}):flagAlertMessage("Order Number is missing","error")}function saveDiscountPayment(e,t,a,s,i,r,o){"use strict";$.post("ws_ckin.php",{cmd:"saveHPay",ord:e,item:t,amt:a,dsc:s,chg:i,adjDate:r,notes:o},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}e.error&&(e.gotopage&&window.location.assign(e.gotopage),flagAlertMessage(e.error,"error")),e.reply&&""!=e.reply&&(flagAlertMessage(e.reply,"success"),$("#keysfees").dialog("close")),e.receipt&&""!==e.receipt&&(0<$("#keysfees").length&&$("#keysfees").dialog("close"),showReceipt("#pmtRcpt",e.receipt,"Payment Receipt"))}})}function getInvoicee(e,t){"use strict";var a=parseInt(e.id,10);!1===isNaN(a)&&0<a?($("#txtInvName").val(e.value),$("#txtInvId").val(a)):($("#txtInvName").val(""),$("#txtInvId").val("")),$("#txtOrderNum").val(t),$("#txtInvSearch").val("")}function invoiceAction(e,t,a,s,i){"use strict";$.post("ws_resc.php",{cmd:"invAct",iid:e,x:a,action:t,sbt:i},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage(e.error,"error");if(e.delete&&("0"==e.eid?(flagAlertMessage(e.delete,"success"),$("#btnInvGo").click()):$("#"+e.eid).parents("tr").first().hide("fade")),e.markup){var t=$(e.markup);null!=s&&""!=s?$(s).append(t):$("body").append(t),t.position({my:"left top",at:"left bottom",of:"#"+e.eid})}}})}function sendVoidReturn(e,t,a,s){var i={pid:a,bid:e};t&&"v"===t?i.cmd="void":t&&"rv"===t?i.cmd="revpmt":t&&"r"===t?(i.cmd="rtn",i.amt=s):t&&"vr"===t?i.cmd="voidret":t&&"d"===t&&(i.cmd="delWaive",i.iid=s),$.post("ws_ckin.php",i,function(e){var t="";if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.bid&&$("#"+e.bid).remove(),e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage(e.error,"error");if(e.reversal&&""!==e.reversal&&(t=e.reversal),e.warning)return void flagAlertMessage(t+e.warning,"warning");e.success&&flagAlertMessage(t+e.success,"success"),e.receipt&&showReceipt("#pmtRcpt",e.receipt,"Receipt")}})}var chgRoomList,payCtrls=function(){var e=this;e.keyDepAmt=$("#keyDepAmt"),e.keyDepCb=$("#keyDepRx"),e.visitFeeAmt=$("#visitFeeAmt"),e.visitFeeCb=$("#visitFeeCb"),e.feePayAmt=$("input#feesPayment"),e.feesCharges=$("#feesCharges"),e.totalPayment=$("#totalPayment"),e.totalCharges=$("#totalCharges"),e.cashTendered=$("#txtCashTendered"),e.invoiceCb=$(".hhk-payInvCb"),e.adjustBtn=$("#paymentAdjust"),e.msg=$("#payChooserMsg"),e.heldAmtTb=$("#heldAmount"),e.heldCb=$("#cbHeld"),e.hsDiscAmt=$("#HsDiscAmount"),e.depRefundAmt=$("#DepRefundAmount"),e.finalPaymentCb=$("input#cbFinalPayment"),e.overPay=$("#txtOverPayAmt"),e.guestCredit=$("#guestCredit"),e.selBalTo=$("#selexcpay")};function amtPaid(){"use strict";var e=new payCtrls,t=0,a=0,s=0,i="",r=0,o=0,n=0,d=0,l=0,h=0,c=0,p=0,g=0,m=0,v=0,u=isCheckedOut;e.msg.text("").hide(),0<e.visitFeeCb.length&&(a=parseFloat($("#spnvfeeAmt").data("amt")),isNaN(a)||a<0||!1===e.visitFeeCb.prop("checked")?(a=0,e.visitFeeAmt.val("")):e.visitFeeAmt.val(a.toFixed(2).toString())),!u&&0<e.keyDepCb.length&&(t=parseFloat($("#spnDepAmt").data("amt")),isNaN(t)||t<0||!1===e.keyDepCb.prop("checked")?(t=0,e.keyDepAmt.val("")):e.keyDepAmt.val(t.toFixed(2).toString())),0<e.invoiceCb.length&&e.invoiceCb.each(function(){var e,t=parseInt($(this).data("invnum")),a=$("#"+t+"invPayAmt"),s=parseFloat($(this).data("invamt"));!0===$(this).prop("checked")?(a.prop("disabled",!1),""===a.val()&&a.val(s.toFixed(2).toString()),e=parseFloat(a.val().replace("$","").replace(",","")),isNaN(e)||0==e?(e=0,a.val("")):Math.abs(e)>Math.abs(s)&&(e=s,a.val(e.toFixed(2).toString())),o+=e):""!==a.val()&&(a.val(""),a.prop("disabled",!0))}),0<e.feePayAmt.length&&(i=e.feePayAmt.val().replace("$","").replace(",",""),s=parseFloat(i),(isNaN(s)||s<0)&&(e.feePayAmt.val(""),s=0)),0<e.feesCharges.length&&(r=parseFloat(e.feesCharges.val()),isNaN(r)&&(r=0)),0<e.guestCredit.length&&(m=parseFloat(e.guestCredit.val()),isNaN(m)&&(m=0)),0<e.depRefundAmt.length&&(g=parseFloat(e.depRefundAmt.val()),isNaN(g)&&(g=0)),0<e.heldCb.length&&(d=parseFloat(e.heldCb.data("amt")),(isNaN(d)||d<0)&&(d=0),e.heldCb.prop("checked")&&(n=d)),l=a+t+r+o+m+g,u||(l+=s),0<l&&0<n?l<n&&!u?(n=l,l=0):l-=n:l<0&&0<n?l-=n:0===l&&0<n&&u?l-=n:0<e.heldCb.length&&e.heldAmtTb.val(""),u?($(".hhk-minPayment").show("fade"),p=l<0?l-s:l+s,l-s<=0?($(".hhk-HouseDiscount").hide(),e.hsDiscAmt.val(""),e.finalPaymentCb.prop("checked",!1),v=0-(l-s),"r"===e.selBalTo.val()?0<=l?(s!==l&&alert("Pay Room Fees amount is reduced to: $"+l.toFixed(2).toString()),s=l,v=0,e.selBalTo.val(""),$("#txtRtnAmount").val(""),$("#divReturnPay").hide()):(0<s&&alert("Pay Room Fees amount is reduced to: $0.00"),v-=s,s=0,$("#divReturnPay").show("fade"),$("#txtRtnAmount").val(v.toFixed(2).toString())):($("#txtRtnAmount").val(""),$("#divReturnPay").hide()),p=s,0<v?$(".hhk-Overpayment").show("fade"):$(".hhk-Overpayment").hide()):($(".hhk-Overpayment").hide(),v=0,p=e.finalPaymentCb.prop("checked")?((c=l-s)<=0?(c=0,e.hsDiscAmt.val("")):e.hsDiscAmt.val((0-c).toFixed(2).toString()),s):(e.hsDiscAmt.val(""),a+t+o+s),$(".hhk-HouseDiscount").show("fade"))):($(".hhk-Overpayment").hide(),$(".hhk-HouseDiscount").hide(),e.hsDiscAmt.val(""),v=0,p=l,h=a+t+o+s),0<p||p<0&&!u?($(".paySelectTbl").show("fade"),$(".hhk-minPayment").show("fade"),p<0&&!u&&$("#txtRtnAmount").val((0-p).toFixed(2).toString())):(p=0,$(".paySelectTbl").hide(),!1===u&&0===h?($(".hhk-minPayment").hide(),n=0):$(".hhk-minPayment").show("fade")),0===s&&""===i?e.feePayAmt.val(""):e.feePayAmt.val(s.toFixed(2).toString()),0===v?e.overPay.val(""):e.overPay.val(v.toFixed(2).toString()),0<n?e.heldAmtTb.val((0-n).toFixed(2).toString()):e.heldAmtTb.val(""),e.totalCharges.val(l.toFixed(2).toString()),e.totalPayment.val(p.toFixed(2).toString()),$("#spnPayAmount").text("$"+p.toFixed(2).toString()),e.cashTendered.change()}function setupPayments(i,t,o,a,s,e){"use strict";var r=$("#PayTypeSel"),n=$(".tblCredit"),d=new payCtrls;0===n.length&&(n=$(".hhk-mcred")),0<r.length&&(r.change(function(){$(".hhk-cashTndrd").hide(),$(".hhk-cknum").hide(),$("#tblInvoice").hide(),$(".hhk-transfer").hide(),$(".hhk-tfnum").hide(),n.hide(),$("#tdCashMsg").hide(),$(".paySelectNotes").show(),"cc"===$(this).val()?n.show("fade"):"ck"===$(this).val()?$(".hhk-cknum").show("fade"):"in"===$(this).val()?($("#tblInvoice").show("fade"),$(".paySelectNotes").hide()):"tf"===$(this).val()?$(".hhk-transfer").show("fade"):$(".hhk-cashTndrd").show("fade")}),r.change());var l=$("#rtnTypeSel"),h=$(".tblCreditr");0===h.length&&(h=$(".hhk-mcredr")),0<l.length&&(l.change(function(){h.hide(),$(".hhk-transferr").hide(),$(".payReturnNotes").show(),$(".hhk-cknum").hide(),"cc"===$(this).val()?h.show("fade"):"ck"===$(this).val()?$(".hhk-cknum").show("fade"):"tf"===$(this).val()?$(".hhk-transferr").show("fade"):"in"===$(this).val()&&$(".payReturnNotes").hide()}),l.change()),0<d.selBalTo.length&&d.selBalTo.change(function(){amtPaid()}),0<d.finalPaymentCb.length&&d.finalPaymentCb.change(function(){amtPaid()}),0<d.keyDepCb.length&&d.keyDepCb.change(function(){amtPaid()}),0<d.heldCb.length&&d.heldCb.change(function(){amtPaid()}),0<d.invoiceCb.length&&(d.invoiceCb.change(function(){amtPaid()}),$(".hhk-payInvAmt").change(function(){amtPaid()})),0<d.visitFeeCb.length&&d.visitFeeCb.change(function(){amtPaid()}),0<d.feePayAmt.length&&d.feePayAmt.change(function(){$(this).removeClass("ui-state-error"),amtPaid()}),0<d.cashTendered.length&&d.cashTendered.change(function(){d.cashTendered.removeClass("ui-state-highlight"),$("#tdCashMsg").hide();var e=parseFloat(d.totalPayment.val().replace(",",""));(isNaN(e)||e<0)&&(e=0);var t=parseFloat(d.cashTendered.val().replace("$","").replace(",",""));(isNaN(t)||t<0)&&(t=0,d.cashTendered.val(""));var a=t-e;a<0&&(a=0,d.cashTendered.addClass("ui-state-highlight")),$("#txtCashChange").text("$"+a.toFixed(2).toString())}),i&&t&&0<t.length&&(chgRoomList=i,$("table#moveTable").on("change","select",function(){$(this).removeClass("ui-state-error");var e=$(this).val();if(""==e&&(e=0),0<d.keyDepAmt.length&&i[e]&&(0===i[e].key?($("#spnDepAmt").data("amt",""),$("#spnDepAmt").text(""),d.keyDepAmt.val(""),d.keyDepCb.prop("checked",!1),$(".hhk-kdrow").hide()):($("#spnDepAmt").data("amt",i[e].key),$("#spnDepAmt").text("($"+i[e].key+")"),d.keyDepAmt.val(i[e].key),$(".hhk-kdrow").show("fade")),amtPaid()),0<e&&i[e]&&0<$("#myRescId").length){$("#rmChgMsg").text("").hide(),$("#rmDepMessage").text("").hide();var t=$("#myRescId").data("idresc"),a=$("#myRescId").data("pmdl");if(i[t].rate!==i[e].rate&&"b"===a&&$("#rmChgMsg").text("The room rate is different.").show("fade"),i[t].key!==i[e].key){var s="";$("#spnDepMsg").hide(),$("#selDepDisposition").show("fade"),0==i[e].key?"0"!=$("#kdPaid").data("amt")&&(s="There is no deposit for this room.  Set the Deposit Status (above) accordingly."):s="The deposit for this room is $"+i[e].key.toFixed(2).toString(),$("#rmDepMessage").text(s).show("fade")}else $("#selDepDisposition").hide(),$("#spnDepMsg").show("fade")}o.change()}),t.change(),$("#resvChangeDate").datepicker("option","onClose",function(e){$("#rbReplaceRoomnew").prop("checked",!0),""!==e&&getVisitRoomList(a,s,$("#resvChangeDate").val(),t)})),0<d.adjustBtn.length&&(d.adjustBtn.button(),d.adjustBtn.click(function(){getApplyDiscDiag(a,e)})),$("#divPmtMkup").on("click",".invAction",function(e){e.preventDefault(),("del"!=$(this).data("stat")||confirm("Delete this Invoice?"))&&invoiceAction($(this).data("iid"),$(this).data("stat"),e.target.id,"#keysfees",!0)}),0<$("#txtInvSearch").length&&($("#txtInvSearch").keypress(function(e){var t=$(this).val();"13"==e.keyCode&&(""!=t&&isNumber(parseInt(t,10))?$.getJSON("../house/roleSearch.php",{cmd:"filter",basis:"ba",letters:t},function(e){try{e=e[0]}catch(e){return void alert("Parser error - "+e.message)}e&&e.error&&(e.gotopage&&(response(),window.open(e.gotopage)),e.value=e.error),getInvoicee(e,a)}):(alert("Don't press the return key unless you enter an Id."),e.preventDefault()))}),createAutoComplete($("#txtInvSearch"),3,{cmd:"filter",basis:"ba"},function(e){getInvoicee(e,a)},!1)),$("#daystoPay").change(function(){var e=parseInt($(this).val()),t=parseInt($(this).data("vid")),a=parseFloat($("#txtFixedRate").val()),s=parseInt($("#spnNumGuests").text()),i=d.feePayAmt;isNaN(s)&&(s=1),isNaN(a)&&(a=0);var r=parseFloat($("#txtadjAmount").val());isNaN(r)&&(r=0),isNaN(e)?$(this).val(""):0<e&&daysCalculator(e,o.val(),t,a,r,s,0,function(e){i.val(e.toFixed(2).toString()),i.change()})}),amtPaid()}function getVisitRoomList(e,t,a,s){s.prop("disabled",!0),$("#hhk-roomChsrtitle").addClass("hhk-loading"),$("#rmDepMessage").text("").hide();var i={cmd:"chgRoomList",idVisit:e,span:t,chgDate:a,selRescId:s.val()};$.post("ws_ckin.php",i,function(e){var t;s.prop("disabled",!1),$("#hhk-roomChsrtitle").removeClass("hhk-loading");try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.open(e.gotopage),void flagAlertMessage(e.error,"error");e.resc&&(chgRoomList=e.resc),e.sel&&(t=$(e.sel),s.children().remove(),t.children().appendTo(s),s.val(e.idResc).change())})}function daysCalculator(e,t,a,s,i,r,o,n){if(0<e){var d={cmd:"rtcalc",vid:a,rid:o,nites:e,rcat:t,fxd:s,adj:i,gsts:r};$.post("ws_ckin.php",d,function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.open(e.gotopage),void flagAlertMessage(e.error,"error");if(e.amt){var t=parseFloat(e.amt);(isNaN(t)||t<0)&&(t=0),n(t)}}else alert("Bad Reply from Server")})}}function verifyBalDisp(){return""==$("#selexcpay").val()&&""!=$("#txtOverPayAmt").val()?($("#payChooserMsg").text('Set "Apply To" to the desired overpayment disposition. ').show(),$("#selexcpay").addClass("ui-state-highlight"),$("#pWarnings").text('Set "Apply To" to the desired overpayment disposition.').show(),!1):($("#payChooserMsg").text("").hide(),$("#selexcpay").removeClass("ui-state-highlight"),!0)}function verifyAmtTendrd(){"use strict";if(0===$("#PayTypeSel").length)return!0;if($("#tdCashMsg").hide("fade"),$("#tdInvceeMsg").text("").hide(),"ca"===$("#PayTypeSel").val()){var e=parseFloat($("#totalPayment").val().replace("$","").replace(",","")),t=parseFloat($("#txtCashTendered").val().replace("$","").replace(",","")),a=$("#remtotalPayment");if(0<a.length&&(e=parseFloat(a.val().replace("$","").replace(",",""))),(isNaN(e)||e<0)&&(e=0),(isNaN(t)||t<0)&&(t=0),0<e&&t<=0)return $("#tdCashMsg").text('Enter the amount paid into "Amount Tendered" ').show(),$("#pWarnings").text('Enter the amount paid into "Amount Tendered"').show(),!1;if(0<e&&t<e)return $("#tdCashMsg").text("Amount tendered is not enough ").show("fade"),$("#pWarnings").text("Amount tendered is not enough").show(),!1}else if("in"===$("#PayTypeSel").val()){var s=parseInt($("#txtInvId").val(),10);if(isNaN(s)||s<1)return $("#tdInvceeMsg").text("The Invoicee is missing. ").show("fade"),!1}return!0}function showReceipt(e,t,a,s){var i=$(e),r=$("<div id='print_button' style='margin-left:1em;'>Print</div>"),o={mode:"popup",popClose:!1,popHt:500,popWd:400,popX:200,popY:200,popTitle:a};void 0!==s&&s||(s=550),i.children().remove(),i.append($(t).addClass("ReceiptArea").css("max-width",s+"px")),r.button(),r.click(function(){$(".ReceiptArea").printArea(o),i.dialog("close")}),i.prepend(r),i.dialog("option","title",a),i.dialog("option","buttons",{}),i.dialog("option","width",s),i.dialog("open"),o.popHt=$("#pmtRcpt").height(),o.popWd=$("#pmtRcpt").width()}function reprintReceipt(e,t){$.post("ws_ckin.php",{cmd:"getPrx",pid:e},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}e.error&&(e.gotopage&&window.location.assign(e.gotopage),flagAlertMessage(e.error,"error")),showReceipt(t,e.receipt,"Receipt Copy")}})}function paymentRedirect(e,t){"use strict";if(e)if(e.hostedError)flagAlertMessage(e.hostedError,"error");else if(e.cvtx)window.location.assign(e.cvtx);else if(e.xfer&&0<t.length){if(t.children("input").remove(),t.prop("action",e.xfer),e.paymentId&&""!=e.paymentId)t.append($('<input type="hidden" name="PaymentID" value="'+e.paymentId+'"/>'));else{if(!e.cardId||""==e.cardId)return void flagAlertMessage("PaymentId and CardId are missing!","error");t.append($('<input type="hidden" name="CardID" value="'+e.cardId+'"/>'))}t.submit()}else e.inctx&&($("#contentDiv").empty().append($("<p>Processing Credit Payment...</p>")),InstaMed.launch(e.inctx),$("#instamed").css("visibility","visible").css("margin-top","50px;"))}function cardOnFile(e,t,a){var s={cmd:"cof",idGuest:e,idGrp:t,pbp:a};$("#tblupCredit").find("input").each(function(){this.checked&&(s[$(this).attr("id")]=$(this).val())}),$.post("ws_ckin.php",s,function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage(e.error,"error");e.hostedError&&flagAlertMessage(e.hostedError,"error"),paymentRedirect(e,$("#xform")),e.success&&""!=e.success&&flagAlertMessage(e.success,"success"),e.COFmkup&&""!==e.COFmkup&&($("#tblupCredit").remove(),$("#upCreditfs").append($(e.COFmkup)))}})}function updateCredit(s,i,e,r,t){var o="";e&&""!=e&&(o=" - "+e),$.post("ws_ckin.php",{cmd:"viewCredit",idGuest:s,reg:i,pbp:t},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}e.error&&(e.gotopage&&window.location.assign(e.gotopage),flagAlertMessage(e.error,"error"));var t={Continue:function(){cardOnFile(s,i,e.pbp),$(this).dialog("close")},Cancel:function(){$(this).dialog("close")}};if(e.success){var a=$("#"+r);a.children().remove(),a.append($('<div class="hhk-panel hhk-tdbox hhk-visitdialog"/>').append($(e.success))),a.dialog("option","buttons",t),a.dialog("option","width",400),a.dialog("option","title","Card On File"+o),a.dialog("open")}}})}
