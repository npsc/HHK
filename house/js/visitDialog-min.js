function setupVisitNotes(e,t){return t.notesViewer({linkId:e,linkType:"visit",newNoteAttrs:{id:"taNewVNote",name:"taNewVNote"},alertMessage:function(e,t){flagAlertMessage(e,t)}}),t}var isCheckedOut=!1;function viewVisit(c,l,g,u,p,f,e){"use strict";$.post("ws_ckin.php",{cmd:"visitFees",idVisit:l,idGuest:c,action:p,span:f,ckoutdt:e},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage?void window.location.assign(e.gotopage):void flagAlertMessage(e.error,"error");var o=$("#keysfees");if(o.children().remove(),o.append($('<div class="hhk-panel hhk-tdbox hhk-visitdialog" style="font-size:0.8em;"/>').append($(e.success))),o.find(".ckdate").datepicker({yearRange:"-07:+01",changeMonth:!0,changeYear:!0,autoSize:!0,numberOfMonths:1,maxDate:0,dateFormat:"M d, yy",onSelect:function(){this.lastShown=(new Date).getTime()},beforeShow:function(){var e=(new Date).getTime();return void 0===this.lastShown||500<e-this.lastShown},onClose:function(){$(this).change()}}),o.find(".ckdateFut").datepicker({yearRange:"-02:+01",changeMonth:!0,changeYear:!0,autoSize:!0,numberOfMonths:1,minDate:0,dateFormat:"M d, yy",onSelect:function(){this.lastShown=(new Date).getTime()},beforeShow:function(){var e=(new Date).getTime();return void 0===this.lastShown||500<e-this.lastShown},onClose:function(){$(this).change()}}),o.css("background-color","#fff"),"ref"===p&&o.css("background-color","#FEFF9B"),0<$(".hhk-extVisitSw").length&&($(".hhk-extVisitSw").change(function(){this.checked?$(".hhk-extendVisit").show("fade"):$(".hhk-extendVisit").hide("fade")}),$(".hhk-extVisitSw").change()),0<$("#rateChgCB").length){var t=$("#chgRateDate");t.datepicker({changeMonth:!0,changeYear:!0,autoSize:!0,numberOfMonths:1,dateFormat:"M d, yy",maxDate:new Date(e.end),minDate:new Date(e.start)}),t.change(function(){""!==this.value&&t.siblings("input#rbReplaceRate").prop("checked",!0)}),$("input#rbReplaceRate").change(function(){this.checked&&""===t.val()?t.val($.datepicker.formatDate("M d, yy",new Date)):t.val("")}),$("#rateChgCB").change(function(){this.checked?($(".changeRateTd").show(),$("#showRateTd").hide("fade")):($(".changeRateTd").hide("fade"),$("#showRateTd").show())}),$("#rateChgCB").change()}$("#spnExPay").hide(),isCheckedOut=!1;var r=0,d=0;if(0<$("#spnCfBalDue").length&&(r=parseFloat($("#spnCfBalDue").data("bal")),d=parseFloat($("#spnCfBalDue").data("vfee")),r-=d),0<$("input.hhk-ckoutCB").length)$("#tblStays").on("change","input.hhk-ckoutCB",function(){var t=!0,a=1,i=new Date;if(!1===this.checked?$(this).next().val(""):""===$(this).next().val()&&$(this).next().val($.datepicker.formatDate("M d, yy",new Date)),$("input.hhk-ckoutCB").each(function(){if(!1===this.checked)t=!1;else if(""!=$(this).next().val()){var e=new Date($(this).next().val());e.getTime()>i.getTime()?($(this).next().val(""),t=!1):e.getTime()>a&&(a=e.getTime())}}),!0===t){isCheckedOut=!0;var e=(i=new Date).getFullYear()+"-"+i.getMonth()+"-"+i.getDate(),n=new Date(a),s=n.getFullYear()+"-"+n.getMonth()+"-"+n.getDate();if(n.getTime()>i.getTime())return!1;if(e!==s&&"ref"!==p)return o.children().remove(),o.dialog("option","buttons",{}),o.append($('<div class="hhk-panel hhk-tdbox hhk-visitdialog"/>').append($('<div class="ui-autocomplete-loading" style="width:5em;">Loading</div>'))),void viewVisit(c,l,g,u,"ref",f,n.toDateString());$(".hhk-kdrow").hide("fade"),$(".hhk-finalPayment").show("fade");var h=parseFloat($("#kdPaid").data("amt"));isNaN(h)&&(h=0),0<h?($("#DepRefundAmount").val((0-h).toFixed(2).toString()),$(".hhk-refundDeposit").show("fade")):($("#DepRefundAmount").val(""),$(".hhk-refundDeposit").hide("fade")),r<0?($("#guestCredit").val(r.toFixed(2).toString()),$("#feesCharges").val(""),$(".hhk-RoomCharge").hide(),$(".hhk-GuestCredit").show(),0<$("#visitFeeCb").length&&Math.abs(r)>=d&&$("#visitFeeCb").prop("checked",!0).prop("disabled",!0)):($("#feesCharges").val(r.toFixed(2).toString()),$("#guestCredit").val(""),$(".hhk-GuestCredit").hide(),$(".hhk-RoomCharge").show()),$("input#cbFinalPayment").change()}else{if("ref"===p)return o.children().remove(),o.dialog("option","buttons",{}),o.append($('<div class="hhk-panel hhk-tdbox hhk-visitdialog"/>').append($('<div class="ui-autocomplete-loading" style="width:5em;">Loading</div>'))),void viewVisit(c,l,g,u,"",f);isCheckedOut=!1,$(".hhk-finalPayment").hide("fade"),$(".hhk-GuestCredit").hide(),$(".hhk-RoomCharge").hide(),$("#feesCharges").val(""),$("#guestCredit").val(""),$(".hhk-refundDeposit").hide("fade"),$("#DepRefundAmount").val(""),$("input#cbFinalPayment").prop("checked",!1),$("input#cbFinalPayment").change()}}),$("#tblStays").on("change","input.hhk-ckoutDate",function(){""!=$(this).val()?$(this).prev().prop("checked",!0):$(this).prev().prop("checked",!1);$("input.hhk-ckoutCB").change()}),$("#cbCoAll").button().click(function(){$("input.hhk-ckoutCB").each(function(){$(this).prop("checked",!0)}),$("input.hhk-ckoutCB").change()}),$("input.hhk-ckoutCB").change();else if(0<$("#cbFinalPayment").length){isCheckedOut=!0,$(".hhk-finalPayment").show();var a=parseFloat($("#kdPaid").data("amt"));isNaN(a)?($("#DepRefundAmount").val(""),$(".hhk-refundDeposit").hide("fade")):($("#DepRefundAmount").val((0-a).toFixed(2).toString()),$(".hhk-refundDeposit").show("fade")),r<0?($("#guestCredit").val(r.toFixed(2).toString()),$("#feesCharges").val(""),$(".hhk-RoomCharge").hide(),$(".hhk-GuestCredit").show()):($("#feesCharges").val(r.toFixed(2).toString()),$("#guestCredit").val(""),$(".hhk-GuestCredit").hide(),$(".hhk-RoomCharge").show()),o.css("background-color","#F2F2F2")}setupPayments(e.resc,$("#selResource"),$("#selRateCategory"),l,f,$("#pmtRcpt"));var i=$("#btnFapp");0<i.length&&(i.button(),i.click(function(){getIncomeDiag(i.data("rid"))})),0<$("#btnAddGuest").length&&($("#btnAddGuest").button(),$("#btnAddGuest").click(function(){window.location.assign("CheckingIn.php?vid="+$(this).data("vid")+"&span="+$(this).data("span")+"&rid="+$(this).data("rid")+"&vstatus="+$(this).data("vstatus"))})),0<$("#selRateCategory").length&&($("#selRateCategory").change(function(){$(this).val()==fixedRate?($(".hhk-fxFixed").show("fade"),$(".hhk-fxAdj").hide("fade")):($(".hhk-fxFixed").hide("fade"),$(".hhk-fxAdj").show("fade"))}),$("#selRateCategory").change()),setupVisitNotes(l,o.find("#visitNoteViewer")),o.dialog("option","buttons",g),o.dialog("option","title",u),o.dialog("option","width",.92*$(window).width()),o.dialog("option","height",$(window).height()),o.dialog("open")}})}function saveFees(e,t,a,i,n){"use strict";var s=[],h=[],o=!1,r={cmd:"saveFees",idGuest:e,idVisit:t,span:a,rtntbl:!0===i?"1":"0",pbp:n};if($("input.hhk-expckout").each(function(){var e=$(this).attr("id").split("_");0<e.length&&(r[e[0]+"["+e[1]+"]"]=$(this).val())}),$("input.hhk-stayckin").each(function(){var e=$(this).attr("id").split("_");0<e.length&&(r[e[0]+"["+e[1]+"]"]=$(this).val())}),0<$("#undoCkout").length&&$("#undoCkout").prop("checked")&&(o=!0),(!isCheckedOut||!1!==verifyBalDisp()||!1!==o)&&!1!==verifyAmtTendrd()){if($("input.hhk-ckoutCB").each(function(){if(this.checked){var e=$(this).attr("id").split("_");if(0<e.length){r["stayActionCb["+e[1]+"]"]="on";var t=$("#stayCkOutDate_"+e[1]).datepicker("getDate");if(t){var a=new Date;t.setHours(a.getHours()),t.setMinutes(a.getMinutes())}else t=new Date;0<$("#stayCkOutHour_"+e[1]).length&&(r["stayCkOutHour["+e[1]+"]"]=$("#stayCkOutHour_"+e[1]).val()),r["stayCkOutDate["+e[1]+"]"]=t.toJSON(),s.push($(this).data("nm")+", "+t.toDateString())}}}),$("input.hhk-removeCB").each(function(){if(this.checked){var e=$(this).attr("id").split("_");0<e.length&&(r[e[0]+"["+e[1]+"]"]="on",h.push($(this).data("nm")))}}),0<s.length){var d="Check Out:\n"+s.join("\n");if("1"===$("#EmptyExtend").val()&&$("#extendCb").prop("checked")&&s.length>=$("#currGuests").val()&&(d+="\nand extend the visit for "+$("#extendDays").val()+" days"),!1===confirm(d+"?"))return void $("#keysfees").dialog("close")}if(0<h.length&&!1===confirm("Remove:\n"+h.join("\n")+"?"))$("#keysfees").dialog("close");else{if($("#keyDepAmt").removeClass("ui-state-highlight"),0<$("#resvResource").length&&"0"!=$("#resvResource").val()){$("#resvChangeDate").removeClass("ui-state-highlight"),$("#chgmsg").text("");var c=$('<span id="chgmsg"/>');if(""==$("#resvChangeDate").val())return c.text("Enter a change room date."),c.css("color","red"),$("#moveTable").prepend($("<tr/>").append($('<td colspan="2">').append(c))),void $("#resvChangeDate").addClass("ui-state-highlight");var l=$("#resvChangeDate").datepicker("getDate");if(!l)return c.text("Something wrong with the change room date."),c.css("color","red"),$("#moveTable").prepend($("<tr/>").append($('<td colspan="2">').append(c))),void $("#resvChangeDate").addClass("ui-state-highlight");if(l>new Date)return c.text("Change room date can't be in the future."),c.css("color","red"),$("#moveTable").prepend($("<tr/>").append($('<td colspan="2">').append(c))),void $("#resvChangeDate").addClass("ui-state-highlight");if(!1===confirm("Change Rooms?"))return void $("#keysfees").dialog("close")}0<$("#taNewVNote").length&&""!==$("#taNewVNote").val()&&(r.taNewVNote=$("#taNewVNote").val()),$(".hhk-feeskeys").each(function(){if("checkbox"===$(this).attr("type"))!1!==this.checked&&(r[$(this).attr("id")]="on");else if($(this).hasClass("ckdate")){var e=$(this).datepicker("getDate");r[$(this).attr("id")]=e?e.toJSON():""}else"radio"===$(this).attr("type")?!1!==this.checked&&(r[$(this).attr("id")]=this.value):r[$(this).attr("id")]=this.value}),$("#keysfees").css("background-color","white"),$("#keysfees").empty().append('<div id="hhk-loading-spinner" style="width: 100%; height: 100%; margin-top: 100px; text-align: center"><img src="../images/ui-anim_basic_16x16.gif"><p>Working...</p></div>'),$.post("ws_ckin.php",r,function(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage(e.error,"error");if($("#keysfees").dialog("close"),paymentRedirect(e,$("#xform")),"undefined"!=typeof refreshdTables&&refreshdTables(e),"undefined"!=typeof pageManager){var t={date1:new Date($("#gstDate").val()),date2:new Date($("#gstCoDate").val())};pageManager.doOnDatesChange(t)}e.success&&""!==e.success&&(flagAlertMessage(e.success,"success"),0<$("#calendar").length&&$("#calendar").fullCalendar("refetchEvents")),e.receipt&&""!==e.receipt&&showReceipt("#pmtRcpt",e.receipt,"Payment Receipt"),e.invoiceNumber&&""!==e.invoiceNumber&&window.open("ShowInvoice.php?invnum="+e.invoiceNumber)})}}}function updateVisitMessage(e,t){$("#h3VisitMsgHdr").text(e),$("#spnVisitMsg").text(t),$("#visitMsg").effect("pulsate")}
