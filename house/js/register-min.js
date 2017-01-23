function isNumber(a){"use strict";return!isNaN(parseFloat(a))&&isFinite(a)}function refreshdTables(a){"use strict";var b;a.curres&&$("#divcurres").length>0&&(b=$("#curres").DataTable(),b.ajax.reload()),a.reservs&&$("div#vresvs").length>0&&(b=$("#reservs").DataTable(),b.ajax.reload()),a.waitlist&&$("div#vwls").length>0&&(b=$("#waitlist").DataTable(),b.ajax.reload()),a.unreserv&&$("div#vuncon").length>0&&(b=$("#unreserv").DataTable(),b.ajax.reload())}function cgResvStatus(a,b){$.post("ws_ckin.php",{cmd:"rvstat",rid:a,stat:b},function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}if(a.error)return a.gotopage&&window.location.assign(a.gotopage),void flagAlertMessage(a.error,!0);a.success&&(flagAlertMessage(a.success,!1),$("#calendar").hhkCalendar("refetchEvents")),refreshdTables(a)}})}function sendVoidReturn(a,b,c,d){var e={pid:c,bid:a};b&&"v"===b?e.cmd="void":b&&"rv"===b?e.cmd="revpmt":b&&"r"===b?(e.cmd="rtn",e.amt=d):b&&"vr"===b&&(e.cmd="voidret"),$.post("ws_ckin.php",e,function(a){var b="";if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}if(a.bid&&$("#"+a.bid).remove(),a.error)return a.gotopage&&window.location.assign(a.gotopage),void flagAlertMessage(a.error,!0);if(a.reversal&&""!==a.reversal&&(b=a.reversal),a.warning)return void flagAlertMessage(b+a.warning,!0);a.success&&flagAlertMessage(b+a.success,!1),a.receipt&&showReceipt("#pmtRcpt",a.receipt,"Receipt")}})}function invPay(a,b,c){if(verifyAmtTendrd()!==!1){var d={cmd:"payInv",pbp:b,id:a};$(".hhk-feeskeys").each(function(){if("checkbox"===$(this).attr("type"))this.checked!==!1&&(d[$(this).attr("id")]="on");else if($(this).hasClass("ckdate")){var a=$(this).datepicker("getDate");a?d[$(this).attr("id")]=a.toJSON():d[$(this).attr("id")]=""}else"radio"===$(this).attr("type")?this.checked!==!1&&(d[$(this).attr("id")]=this.value):d[$(this).attr("id")]=this.value}),c.dialog("close"),$.post("ws_ckin.php",d,function(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}a.error&&(a.gotopage&&window.location.assign(a.gotopage),flagAlertMessage(a.error,!0)),paymentReply(a,!1),$("#btnInvGo").click()})}}function invLoadPc(a,b,c){"use strict";var d={"Pay Fees":function(){invPay(b,"register.php",$("div#keysfees"))},Cancel:function(){$(this).dialog("close")}};$.post("ws_ckin.php",{cmd:"showPayInv",id:b,iid:c},function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}a.error?(a.gotopage&&window.location.assign(a.gotopage),flagAlertMessage(a.error,!0)):a.mkup&&($("div#keysfees").children().remove(),$("div#keysfees").append($('<div class="hhk-panel hhk-tdbox hhk-visitdialog" style="font-size:0.8em;"/>').append($(a.mkup))),$("div#keysfees .ckdate").datepicker({yearRange:"-01:+01",changeMonth:!0,changeYear:!0,autoSize:!0,numberOfMonths:1,dateFormat:"M d, yy"}),isCheckedOut=!1,setupPayments(a.resc,"","",0,$("#pmtRcpt")),$("#keysfees").dialog("option","buttons",d),$("#keysfees").dialog("option","title","Pay Invoice"),$("#keysfees").dialog("option","width",700),$("#keysfees").dialog("open"))}})}function invSetBill(a,b,c,d,e,f,g){"use strict";var h=$(c),i={Save:function(){var b,c=h.find("#taBillNotes").val();""!=h.find("#txtBillDate").val()&&(b=h.find("#txtBillDate").datepicker("getDate").toJSON()),$.post("ws_resc.php",{cmd:"invSetBill",inb:a,date:b,ele:d,nts:c,ntele:g},function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}a.error?(a.gotopage&&window.location.assign(a.gotopage),flagAlertMessage(a.error,!0)):a.success&&(a.elemt&&a.strDate&&$(a.elemt).text(a.strDate),a.notesElemt&&a.notes&&$(a.notesElemt).text(a.notes),flagAlertMessage(a.success,!1))}}),$(this).dialog("close")},Cancel:function(){$(this).dialog("close")}};h.find("#spnInvNumber").text(a),h.find("#spnBillPayor").text(b),h.find("#txtBillDate").val(e),h.find("#taBillNotes").val(f),h.find("#txtBillDate").datepicker({numberOfMonths:1}),h.dialog("option","buttons",i),h.dialog("option","width",500),h.dialog("open")}function chgRoomCleanStatus(a,b){"use strict";confirm("Change the room status?")&&$.post("ws_resc.php",{cmd:"saveRmCleanCode",idr:a,stat:b},function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}if(a.error)return a.gotopage&&window.location.assign(a.gotopage),void flagAlertMessage("Server error - "+a.error,!0);refreshdTables(a),a.msg&&""!=a.msg&&flagAlertMessage(a.msg,!1)}})}function payFee(a,b,c,d){var e={"Show Statement":function(){window.open("ShowStatement.php?vid="+c,"_blank")},"Pay Fees":function(){saveFees(b,c,d,!1,"register.php")},Cancel:function(){$(this).dialog("close")}};viewVisit(b,c,e,"Pay Fees for "+a,"pf",d)}function editPSG(a){var b={Cancel:function(){$(this).dialog("close")}};$.post("ws_ckin.php",{cmd:"viewPSG",psg:a},function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}if(a.error)a.gotopage&&window.location.assign(a.gotopage),flagAlertMessage(a.error,!0);else if(a.markup){var c=$("div#keysfees");c.children().remove(),c.append($('<div class="hhk-panel hhk-tdbox hhk-visitdialog" style="font-size:0.8em;"/>').append($(a.markup))),c.dialog("option","buttons",b),c.dialog("option","title","View Patient Support Group"),c.dialog("option","width",900),c.dialog("open")}}})}function ckOut(a,b,c,d){var e={"Show Statement":function(){window.open("ShowStatement.php?vid="+c,"_blank")},"Show Registration Form":function(){window.open("ShowRegForm.php?vid="+c,"_blank")},"Check Out":function(){saveFees(b,c,d,!0,"register.php")},Cancel:function(){$(this).dialog("close")}};viewVisit(b,c,e,"Check Out "+a,"co",d)}function editVisit(a,b,c,d){var e={"Show Statement":function(){window.open("ShowStatement.php?vid="+c,"_blank")},"Show Registration Form":function(){window.open("ShowRegForm.php?vid="+c,"_blank")},Save:function(){saveFees(b,c,d,!0,"register.php")},Cancel:function(){$(this).dialog("close")}};viewVisit(b,c,e,"Edit Visit #"+c+"-"+d,"",d)}function getStatusEvent(a,b,c){"use strict";$.post("ws_resc.php",{cmd:"getStatEvent",tp:b,title:c,id:a},function(c){if(c){try{c=$.parseJSON(c)}catch(a){return void alert("Parser error - "+a.message)}if(c.error)c.gotopage&&window.location.assign(c.gotopage),alert("Server error - "+c.error);else if(c.tbl){$("#statEvents").children().remove().end().append($(c.tbl)),$(".ckdate").datepicker({autoSize:!0,dateFormat:"M d, yy"});var d={Save:function(){saveStatusEvent(a,b)},Cancel:function(){$(this).dialog("close")}};$("#statEvents").dialog("option","buttons",d),$("#statEvents").dialog("open")}}})}function saveStatusEvent(a,b){"use strict";$.post("ws_resc.php",$("#statForm").serialize()+"&cmd=saveStatEvent&id="+a+"&tp="+b,function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}a.error&&(a.gotopage&&window.location.assign(a.gotopage),alert("Server error - "+a.error)),a.reload&&1==a.reload&&$("#calendar").hhkCalendar("refetchEvents"),a.msg&&""!=a.msg&&flagAlertMessage(a.msg,!1)}$("#statEvents").dialog("close")})}function cgRoom(a,b,c,d){var e={"Change Rooms":function(){saveFees(b,c,d,!0,"register.php")},Cancel:function(){$(this).dialog("close")}};viewVisit(b,c,e,"Change Rooms for "+a,"cr",d)}function moveVisit(a,b,c,d,e){$.post("ws_ckin.php",{cmd:a,idVisit:b,span:c,sdelta:d,edelta:e},function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}a.error?(a.gotopage&&window.location.assign(a.gotopage),flagAlertMessage(a.error,!0)):a.success&&($("#calendar").hhkCalendar("refetchEvents"),flagAlertMessage(a.success,!1),refreshdTables(a))}})}function getRoomList(a,b){a&&$.post("ws_ckin.php",{cmd:"rmlist",rid:a,x:b},function(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}if(a.error)return a.gotopage&&window.location.assign(a.gotopage),void flagAlertMessage(a.error,!0);if(a.container){var b=$(a.container);$("body").append(b),b.position({my:"top",at:"bottom",of:"#"+a.eid}),$("#selRoom").change(function(){return""==$("#selRoom").val()?void b.remove():(confirm("Change room to "+$("#selRoom option:selected").text()+"?")&&$.post("ws_ckin.php",{cmd:"setRoom",rid:a.rid,idResc:$("#selRoom").val()},function(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}return a.error?(a.gotopage&&window.location.assign(a.gotopage),void flagAlertMessage(a.error,!0)):(a.msg&&""!=a.msg&&flagAlertMessage(a.msg,!1),$("#calendar").hhkCalendar("refetchEvents"),void refreshdTables(a))}),void b.remove())})}})}function checkStrength(a){var b=new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])(?=.{8,})"),c=new RegExp("^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*[0-9]))|((?=.*[A-Z])(?=.*[0-9])))(?=.{8,})"),d=!0;return b.test(a.val())?a.removeClass("ui-state-error"):c.test(a.val())?a.removeClass("ui-state-error"):(a.addClass("ui-state-error"),d=!1),d}$(document).ready(function(){"use strict";var a=new Date,b="ws_ckin.php",c=b+"?cmd=register",d=0;$.ajaxSetup({beforeSend:function(){$("body").css("cursor","wait")},complete:function(){$("body").css("cursor","auto")},cache:!1}),$("#contentDiv").css("margin-top",$("#global-nav").css("height")),""!==pmtMkup&&$("#paymentMessage").html(pmtMkup).show("pulsate",{},400),$(':input[type="button"], :input[type="submit"]').button(),$.datepicker.setDefaults({yearRange:"-10:+02",changeMonth:!0,changeYear:!0,autoSize:!0,numberOfMonths:2,dateFormat:"M d, yy"}),$("#vstays").on("click",".stpayFees",function(a){a.preventDefault(),$("#divAlert1, #paymentMessage").hide(),payFee($(this).data("name"),$(this).data("id"),$(this).data("vid"),$(this).data("spn"))}),$("#vstays").on("click",".applyDisc",function(a){a.preventDefault(),$("#divAlert1, #paymentMessage").hide(),getApplyDiscDiag($(this).data("vid"),$("#pmtRcpt"))}),$("#vstays, #vresvs, #vwls, #vuncon").on("click",".stupCredit",function(a){a.preventDefault(),$("#divAlert1, #paymentMessage").hide(),updateCredit($(this).data("id"),$(this).data("reg"),$(this).data("name"),"cardonfile")}),$("#vstays").on("click",".stckout",function(a){a.preventDefault(),$("#divAlert1, #paymentMessage").hide(),ckOut($(this).data("name"),$(this).data("id"),$(this).data("vid"),$(this).data("spn"))}),$("#vstays").on("click",".stvisit",function(a){a.preventDefault(),$("#divAlert1, #paymentMessage").hide(),editVisit($(this).data("name"),$(this).data("id"),$(this).data("vid"),$(this).data("spn"))}),$("#vstays").on("click",".hhk-getPSGDialog",function(a){a.preventDefault(),$("#divAlert1, #paymentMessage").hide(),editPSG($(this).data("psg"))}),$("#vstays").on("click",".stchgrooms",function(a){a.preventDefault(),$("#divAlert1, #paymentMessage").hide(),cgRoom($(this).data("name"),$(this).data("id"),$(this).data("vid"),$(this).data("spn"))}),$("#vstays").on("click",".stcleaning",function(a){a.preventDefault(),$("#divAlert1, #paymentMessage").hide(),chgRoomCleanStatus($(this).data("idroom"),$(this).data("clean"))}),$("#vresvs, #vwls, #vuncon").on("click",".resvStat",function(a){a.preventDefault(),$("#divAlert1, #paymentMessage").hide(),cgResvStatus($(this).data("rid"),$(this).data("stat"))}),$.extend($.fn.dataTable.defaults,{dom:'<"top"if>rt<"bottom"lp><"clear">',iDisplayLength:50,aLengthMenu:[[25,50,-1],[25,50,"All"]],order:[[2,"asc"]]}),$("#curres").DataTable({ajax:{url:"ws_resc.php?cmd=getHist&tbl=curres",dataSrc:"curres"},deferRender:!0,drawCallback:function(a){$("#curres .gmenu").menu()},columns:cgCols}),$("#reservs").DataTable({ajax:{url:"ws_resc.php?cmd=getHist&tbl=reservs",dataSrc:"reservs"},drawCallback:function(a){$("#reservs .gmenu").menu()},deferRender:!0,columns:rvCols}),$("#unreserv").length>0&&$("#unreserv").DataTable({ajax:{url:"ws_resc.php?cmd=getHist&tbl=unreserv",dataSrc:"unreserv"},drawCallback:function(a){$("#unreserv .gmenu").menu()},deferRender:!0,columns:rvCols}),$("#waitlist").DataTable({ajax:{url:"ws_resc.php?cmd=getHist&tbl=waitlist",dataSrc:"waitlist"},drawCallback:function(a){$("#waitlist .gmenu").menu()},deferRender:!0,columns:wlCols}),$(".ckdate3").datepicker({onClose:function(a,b){var c=$(this).prop("defaultValue");""!=a&&a!=c&&(changeExptDeparture($(this).data("id"),$(this).data("vid"),a,$(this)),$(this).val($(this).prop("defaultValue")))}}),$("#statEvents").dialog({autoOpen:!1,resizable:!0,width:830,modal:!0,title:"Manage Status Events"}),$("#keysfees").dialog({autoOpen:!1,resizable:!0,modal:!0,close:function(a,b){$("div#submitButtons").show()},open:function(a,b){$("div#submitButtons").hide()}}),$("#keysfees").mousedown(function(a){var b=$(a.target);"pudiv"!==b[0].id&&0===b.parents("#pudiv").length&&$("div#pudiv").remove()}),$("#faDialog").dialog({autoOpen:!1,resizable:!0,width:650,modal:!0,title:"Income Chooser"}),$("#setBillDate").dialog({autoOpen:!1,resizable:!0,modal:!0,title:"Set Invoice Billing Date"}),$("#pmtRcpt").dialog({autoOpen:!1,resizable:!0,width:530,modal:!0,title:"Payment Receipt"}),$("#cardonfile").dialog({autoOpen:!1,resizable:!0,modal:!0,title:"Update Credit Card On File",close:function(a,b){$("div#submitButtons").show()},open:function(a,b){$("div#submitButtons").hide()}}),$(".ckdate").datepicker();$("#mainTabs").tabs({activate:function(a,b){0===b.newTab.index()&&$("#calendar").hhkCalendar("render"),b.newTab.index()==$(this).find(".ui-tabs-nav").children("li").length-1&&$("#btnInvGo").click()}});if(""===$("#txtactstart").val()){var f=new Date;f.setTime(f.getTime()-432e6),$("#txtactstart").datepicker("setDate",f)}if(""===$("#txtfeestart").val()){var f=new Date;f.setTime(f.getTime()-2592e5),$("#txtfeestart").datepicker("setDate",f)}$("#txtsearch").keypress(function(a){var b=$(this).val();"13"==a.keyCode&&(""!==b&&isNumber(parseInt(b,10))?window.location.assign("GuestEdit.php?id="+b):(alert("Don't press the return key unless you enter an Id."),a.preventDefault()))}),createAutoComplete($("#txtsearch"),3,{cmd:"role",mode:"mo"},function(a){var b=a.id;0!==b&&window.location.assign("GuestEdit.php?id="+b)},!1);var g=parseInt(viewDays,10);$("#calendar").hhkCalendar({defaultView:"twoweeks",viewDays:g,hospitalSelector:null,theme:!0,contentHeight:28*parseInt(roomCnt),header:{left:"title",center:"goto",right:"refresh,today prev,next"},allDayDefault:!0,lazyFetching:!0,draggable:!1,editable:!0,selectHelper:!0,selectable:!0,unselectAuto:!0,year:a.getFullYear(),month:a.getMonth(),ignoreTimezone:!0,loading:function(a){a||$("body").css("cursor","auto")},eventSources:[{url:c,ignoreTimezone:!0}],select:function(a,b,c,d,e){},eventDrop:function(a,b,c,d,e,f,g,h){$("#divAlert1, #paymentMessage").hide(),a.idVisit>0&&isGuestAdmin&&confirm("Move Visit to a new start date?")&&moveVisit("visitMove",a.idVisit,a.Span,b,b),a.idReservation>0&&isGuestAdmin&&confirm("Move Reservation to a new start date?")&&moveVisit("reservMove",a.idReservation,a.Span,b,b),e()},eventResize:function(a,b,c,d,e,f,g){$("#divAlert1, #paymentMessage").hide(),a.idVisit>0&&isGuestAdmin&&confirm("Move check out date?")&&moveVisit("visitMove",a.idVisit,a.Span,0,b),a.idReservation>0&&isGuestAdmin&&confirm("Move expected end date?")&&moveVisit("reservMove",a.idReservation,a.Span,0,b),d()},eventClick:function(a,b,c){if($("#divAlert1, #paymentMessage").hide(),a.idResc&&a.idResc>0)return void getStatusEvent(a.idResc,"resc",a.title);if(a.idReservation&&a.idReservation>0){if(b.target.classList.contains("hhk-schrm"))return void getRoomList(a.idReservation,b.target.id);window.location.assign("Referral.php?rid="+a.idReservation)}if(!isNaN(parseInt(a.id,10))){var d={"Show Statement":function(){window.open("ShowStatement.php?vid="+a.idVisit,"_blank")},"Show Registration Form":function(){window.open("ShowRegForm.php?vid="+a.idVisit,"_blank")},Save:function(){saveFees(0,a.idVisit,a.Span,!0,"register.php")},Cancel:function(){$(this).dialog("close")}};viewVisit(0,a.idVisit,d,"Edit Visit #"+a.idVisit+"-"+a.Span,"",a.Span)}},eventRender:function(a,b){return void 0==d||0===d||a.idAssoc==d||a.idHosp==d||0==a.idHosp}}),$(document).mousedown(function(a){var b=$(a.target);"pudiv"!==b[0].id&&0===b.parents("#pudiv").length&&$("div#pudiv").remove()}),$(".spnHosp").length>0&&$(".spnHosp").click(function(){$(".spnHosp").css("border","solid 1px black").css("font-size","100%"),d=parseInt($(this).data("id"),10),isNaN(d)&&(d=0),$("#calendar").hhkCalendar("rerenderEvents"),$(this).css("border","solid 3px black").css("font-size","120%")}),$("#btnActvtyGo").click(function(){$("#divAlert1, #paymentMessage").hide();var a=$("#txtactstart").datepicker("getDate");if(null===a)return $("#txtactstart").addClass("ui-state-highlight"),void flagAlertMessage("Enter start date",!0);$("#txtactstart").removeClass("ui-state-highlight");var b=$("#txtactend").datepicker("getDate");null===b&&(b=new Date);var c={cmd:"actrpt",start:a.toJSON(),end:b.toJSON()};$("#cbVisits").prop("checked")&&(c.visit="on"),$("#cbReserv").prop("checked")&&(c.resv="on"),$("#cbHospStay").prop("checked")&&(c.hstay="on"),$.post("ws_resc.php",c,function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}a.error?(a.gotopage&&window.open(a.gotopage,"_self"),flagAlertMessage(a.error,!0)):a.success&&($("#rptdiv").remove(),$("#vactivity").append($('<div id="rptdiv"/>').append($(a.success))),$(".hhk-viewvisit").css("cursor","pointer"),$("#rptdiv").on("click",".hhk-viewvisit",function(){if($(this).data("visitid")){var a=$(this).data("visitid").split("_");if(2!==a.length)return;var b={Save:function(){saveFees(0,a[0],a[1])},Cancel:function(){$(this).dialog("close")}};viewVisit(0,a[0],b,"View Visit","n",a[1])}else $(this).data("reservid")&&window.location.assign("Referral.php?id="+$(this).data("reservid"))}))}})}),$("#btnFeesGo").click(function(){$("#divAlert1, #paymentMessage").hide();var a=$("#txtfeestart").datepicker("getDate");if(null===a)return $("#txtfeestart").addClass("ui-state-highlight"),void flagAlertMessage("Enter start date",!0);$("#txtfeestart").removeClass("ui-state-highlight");var b=$("#txtfeeend").datepicker("getDate");null===b&&(b=new Date);var c=$("#selPayStatus").val()||[],d=$("#selPayType").val()||[],e={cmd:"actrpt",start:a.toJSON(),end:b.toJSON(),st:c,pt:d};$("#fcbdinv").prop("checked")!==!1&&(e.sdinv="on"),e.fee="on",$.post("ws_resc.php",e,function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}a.error?(a.gotopage&&window.open(a.gotopage,"_self"),flagAlertMessage(a.error,!0)):a.success&&($("#rptfeediv").remove(),$("#vfees").append($('<div id="rptfeediv"/>').append($(a.success))),$("#feesTable").dataTable({dom:'<"top"if>rt<"bottom"lp><"clear">',iDisplayLength:50,aLengthMenu:[[25,50,-1],[25,50,"All"]]}),$("#rptfeediv").on("click",".invAction",function(a){invoiceAction($(this).data("iid"),"view",a.target.id)}),$("#rptfeediv").on("click",".hhk-voidPmt",function(){var a=$(this);"Saving..."!=a.val()&&confirm("Void/Reverse?")&&(a.val("Saving..."),sendVoidReturn(a.attr("id"),"rv",a.data("pid")))}),$("#rptfeediv").on("click",".hhk-voidRefundPmt",function(){var a=$(this);"Saving..."!=a.val()&&confirm("Void this Return?")&&(a.val("Saving..."),sendVoidReturn(a.attr("id"),"vr",a.data("pid")))}),$("#rptfeediv").on("click",".hhk-returnPmt",function(){var a=$(this);if("Saving..."!=a.val()){a.val("Saving...");var b=parseFloat($(this).data("amt"));confirm("Return $"+b.toFixed(2).toString()+"?")&&sendVoidReturn(a.attr("id"),"r",a.data("pid"),b)}}),$("#rptfeediv").on("click",".pmtRecpt",function(){reprintReceipt($(this).data("pid"),"#pmtRcpt")}))}})}),$("#btnInvGo").click(function(){var a=["up"],b={cmd:"actrpt",st:a,inv:"on"};$.post("ws_resc.php",b,function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}a.error?(a.gotopage&&window.open(a.gotopage,"_self"),flagAlertMessage(a.error,!0)):a.success&&($("#rptInvdiv").remove(),$("#vInv").append($('<div id="rptInvdiv" style="min-height:500px;"/>').append($(a.success))),$("#rptInvdiv .gmenu").menu(),$("#rptInvdiv").on("click",".invLoadPc",function(a){a.preventDefault(),$("#divAlert1, #paymentMessage").hide(),invLoadPc($(this).data("name"),$(this).data("id"),$(this).data("iid"))}),$("#rptInvdiv").on("click",".invSetBill",function(a){a.preventDefault(),$("#divAlert1, #paymentMessage").hide(),invSetBill($(this).data("inb"),$(this).data("name"),"div#setBillDate","#trBillDate"+$(this).data("inb"),$("#trBillDate"+$(this).data("inb")).text(),$("#divInvNotes"+$(this).data("inb")).text(),"#divInvNotes"+$(this).data("inb"))}),$("#rptInvdiv").on("click",".invAction",function(a){a.preventDefault(),$("#divAlert1, #paymentMessage").hide(),("del"!=$(this).data("stat")||confirm("Delete this Invoice?"))&&(invoiceAction($(this).data("iid"),$(this).data("stat"),a.target.id),$("#rptInvdiv .gmenu").menu("collapse"))}),$("#InvTable").dataTable({dom:'<"top"if>rt<"bottom"lp><"clear">',iDisplayLength:50,aLengthMenu:[[20,50,100,-1],[20,50,100,"All"]],order:[[1,"asc"]]}))}})}),$("#btnPrintRegForm").click(function(){window.open($(this).data("page")+"?d="+$("#regckindate").val(),"_blank")}),""!==rctMkup&&showReceipt("#pmtRcpt",rctMkup,"Payment Receipt"),$(".gmenu").menu(),$("#version").click(function(){$("div#dchgPw").find("input").removeClass("ui-state-error").val(""),$("#pwChangeErrMsg").text(""),$("#dchgPw").dialog("option","title","Change Your Password"),$("#dchgPw").dialog("open"),$("#txtOldPw").focus()}),$("div#dchgPw").on("change","input",function(){$(this).removeClass("ui-state-error"),$("#pwChangeErrMsg").text("")}),$("#dchgPw").dialog({autoOpen:!1,width:450,resizable:!0,modal:!0,buttons:{Save:function(){var d,e,a=$("#txtOldPw"),b=$("#txtNewPw1"),c=$("#txtNewPw2"),f=$("#pwChangeErrMsg");return""==a.val()?(a.addClass("ui-state-error"),a.focus(),void f.text("Enter your old password")):checkStrength(b)===!1?(b.addClass("ui-state-error"),f.text("Password must have 8 characters including at least one uppercase and one lower case alphabetical character and one number."),void b.focus()):b.val()!==c.val()?void f.text("New passwords do not match"):a.val()==b.val()?(b.addClass("ui-state-error"),f.text("The new password must be different from the old password"),void b.focus()):(d=hex_md5(hex_md5(a.val())+challVar),e=hex_md5(b.val()),a.val(""),b.val(""),c.val(""),void $.post("ws_admin.php",{cmd:"chgpw",old:d,newer:e},function(a){if(a){try{a=$.parseJSON(a)}catch(a){return void alert("Parser error - "+a.message)}a.error?(a.gotopage&&window.open(a.gotopage,"_self"),flagAlertMessage(a.error,!0)):a.success?($("#dchgPw").dialog("close"),flagAlertMessage(a.success,!1)):a.warning&&$("#pwChangeErrMsg").text(a.warning)}}))},Cancel:function(){$(this).dialog("close")}}}),$("#mainTabs").show(),$("#mainTabs").tabs("option","active",defaultTab),$("#calendar").hhkCalendar("render")});
