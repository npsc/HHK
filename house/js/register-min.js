function isNumber(e){"use strict";return!isNaN(parseFloat(e))&&isFinite(e)}function setRoomTo(e,t){$.post("ws_resv.php",{cmd:"moveResvRoom",rid:e,idResc:t},function(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage(e.error,"error");e.warning&&""!==e.warning?flagAlertMessage(e.warning,"alert"):(e.msg&&""!==e.msg&&flagAlertMessage(e.msg,"info"),$("#calendar").fullCalendar("refetchEvents"),refreshdTables(e))})}var $dailyTbl,isGuestAdmin,pmtMkup,rctMkup,defaultTab,resourceGroupBy,resourceColumnWidth,patientLabel,challVar,defaultView,calDateIncrement,dateFormat,fixedRate,resvPageName,showCreatedDate,expandResources,shoHospitalName,showRateCol,hospTitle,showDiags,showLocs,locationTitle,diagnosisTitle,showWlNotes,showCharges,wlTitle,cgCols,rvCols,wlCols,dailyCols;function refreshdTables(e){"use strict";e.curres&&0<$("#divcurres").length&&$("#curres").DataTable().ajax.reload();e.reservs&&0<$("div#vresvs").length&&$("#reservs").DataTable().ajax.reload();e.waitlist&&0<$("div#vwls").length&&$("#waitlist").DataTable().ajax.reload();e.unreserv&&0<$("div#vuncon").length&&$("#unreserv").DataTable().ajax.reload();0<$("#daily").length&&$dailyTbl&&$dailyTbl.ajax.reload()}function cgResvStatus(e,t){$.post("ws_ckin.php",{cmd:"rvstat",rid:e,stat:t},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage(e.error,"error");e.success&&(flagAlertMessage(e.success,"info"),$("#calendar").fullCalendar("refetchEvents")),refreshdTables(e)}})}function chgRoomCleanStatus(e,t){"use strict";confirm("Change the room status?")&&$.post("ws_resc.php",{cmd:"saveRmCleanCode",idr:e,stat:t},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage("Server error - "+e.error,"error");refreshdTables(e),e.msg&&""!=e.msg&&flagAlertMessage(e.msg,"info")}})}function payFee(e,t,a,i){viewVisit(t,a,{"Show Statement":function(){window.open("ShowStatement.php?vid="+a,"_blank")},"Pay Fees":function(){saveFees(t,a,i,!1,"register.php")},Cancel:function(){$(this).dialog("close")}},"Pay Fees for "+e,"pf",i)}function editPSG(e){var a={Cancel:function(){$(this).dialog("close")}};$.post("ws_ckin.php",{cmd:"viewPSG",psg:e},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)e.gotopage&&window.location.assign(e.gotopage),flagAlertMessage(e.error,"error");else if(e.markup){var t=$("div#keysfees");t.children().remove(),t.append($('<div class="hhk-panel hhk-tdbox hhk-visitdialog" style="font-size:0.8em;"/>').append($(e.markup))),t.dialog("option","buttons",a),t.dialog("option","title","View Patient Support Group"),t.dialog("option","width",900),t.dialog("open")}}})}function ckOut(e,t,a,i){viewVisit(t,a,{"Show Statement":function(){window.open("ShowStatement.php?vid="+a,"_blank")},"Show Registration Form":function(){window.open("ShowRegForm.php?vid="+a+"&span="+i,"_blank")},"Check Out":function(){saveFees(t,a,i,!0,"register.php")},Cancel:function(){$(this).dialog("close")}},"Check Out "+e,"co",i)}function editVisit(e,t,a,i){viewVisit(t,a,{"Show Statement":function(){window.open("ShowStatement.php?vid="+a,"_blank")},"Show Registration Form":function(){window.open("ShowRegForm.php?vid="+a+"&span="+i,"_blank")},Save:function(){saveFees(t,a,i,!0,"register.php")},Cancel:function(){$(this).dialog("close")}},"Edit Visit #"+a+"-"+i,"",i)}function getStatusEvent(a,i,e){"use strict";$.post("ws_resc.php",{cmd:"getStatEvent",tp:i,title:e,id:a},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)e.gotopage&&window.location.assign(e.gotopage),alert("Server error - "+e.error);else if(e.tbl){$("#statEvents").children().remove().end().append($(e.tbl)),$(".ckdate").datepicker({autoSize:!0,dateFormat:"M d, yy"});var t={Save:function(){saveStatusEvent(a,i)},Cancel:function(){$(this).dialog("close")}};$("#statEvents").dialog("option","buttons",t),$("#statEvents").dialog("open")}}})}function saveStatusEvent(e,t){"use strict";$.post("ws_resc.php",$("#statForm").serialize()+"&cmd=saveStatEvent&id="+e+"&tp="+t,function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}e.error&&(e.gotopage&&window.location.assign(e.gotopage),alert("Server error - "+e.error)),e.reload&&1==e.reload&&($("#calendar").fullCalendar("refetchResources"),$("#calendar").fullCalendar("refetchEvents")),e.msg&&""!=e.msg&&flagAlertMessage(e.msg,"info")}$("#statEvents").dialog("close")})}function cgRoom(e,t,a,i){viewVisit(t,a,{"Change Rooms":function(){saveFees(t,a,i,!0,"register.php")},Cancel:function(){$(this).dialog("close")}},"Change Rooms for "+e,"cr",i)}function moveVisit(e,t,a,i,s){$.post("ws_ckin.php",{cmd:e,idVisit:t,span:a,sdelta:i,edelta:s},function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}e.error?(e.gotopage&&window.location.assign(e.gotopage),flagAlertMessage(e.error,"error")):e.success&&($("#calendar").fullCalendar("refetchEvents"),flagAlertMessage(e.success,"success"),refreshdTables(e))}})}function getRoomList(e,t){e&&$.post("ws_ckin.php",{cmd:"rmlist",rid:e,x:t},function(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e.error)return e.gotopage&&window.location.assign(e.gotopage),void flagAlertMessage(e.error,"error");if(e.container){var t=$(e.container);$("body").append(t),t.position({my:"top",at:"bottom",of:"#"+e.eid}),$("#selRoom").change(function(){""!=$("#selRoom").val()&&confirm("Change room to "+$("#selRoom option:selected").text()+"?")&&setRoomTo(e.rid,$("#selRoom").val()),t.remove()})}})}$(document).ready(function(){"use strict";var e,i=0,a=new moment;isGuestAdmin=$("#isGuestAdmin").val(),pmtMkup=$("#pmtMkup").val(),rctMkup=$("#rctMkup").val(),defaultTab=$("#defaultTab").val(),resourceGroupBy=$("#resourceGroupBy").val(),resourceColumnWidth=$("#resourceColumnWidth").val(),patientLabel=$("#patientLabel").val(),challVar=$("#challVar").val(),defaultView=$("#defaultView").val(),calDateIncrement=$("#calDateIncrement").val(),dateFormat=$("#dateFormat").val(),fixedRate=$("#fixedRate").val(),resvPageName=$("#resvPageName").val(),showCreatedDate=$("#showCreatedDate").val(),expandResources=$("#expandResources").val(),shoHospitalName=$("#shoHospitalName").val(),showRateCol=$("#showRateCol").val(),hospTitle=$("#hospTitle").val(),showDiags=$("#showDiags").val(),showLocs=$("#showLocs").val(),locationTitle=$("#locationTitle").val(),diagnosisTitle=$("#diagnosisTitle").val(),showWlNotes=$("#showWlNotes").val(),wlTitle=$("#wlTitle").val(),showCharges=$("#showCharges").val(),cgCols=[{data:"Action",title:"Action",sortable:!1,searchable:!1},{data:"Guest First",title:"Guest First"},{data:"Guest Last",title:"Guest Last"},{data:"Checked In",title:"Checked In",render:function(e,t){return dateRender(e,t,dateFormat)}},{data:"Nights",title:"Nights",className:"hhk-justify-c"},{data:"Expected Departure",title:"Expected Departure",render:function(e,t){return dateRender(e,t,dateFormat)}},{data:"Room",title:"Room",className:"hhk-justify-c"}],showRateCol&&cgCols.push({data:"Rate",title:"Rate"}),cgCols.push({data:"Phone",title:"Phone"}),shoHospitalName&&cgCols.push({data:"Hospital",title:hospTitle}),cgCols.push({data:"Patient",title:patientLabel}),rvCols=[{data:"Action",title:"Action",sortable:!1,searchable:!1},{data:"Guest First",title:"Guest First"},{data:"Guest Last",title:"Guest Last"},{data:"Expected Arrival",title:"Expected Arrival",render:function(e,t){return dateRender(e,t,dateFormat)}},{data:"Nights",title:"Nights",className:"hhk-justify-c"},{data:"Expected Departure",title:"Expected Departure",render:function(e,t){return dateRender(e,t,dateFormat)}},{data:"Room",title:"Room",className:"hhk-justify-c"}],showRateCol&&rvCols.push({data:"Rate",title:"Rate"}),rvCols.push({data:"Occupants",title:"Occupants",className:"hhk-justify-c"}),shoHospitalName&&rvCols.push({data:"Hospital",title:hospTitle}),showLocs&&rvCols.push({data:"Location",title:locationTitle}),showDiags&&rvCols.push({data:"Diagnosis",title:diagnosisTitle}),rvCols.push({data:"Patient",title:patientLabel}),wlCols=[{data:"Action",title:"Action",sortable:!1,searchable:!1},{data:"Guest First",title:"Guest First"},{data:"Guest Last",title:"Guest Last"}],showCreatedDate&&wlCols.push({data:"Timestamp",title:"Created On",render:function(e,t){return dateRender(e,t,"MMM D, YYYY H:mm")}}),wlCols.push({data:"Expected Arrival",title:"Expected Arrival",render:function(e,t){return dateRender(e,t,dateFormat)}}),wlCols.push({data:"Nights",title:"Nights",className:"hhk-justify-c"}),wlCols.push({data:"Expected Departure",title:"Expected Departure",render:function(e,t){return dateRender(e,t,dateFormat)}}),wlCols.push({data:"Occupants",title:"Occupants",className:"hhk-justify-c"}),shoHospitalName&&wlCols.push({data:"Hospital",title:hospTitle}),showLocs&&wlCols.push({data:"Location",title:locationTitle}),showDiags&&wlCols.push({data:"Diagnosis",title:diagnosisTitle}),wlCols.push({data:"Patient",title:patientLabel}),showWlNotes&&wlCols.push({data:"WL Notes",title:wlTitle}),dailyCols=[{data:"titleSort",visible:!1},{data:"Title",title:"Room",orderData:[0,1],className:"hhk-justify-c"},{data:"Status",title:"Status",searchable:!1},{data:"Guests",title:"Guests"},{data:"Patient_Name",title:patientLabel}],showCharges&&dailyCols.push({data:"Unpaid",title:"Unpaid",className:"hhk-justify-r"}),dailyCols.push({data:"Visit_Notes",title:"Last Visit Note",sortable:!1}),dailyCols.push({data:"Notes",title:"Room Notes",sortable:!1}),$.widget("ui.autocomplete",$.ui.autocomplete,{_resizeMenu:function(){var e=this.menu.element;e.outerWidth(1.1*Math.max(e.width("").outerWidth()+1,this.element.outerWidth()))}}),""!==pmtMkup&&$("#paymentMessage").html(pmtMkup).show("pulsate",{},400),$(':input[type="button"], :input[type="submit"]').button(),$.datepicker.setDefaults({yearRange:"-10:+02",changeMonth:!0,changeYear:!0,autoSize:!0,numberOfMonths:2,dateFormat:"M d, yy"}),$.extend($.fn.dataTable.defaults,{dom:'<"dtTop"if>rt<"dtBottom"lp><"clear">',displayLength:50,lengthMenu:[[25,50,-1],[25,50,"All"]],order:[[3,"asc"]],processing:!0,deferRender:!0}),$("#vstays").on("click",".stpayFees",function(e){e.preventDefault(),$(".hhk-alert").hide(),payFee($(this).data("name"),$(this).data("id"),$(this).data("vid"),$(this).data("spn"))}),$("#vstays").on("click",".applyDisc",function(e){e.preventDefault(),$(".hhk-alert").hide(),getApplyDiscDiag($(this).data("vid"),$("#pmtRcpt"))}),$("#vstays").on("click",".stckout",function(e){e.preventDefault(),$(".hhk-alert").hide(),ckOut($(this).data("name"),$(this).data("id"),$(this).data("vid"),$(this).data("spn"))}),$("#vstays").on("click",".stvisit",function(e){e.preventDefault(),$(".hhk-alert").hide(),editVisit($(this).data("name"),$(this).data("id"),$(this).data("vid"),$(this).data("spn"))}),$("#vstays").on("click",".hhk-getPSGDialog",function(e){e.preventDefault(),$(".hhk-alert").hide(),editPSG($(this).data("psg"))}),$("#vstays").on("click",".stchgrooms",function(e){e.preventDefault(),$(".hhk-alert").hide(),cgRoom($(this).data("name"),$(this).data("id"),$(this).data("vid"),$(this).data("spn"))}),$("#vstays").on("click",".stcleaning",function(e){e.preventDefault(),$(".hhk-alert").hide(),chgRoomCleanStatus($(this).data("idroom"),$(this).data("clean"))}),$("#vresvs, #vwls, #vuncon").on("click",".resvStat",function(e){e.preventDefault(),$(".hhk-alert").hide(),cgResvStatus($(this).data("rid"),$(this).data("stat"))}),$(".ckdate").datepicker(),$("#statEvents").dialog({autoOpen:!1,resizable:!0,width:830,modal:!0,title:"Manage Status Events"}),$("#keysfees").dialog({autoOpen:!1,resizable:!0,modal:!0,close:function(e,t){$("div#submitButtons").show()},open:function(e,t){$("div#submitButtons").hide()}}),$("#keysfees").mousedown(function(e){var t=$(e.target);"pudiv"!==t[0].id&&0===t.parents("#pudiv").length&&$("div#pudiv").remove()}),$("#faDialog").dialog({autoOpen:!1,resizable:!0,width:650,modal:!0,title:"Income Chooser"}),$("#setBillDate").dialog({autoOpen:!1,resizable:!0,modal:!0,title:"Set Invoice Billing Date"}),$("#pmtRcpt").dialog({autoOpen:!1,resizable:!0,width:530,modal:!0,title:"Payment Receipt"}),""===$("#txtactstart").val()&&((e=new Date).setTime(e.getTime()-432e6),$("#txtactstart").datepicker("setDate",e));""===$("#txtfeestart").val()&&((e=new Date).setTime(e.getTime()-2592e5),$("#txtfeestart").datepicker("setDate",e));$("#txtsearch").keypress(function(e){var t=$(this).val();"13"==e.keyCode&&(""!==t&&isNumber(parseInt(t,10))?0<t&&window.location.assign("GuestEdit.php?id="+t):alert("Don't press the return key unless you enter an Id."),e.preventDefault())}),createAutoComplete($("#txtsearch"),3,{cmd:"role",mode:"mo",gp:"1"},function(e){var t=e.id;0<t&&window.location.assign("GuestEdit.php?id="+t)},!1);var t=null;0<calDateIncrement&&calDateIncrement<5&&(t={weeks:calDateIncrement}),$("#selRoomGroupScheme").val(resourceGroupBy);var s=window.innerHeight;$("#calendar").fullCalendar({height:s-175,themeSystem:"jquery-ui",allDay:!0,firstDay:0,dateIncrement:t,nextDayThreshold:"13:00",schedulerLicenseKey:"CC-Attribution-NonCommercial-NoDerivatives",customButtons:{refresh:{text:"Refresh",click:function(){$("#calendar").fullCalendar("refetchResources"),$("#calendar").fullCalendar("refetchEvents")}},prevMonth:{click:function(){$("#calendar").fullCalendar("incrementDate",{months:-1})},themeIcon:"ui-icon-seek-prev"},nextMonth:{click:function(){$("#calendar").fullCalendar("incrementDate",{months:1})},themeIcon:"ui-icon-seek-next"},setup:{click:function(){$("#divRoomGrouping").show("fade")},themeIcon:"ui-icon-gear"}},views:{timeline1weeks:{type:"timeline",slotDuration:{days:1},duration:{weeks:1},buttonText:"1"},timeline2weeks:{type:"timeline",slotDuration:{days:1},duration:{weeks:2},buttonText:"2"},timeline3weeks:{type:"timeline",slotDuration:{days:1},duration:{weeks:3},buttonText:"3"},timeline4weeks:{type:"timeline",slotDuration:{days:7},duration:{weeks:26},buttonText:"26"}},viewRender:function(e,t){defaultView=e.name,a=$("#calendar").fullCalendar("getDate")},header:{left:"setup timeline1weeks,timeline2weeks,timeline3weeks,timeline4weeks title",center:"",right:"refresh,today prevMonth,prev,next,nextMonth"},defaultView:defaultView,editable:!0,resourcesInitiallyExpanded:expandResources,resourceLabelText:"Rooms",resourceAreaWidth:resourceColumnWidth,refetchResourcesOnNavigate:!1,resourceGroupField:resourceGroupBy,loading:function(e,t){e?($("#pCalLoad").show(),$("#spnGotoDate").hide()):($("#pCalLoad").hide(),$("#spnGotoDate").show())},resources:function(t){$.ajax({url:"ws_calendar.php",dataType:"JSON",data:{cmd:"resclist",start:a.format("YYYY-MM-DD"),view:defaultView},success:function(e){t(e)},error:function(e,t,a){$("#pCalError").text("Error getting resources: "+a).show()}})},resourceGroupText:function(e){return e},resourceRender:function(e,t,a){if(t.css("background",e.bgColor).css("color",e.textColor),0<e.id){var i=e.title+(0==e.maxOcc?"":" ("+e.maxOcc+")");t.prop("title",i),t.click(function(){getStatusEvent(e.id,"resc",e.title)})}},eventOverlap:function(e,t){return"bak"===e.kind||e.idVisit===t.idVisit},events:{url:"ws_calendar.php?cmd=eventlist",error:function(e,t,a){$("#pCalError").text("Error getting events: "+a).show()}},eventDrop:function(e,t,a){if($(".hhk-alert").hide(),0<e.idVisit&&0!==t.asDays()&&confirm("Move Visit to a new start date?")&&moveVisit("visitMove",e.idVisit,e.Span,t.asDays(),t.asDays()),0<e.idReservation){if(0<t.asDays()&&(e.resourceId,e.idResc),0!==t.asDays()&&confirm("Move Reservation to a new start date?"))return void moveVisit("reservMove",e.idReservation,0,t.asDays(),t.asDays());if(e.resourceId!==e.idResc&&confirm("Move Reservation to a new room?"))return void setRoomTo(e.idReservation,e.resourceId)}a()},eventResize:function(e,t,a){$(".hhk-alert").hide(),void 0!==t?0<e.idVisit&&confirm("Move check out date?")?moveVisit("visitMove",e.idVisit,e.Span,0,t.asDays()):0<e.idReservation&&confirm("Move expected end date?")?moveVisit("reservMove",e.idReservation,0,0,t.asDays()):a():a()},eventClick:function(e,t){if($(".hhk-alert").hide(),e.kind&&"oos"===e.kind)getStatusEvent(e.resourceId,"resc",e.title);else{if(e.idReservation&&0<e.idReservation){if(t.target.classList.contains("hhk-schrm"))return void getRoomList(e.idReservation,t.target.id);window.location.assign(resvPageName+"?rid="+e.idReservation)}if(e.idVisit&&0<e.idVisit){var a={"Show Statement":function(){window.open("ShowStatement.php?vid="+e.idVisit,"_blank")},"Show Registration Form":function(){window.open("ShowRegForm.php?vid="+e.idVisit+"&span="+e.Span,"_blank")},Save:function(){saveFees(0,e.idVisit,e.Span,!0,"register.php")},Cancel:function(){$(this).dialog("close")}};viewVisit(0,e.idVisit,a,"Edit Visit #"+e.idVisit+"-"+e.Span,"",e.Span)}}},eventRender:function(e,t){if(void 0===i||0===i||void 0===e.idHosp||e.idAssoc==i||e.idHosp==i){var a=$("#calendar").fullCalendar("getResourceById",e.resourceId);void 0!==e.idReservation?(t.prop("title",e.fullName+(0<e.resourceId?", Room: "+a.title:"")+", Status: "+e.resvStatus+(shoHospitalName?", Hospital: "+e.hospName:"")),"uc"===e.status?t.css("border","2px dashed black").css("padding","1px 0"):t.css("border","2px solid black").css("padding","1px 0")):void 0!==e.idVisit?(t.prop("title",e.fullName+", Room: "+a.title+", Status: "+e.visitStatus+", "+e.guests+(1<e.guests?" guests":" guest")+(shoHospitalName?", Hospital: "+e.hospName:"")),void 0!==e.extended&&e.extended&&t.find("div.fc-content").append($('<span style="float:right;margin-right:5px;" class="hhk-fc-title"/>'))):"oos"===e.kind&&t.prop("title",e.reason),t.show()}else t.hide()}}),$(document).mousedown(function(e){var t=$(e.target);"divRoomGrouping"!==t[0].id&&"selRoomGroupScheme"!==t[0].id&&$("#divRoomGrouping").hide()}),0<$(".spnHosp").length&&$(".spnHosp").click(function(){$(".hhk-alert").hide(),$(".spnHosp").css("border","solid 1px black").css("font-size","100%"),i=parseInt($(this).data("id"),10),isNaN(i)&&(i=0),$("#calendar").fullCalendar("rerenderEvents"),$(this).css("border","solid 3px black").css("font-size","120%")}),$("#btnActvtyGo").click(function(){$(".hhk-alert").hide();var e=$("#txtactstart").datepicker("getDate");if(null===e)return $("#txtactstart").addClass("ui-state-highlight"),void flagAlertMessage("Enter start date","alert");$("#txtactstart").removeClass("ui-state-highlight");var t=$("#txtactend").datepicker("getDate");null===t&&(t=new Date);var a={cmd:"actrpt",start:e.toJSON(),end:t.toJSON()};$("#cbVisits").prop("checked")&&(a.visit="on"),$("#cbReserv").prop("checked")&&(a.resv="on"),$("#cbHospStay").prop("checked")&&(a.hstay="on"),$.post("ws_resc.php",a,function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}e.error?(e.gotopage&&window.open(e.gotopage,"_self"),flagAlertMessage(e.error,"error")):e.success&&($("#rptdiv").remove(),$("#vactivity").append($('<div id="rptdiv"/>').append($(e.success))),$(".hhk-viewvisit").css("cursor","pointer"),$("#rptdiv").on("click",".hhk-viewvisit",function(){if($(this).data("visitid")){var e=$(this).data("visitid").split("_");if(2!==e.length)return;var t={Save:function(){saveFees(0,e[0],e[1])},Cancel:function(){$(this).dialog("close")}};viewVisit(0,e[0],t,"View Visit","n",e[1])}else $(this).data("reservid")&&window.location.assign("Referral.php?id="+$(this).data("reservid"))}))}})}),$("#btnFeesGo").click(function(){$(".hhk-alert").hide();var e=$("#txtfeestart").datepicker("getDate");if(null===e)return $("#txtfeestart").addClass("ui-state-highlight"),void flagAlertMessage("Enter start date","alert");$("#txtfeestart").removeClass("ui-state-highlight");var t=$("#txtfeeend").datepicker("getDate");null===t&&(t=new Date);var a=$("#selPayStatus").val()||[],i=$("#selPayType").val()||[],s={cmd:"actrpt",start:e.toJSON(),end:t.toJSON(),st:a,pt:i};!1!==$("#fcbdinv").prop("checked")&&(s.sdinv="on"),$("#rptFeeLoading").show(),s.fee="on",$.post("ws_resc.php",s,function(e){if($("#rptFeeLoading").hide(),e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}e.error?(e.gotopage&&window.open(e.gotopage,"_self"),flagAlertMessage(e.error,"error")):e.success&&($("#rptfeediv").remove(),$("#vfees").append($('<div id="rptfeediv"/>').append($(e.success))),paymentsTable("feesTable","rptfeediv"))}})}),$("#btnInvGo").click(function(){var e={cmd:"actrpt",st:["up"],inv:"on"};$.post("ws_resc.php",e,function(e){if(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}e.error?(e.gotopage&&window.open(e.gotopage,"_self"),flagAlertMessage(e.error,"error")):e.success&&($("#rptInvdiv").remove(),$("#vInv").append($('<div id="rptInvdiv" style="min-height:500px;"/>').append($(e.success))),$("#rptInvdiv .gmenu").menu(),$("#rptInvdiv").on("click",".invLoadPc",function(e){e.preventDefault(),$("#divAlert1, #paymentMessage").hide(),invLoadPc($(this).data("name"),$(this).data("id"),$(this).data("iid"))}),$("#rptInvdiv").on("click",".invSetBill",function(e){e.preventDefault(),$(".hhk-alert").hide(),invSetBill($(this).data("inb"),$(this).data("name"),"div#setBillDate","#trBillDate"+$(this).data("inb"),$("#trBillDate"+$(this).data("inb")).text(),$("#divInvNotes"+$(this).data("inb")).text(),"#divInvNotes"+$(this).data("inb"))}),$("#rptInvdiv").on("click",".invAction",function(e){e.preventDefault(),$(".hhk-alert").hide(),"del"==$(this).data("stat")&&!confirm("Delete this Invoice?")||("vem"!==$(this).data("stat")?(invoiceAction($(this).data("iid"),$(this).data("stat"),e.target.id),$("#rptInvdiv .gmenu").menu("collapse")):window.open("ShowInvoice.php?invnum="+$(this).data("inb")))}),$("#InvTable").dataTable({columnDefs:[{targets:[2,4],type:"date",render:function(e,t,a){return dateRender(e,t)}}],dom:'<"top"if>rt<"bottom"lp><"clear">',displayLength:50,lengthMenu:[[20,50,100,-1],[20,50,100,"All"]],order:[[1,"asc"]]}))}})}),$("#btnPrintRegForm").click(function(){window.open($(this).data("page")+"?d="+$("#regckindate").val(),"_blank")}),$("#btnPrintWL").click(function(){window.open($(this).data("page")+"?d="+$("#regwldate").val(),"_blank")}),$("#btnPrtDaily").button().click(function(){$("#divdaily").printArea()}),$("#btnRefreshDaily").button().click(function(){$("#daily").DataTable().ajax.reload()}),$("#txtGotoDate").change(function(){$(".hhk-alert").hide(),a=new moment($(this).datepicker("getDate")),$("#calendar").fullCalendar("refetchResources"),$("#calendar").fullCalendar("gotoDate",a)}),$("#selRoomGroupScheme").change(function(){$("#divRoomGrouping").hide(),$("#calendar").fullCalendar("option","resourceGroupField",$(this).val()),$("#calendar").fullCalendar("refetchResources")}),""!==rctMkup&&showReceipt("#pmtRcpt",rctMkup,"Payment Receipt"),$("#mainTabs").tabs({beforeActivate:function(e,t){"liInvoice"===t.newTab.prop("id")&&$("#btnInvGo").click(),"liDaylog"!==t.newTab.prop("id")||$dailyTbl||($dailyTbl=$("#daily").DataTable({ajax:{url:"ws_resc.php?cmd=getHist&tbl=daily",dataSrc:"daily"},order:[[0,"asc"]],columns:dailyCols,infoCallback:function(e,t,a,i,s,o){return"Prepared: "+dateRender((new Date).toISOString(),"display","ddd, MMM D YYYY, h:mm a")}}))},activate:function(e,t){"liCal"===t.newTab.prop("id")&&($("#calendar").fullCalendar("render"),$("#divGoto").position({my:"center top",at:"center top+8",of:"#calendar",within:"#calendar"}))},active:defaultTab}),$("#mainTabs").show(),$("#divGoto").position({my:"center top",at:"center top+8",of:"#calendar",within:"#calendar"}),$("#curres").DataTable({ajax:{url:"ws_resc.php?cmd=getHist&tbl=curres",dataSrc:"curres"},drawCallback:function(e){$("#spnNumCurrent").text(this.api().rows().data().length),$("#curres .gmenu").menu()},columns:cgCols}),$("#reservs").DataTable({ajax:{url:"ws_resc.php?cmd=getHist&tbl=reservs",dataSrc:"reservs"},drawCallback:function(e){$("#spnNumConfirmed").text(this.api().rows().data().length),$("#reservs .gmenu").menu()},columns:rvCols}),0<$("#unreserv").length&&$("#unreserv").DataTable({ajax:{url:"ws_resc.php?cmd=getHist&tbl=unreserv",dataSrc:"unreserv"},drawCallback:function(e){$("#spnNumUnconfirmed").text(this.api().rows().data().length),$("#unreserv .gmenu").menu()},columns:rvCols}),$("#waitlist").DataTable({ajax:{url:"ws_resc.php?cmd=getHist&tbl=waitlist",dataSrc:"waitlist"},order:[[showCreatedDate?4:3,"asc"]],drawCallback:function(){$("#spnNumWaitlist").text(this.api().rows().data().length),$("#waitlist .gmenu").menu()},columns:wlCols})});
