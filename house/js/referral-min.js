function additionalGuest(e){"use strict";var t=reserv;if(hideAlertMessage(),e.id>0)for(var a=0;a<t.members.length;a++){var i=t.members[a];if(i.idName==e.id&&i.idPrefix==t.patientPrefix&&1==t.patStaying)return void flagAlertMessage("This guest is already added.",!0)}if(confirm("Add "+e.value+"?")){t.addRoom=!1,$("#cbAddnlRoom").prop("checked")&&(t.addRoom=!0);var r={cmd:"addResv",id:e.id,rid:t.idReserv,addRoom:t.addRoom};$.post("ws_ckin.php",r,function(e){var t=reserv;try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}if(e){if(e.error)return e.gotopage&&window.open(e.gotopage,"_self"),void flagAlertMessage(e.error,!0);if($("#txtAddGuest").val(""),e.newRoom&&e.newRoom>1)flagAlertMessage('<a href="Referral.php?rid='+e.newRoom+'">View New Reservation</a>',!1);else{if(e.addr&&(t.addr=e.addr),e.addtguest&&""!==e.addtguest){$("#diagAddGuest").remove();var a=$(e.addtguest);a.css("font-size",".85em"),a.dialog({autoOpen:!1,resizable:!0,width:1e3,modal:!0,title:"Additional Guest",close:function(e,t){$("div#submitButtons").show()},open:function(e,t){$("div#submitButtons").hide()},buttons:{Save:function(){var e=!1;if($("#adgstMsg").text(""),""==$("#bselPatRel").val()?($("#bselPatRel").addClass("ui-state-error"),e=!0):$("#bselPatRel").removeClass("ui-state-error"),""==$("#btxtFirstName").val()?($("#btxtFirstName").addClass("ui-state-error"),e=!0):$("#btxtFirstName").removeClass("ui-state-error"),""==$("#btxtLastName").val()?($("#btxtLastName").addClass("ui-state-error"),e=!0):$("#btxtLastName").removeClass("ui-state-error"),!1===$("#bincomplete").prop("checked")&&$(".bhhk-addr-val").each(function(){""!==$(this).val()||$(this).hasClass("bfh-states")?$(this).removeClass("ui-state-error"):($(this).addClass("ui-state-error"),e=!0)}),e)$("#adgstMsg").text("Fill in missing information");else{e=!1;var a=/^([\(]{1}[0-9]{3}[\)]{1}[\.| |\-]{0,1}|^[0-9]{3}[\.|\-| ]?)?[0-9]{3}(\.|\-| )?[0-9]{4}$/;if($('.hhk-phoneInput[id^="btxtPhone"]').each(function(){""!=$.trim($(this).val())&&!1===a.test($(this).val())&&($(this).addClass("ui-state-error"),e=!0)}),e)return $("#adgstMsg").text("Guest has an invalid phone number.  "),$("#diagAddGuest #bphEmlTabs").tabs("option","active",1),!1;$.post("ws_ckin.php",$("#fAddGuest").serialize()+"&cmd=addResv&rid="+t.idReserv+"&addRoom="+t.addRoom,function(e){if((e=$.parseJSON(e)).error)return e.gotopage&&window.open(e.gotopage,"_self"),void flagAlertMessage(e.error,!0);injectSlot(e)}),$(this).dialog("close")}},Cancel:function(){$(this).dialog("close")}}}),a.find("select.bfh-countries").each(function(){var e=$(this);e.bfhcountries(e.data())}),a.find("select.bfh-states").each(function(){var e=$(this);e.bfhstates(e.data())}),$("#diagAddGuest #bphEmlTabs").tabs(),verifyAddrs("#diagAddGuest");return createZipAutoComplete($("#diagAddGuest input.hhk-zipsearch"),"ws_admin.php",void 0),$("#diagAddGuest").on("click",".hhk-addrCopy",function(){var e=$(this).attr("name");if(t.addr&&""!=t.addr.adraddress1&&$("#"+e+"adraddress1"+t.adrPurpose).val()!=t.addr.adraddress1)return $("#"+e+"adraddress1"+t.adrPurpose).val(t.addr.adraddress1),$("#"+e+"adraddress2"+t.adrPurpose).val(t.addr.adraddress2),$("#"+e+"adrcity"+t.adrPurpose).val(t.addr.adrcity),$("#"+e+"adrcounty"+t.adrPurpose).val(t.addr.adrcounty),$("#"+e+"adrstate"+t.adrPurpose).val(t.addr.adrstate),$("#"+e+"adrcountry"+t.adrPurpose).val(t.addr.adrcountry),void $("#"+e+"adrzip"+t.adrPurpose).val(t.addr.adrzip);if(!(t.members.length<1))for(var a=0;a<t.members.length;a++)t.members[a]&&t.members[a].idPrefix!==e&&($("#"+e+"adraddress1"+t.adrPurpose).val($("#"+t.members[a].idPrefix+"adraddress1"+t.adrPurpose).val()),$("#"+e+"adraddress2"+t.adrPurpose).val($("#"+t.members[a].idPrefix+"adraddress2"+t.adrPurpose).val()),$("#"+e+"adrcity"+t.adrPurpose).val($("#"+t.members[a].idPrefix+"adrcity"+t.adrPurpose).val()),$("#"+e+"adrcounty"+t.adrPurpose).val($("#"+t.members[a].idPrefix+"adrcounty"+t.adrPurpose).val()),$("#"+e+"adrstate"+t.adrPurpose).val($("#"+t.members[a].idPrefix+"adrstate"+t.adrPurpose).val()),$("#"+e+"adrcountry"+t.adrPurpose).val($("#"+t.members[a].idPrefix+"adrcountry"+t.adrPurpose).val()),$("#"+e+"adrzip"+t.adrPurpose).val($("#"+t.members[a].idPrefix+"adrzip"+t.adrPurpose).val()))}),$("#diagAddGuest").on("click",".hhk-addrErase",function(){var e=$(this).attr("name");$("#"+e+"adraddress1"+t.adrPurpose).val(""),$("#"+e+"adraddress2"+t.adrPurpose).val(""),$("#"+e+"adrcity"+t.adrPurpose).val(""),$("#"+e+"adrcounty"+t.adrPurpose).val(""),$("#"+e+"adrstate"+t.adrPurpose).val(""),$("#"+e+"adrcountry"+t.adrPurpose).val(""),$("#"+e+"adrzip"+t.adrPurpose).val(""),$("#"+e+"adrbad"+t.adrPurpose).prop("checked",!1)}),void a.dialog("open")}injectSlot(e)}}else alert("Bad Reply from Server")})}}function delAdditionalGuest(e){"use strict";var t=reserv;if(hideAlertMessage(),confirm("Remove "+e.value+"?")){for(var a=0;a<t.members.length;a++){var i=t.members[a];i&&i.idName===e.id&&t.members.splice(a,1)}$("#spnNumGuests").text(t.members.length),$.post("ws_ckin.php",{cmd:"delResvGst",id:e.id,rid:t.idReserv}).done(function(e){if(e=$.parseJSON(e))return e.error?(e.gotopage&&window.open(e.gotopage,"_self"),void flagAlertMessage(e.error,!0)):void injectSlot(e);alert("Bad Reply from Server")})}}function injectSlot(e){"use strict";var t=reserv;if(e.memMkup&&e.txtHdr){var a,i,r=$("div#guestAccordion"),s=$('<div id="'+e.idPrefix+'divGstpnl" />').append($(e.memMkup));s.addClass("Slot gstdetail");var d=$("<ul id='ulIcons' style='float:right;margin-left:5px;padding-top:1px;' class='ui-widget'/>").append($("<li class='ui-widget-header ui-corner-all' title='Open - Close'>").append($("<span id='"+e.idPrefix+"drpDown' class='ui-icon ui-icon-circle-triangle-n'></span>"))),o=$('<div id="'+e.idPrefix+'divGsthdr" style="padding:2px;"/>').append($(e.txtHdr)).append($(d)).append($('<div style="clear:both;"/>')).click(function(){"none"===s.css("display")?(s.show("blind"),o.removeClass("ui-corner-all").addClass("ui-corner-top"),$("#"+e.idPrefix+"drpDown").removeClass("ui-icon-circle-triangle-s").addClass("ui-icon-circle-triangle-n")):(s.hide("blind"),o.removeClass("ui-corner-top").addClass("ui-corner-all"),$("#"+e.idPrefix+"drpDown").removeClass("ui-icon-circle-triangle-n").addClass("ui-icon-circle-triangle-s"))});o.addClass("ui-widget-header ui-state-default ui-corner-top"),r.children().remove(),r.append(o),r.append(s),s.find("select.bfh-countries").each(function(){var e=$(this);e.bfhcountries(e.data())}),s.find("select.bfh-states").each(function(){var e=$(this);e.bfhstates(e.data())}),$("#"+e.idPrefix+"selPatRel").change(function(){"slf"===$(this).val()||""===$(this).val()?($("div#patientSection").hide("blind"),"slf"===$(this).val()&&(o.removeClass("ui-state-default"),o.find("#pgspnHdrLabel").text((t.patAsGuest?t.patientLabel+"/":"")+"Primary Guest: ")),t.patSection=!1):($("div#patientSection").show("blind"),o.addClass("ui-state-default"),o.find("#pgspnHdrLabel").text("Primary Guest: "),t.patSection=!0)}),$("input.dprange").click(function(t){$("div#dtpkrDialog").toggle("scale, horizontal"),$("#dtpkrDialog").position({my:"left top",at:"left bottom",of:"#"+e.idPrefix+"gstDate"}),t.stopPropagation()}),$(".ckdate").datepicker(),$("#"+e.idPrefix+"phEmlTabs").tabs(),0===e.idName&&($("#"+e.idPrefix+"phEmlTabs").tabs("option","active",1),$("#"+e.idPrefix+"phEmlTabs").tabs("option","disabled",[0])),a=$("#"+e.idPrefix+"gstDate"),(i=$("#"+e.idPrefix+"gstCoDate")).datepicker("destroy"),i.datepicker({minDate:1}),i.click(function(e){e.stopPropagation()}),$("#dtpkrDialog").datepicker("destroy"),$("#dtpkrDialog").datepicker({numberOfMonths:2,minDate:0,beforeShowDay:function(e){var t,r;try{t=$.datepicker.parseDate($.datepicker._defaults.dateFormat,a.val()),r=$.datepicker.parseDate($.datepicker._defaults.dateFormat,i.val())}catch(e){}return[!0,t&&(e.getTime()===t.getTime()||r&&e>=t&&e<=r)?"dp-highlight":""]},onSelect:function(e,t){var r,s;try{r=$.datepicker.parseDate($.datepicker._defaults.dateFormat,a.val()),s=$.datepicker.parseDate($.datepicker._defaults.dateFormat,i.val())}catch(e){}!r||s?(a.val(e),i.val("")):(i.val(e),$("div#dtpkrDialog").hide("fade"))}}),$(document).mousedown(function(e){var t=$(e.target);"dtpkrDialog"!==t[0].id&&0===t.parents("#dtpkrDialog").length&&$("#dtpkrDialog").hide("fade")}),$("#guestSearch").hide()}if(e.notes&&$("#notesGuest").children().remove().end().append($(e.notes)).show(),t.patStaying=e.patStay,e.idPsg&&(t.idPsg=e.idPsg),void 0!==e.hosp){var n=$(e.hosp.div).addClass("ui-widget-content").prop("id","divhospDetail"),d=$("<ul id='ulIcons' style='float:right;margin-left:5px;padding-top:1px;' class='ui-widget'/>").append($("<li class='ui-widget-header ui-corner-all' title='Open - Close'>").append($("<span id='h_drpDown' class='ui-icon ui-icon-circle-triangle-n'></span>"))),l=$('<div id="divhospHdr" style="padding:2px; cursor:pointer;"/>').append($(e.hosp.hdr)).append(d).append('<div style="clear:both;"/>');l.addClass("ui-widget-header ui-state-default ui-corner-top"),l.click(function(){"none"===n.css("display")?(n.show("blind"),l.removeClass("ui-corner-all").addClass("ui-corner-top")):(n.hide("blind"),l.removeClass("ui-corner-top").addClass("ui-corner-all"))}),$("#hospitalSection").children().remove().end().append(l).append(n),$("#txtEntryDate, #txtExitDate").datepicker(),$("#txtAgentSch").length>0&&(createAutoComplete($("#txtAgentSch"),3,{cmd:"filter",basis:"ra"},getAgent),""===$("#a_txtLastName").val()&&$(".hhk-agentInfo").hide()),$("#txtDocSch").length>0&&(createAutoComplete($("#txtDocSch"),3,{cmd:"filter",basis:"doc"},getDoc),""===$("#d_txtLastName").val()&&$(".hhk-docInfo").hide()),$("#hospitalSection").show("blind"),""!==$("#selHospital").val()&&e.rvstCode&&""!==e.rvstCode&&l.click()}if(void 0!==e.patient&&""!=e.patient){var p=$("div#patientSection"),c=$('<div id="h_divGstpnl" />').append($(e.patient)),d=$("<ul id='ulIcons' style='float:right;margin-left:5px;padding-top:1px;' class='ui-widget'/>").append($("<li class='ui-widget-header ui-corner-all' title='Open - Close'>").append($("<span id='h_drpDown' class='ui-icon ui-icon-circle-triangle-n'></span>"))),u=$('<div id="h_divGsthdr" style="padding:4px;" class="hhk-checkinHdr"/>').append($("<span >"+t.patientLabel+": </span>")).append($('<span id="h_hdrFirstName">'+c.find("#h_txtFirstName").val()+" </span>")).append($('<span id="h_hdrLastName">'+c.find("#h_txtLastName").val()+"</span>")).append($('<span">'+(e.patStay?" (staying)":"")+"</span>")).append(d).append($('<div style="clear:both;"/>')).click(function(){"none"===c.css("display")?(c.show("blind"),u.removeClass("ui-corner-all").addClass("ui-corner-top"),$("#h_drpDown").removeClass("ui-icon-circle-triangle-s").addClass("ui-icon-circle-triangle-n")):(c.hide("blind"),u.removeClass("ui-corner-top").addClass("ui-corner-all"),$("#h_drpDown").removeClass("ui-icon-circle-triangle-n").addClass("ui-icon-circle-triangle-s"))}).addClass("ui-widget-header ui-corner-top");c.find("select.bfh-countries").each(function(){var e=$(this);e.bfhcountries(e.data())}),c.find("select.bfh-states").each(function(){var e=$(this);e.bfhstates(e.data())}),c.find("#h_phEmlTabs").tabs(),void 0!==e.idPsg&&0!=e.idPsg||(c.find("#h_phEmlTabs").tabs("option","active",1),c.find("#h_phEmlTabs").tabs("option","disabled",[0])),t.patSection=!0,$(".patientRelch").length>0&&$('.patientRelch option[value="slf"]').remove(),p.children().remove().end().append(u).append(c).show("scale, horizontal"),""!==c.find("#h_txtLastName").val()&&$("#h_drpDown").click()}$("#"+e.idPrefix+"selPatRel").change(),void 0!==e.ratelist&&(t.rateList=e.ratelist),void 0!==e.rooms&&(t.resources=e.rooms),void 0!==e.vfee&&(t.visitFees=e.vfee),void 0!==e.resc&&($("#rescList").children().remove().end().append($(e.resc)).show(),void 0!==e.resv&&($("#resvStatus").children().remove().end().append($(e.resv)).show(),$(".hhk-viewResvActivity").click(function(){$.post("ws_ckin.php",{cmd:"viewActivity",rid:$(this).data("rid")},function(e){if((e=$.parseJSON(e)).error)return e.gotopage&&window.open(e.gotopage,"_self"),void flagAlertMessage(e.error,!0);e.activity&&($("div#submitButtons").hide(),$("#activityDialog").children().remove(),$("#activityDialog").append($(e.activity)),$("#activityDialog").dialog("open"))})}),$("input.hhk-constraintsCB").change(function(){updateRoomChooser(t.idReserv,$("#spnNumGuests").text(),$("#pggstDate").val(),$("#pggstCoDate").val())}),$("#btnShowCnfrm").button(),$("#btnShowCnfrm").click(function(){$.post("ws_ckin.php",{cmd:"confrv",rid:$(this).data("rid"),amt:$("#spnAmount").text(),eml:"0"},function(e){if((e=$.parseJSON(e)).error)return e.gotopage&&window.open(e.gotopage,"_self"),void flagAlertMessage(e.error,!0);e.confrv&&($("div#submitButtons").hide(),$("#frmConfirm").children().remove(),$("#frmConfirm").html(e.confrv).append($('<div style="padding-top:10px;" class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><span>Email Address </span><input type="text" id="confEmail" value="'+e.email+'"/></div>')),$("#confirmDialog").dialog("open"))})})),$("#btnDone").val("Save")),void 0!==e.rate&&($("#rate").children().remove().end().append($(e.rate)).show(),$("#btnFapp").button(),$("#btnFapp").click(function(){getIncomeDiag(t.idReserv)}),setupRates(t,$("#selResource").val()),$("#h_drpDown, #divhospHdr, #"+e.idPrefix+"drpDown").click(),$("#selResource").change(function(){$("#selRateCategory").change(),"Not Suitable"===$("option:selected",this).parent()[0].label?$("#hhkroomMsg").text("Not Suitable").show():$("#hhkroomMsg").hide()})),void 0!==e.pay&&($("#pay").children().remove().end().append($(e.pay)).show(),$("#paymentDate").datepicker({yearRange:"-1:+01",numberOfMonths:1}),setupPayments(e.rooms,$("#selResource"),$("#selRateCategory"))),e.adguests&&($("#resvGuest").children().remove().end().append($(e.adguests)).show(),$(".hhk-addResv, .hhk-delResv").button(),e.static&&"y"===e.static||(createAutoComplete($("#txtAddGuest"),3,{cmd:"role"},additionalGuest),createAutoComplete($("#txtAddPhone"),5,{cmd:"role"},additionalGuest),$(".hhk-addResv").click(function(){additionalGuest({id:$(this).data("id"),value:$(this).data("name")})}),$(".hhk-delResv").click(function(){delAdditionalGuest({id:$(this).data("id"),value:$(this).data("name")}),$(this).parent("td").next().children("a").removeClass("ui-state-highlight"),$(this).remove()}))),e.vehicle&&($("#vehicle").children().remove().end().append($(e.vehicle)).show(),$("#cbNoVehicle").change(function(){this.checked?$("#tblVehicle").hide("scale, horizontal"):$("#tblVehicle").show("scale, horizontal")}),$("#cbNoVehicle").change(),$("#btnNextVeh").button(),t.nextVeh=1,$("#btnNextVeh").click(function(){$("#trVeh"+t.nextVeh).show("fade"),++t.nextVeh>4&&$("#btnNextVeh").hide("fade")})),e.numGuests&&$("#spnNumGuests").text(e.numGuests),e.rvstatus?$("#spnStatus").text(" - "+e.rvstatus).show():$("#spnStatus").text("").hide(),e.showRegBtn&&"y"===e.showRegBtn?$("#btnCkinForm").data("rid",t.idReserv).show():$("#btnCkinForm").hide(),t.idReserv>0?$("input#btnDelete").show():$("input#btnDelete").hide(),e.resun&&flagAlertMessage(e.resun,!0),$(".ckbdate").datepicker({yearRange:"-99:+00",changeMonth:!0,changeYear:!0,autoSize:!0,maxDate:0,dateFormat:"M d, yy"});createZipAutoComplete($("input.hhk-zipsearch"),"ws_admin.php",void 0)}function loadResources(e,t){"use strict";var a=reserv;hideAlertMessage();try{e=$.parseJSON(e)}catch(e){flagAlertMessage(e.message,!0),$("form#form1").remove()}if(e.error&&(e.gotopage&&window.open(e.gotopage,"_self"),flagAlertMessage(e.error,!0),$("form#form1").remove()),e.xfer){var i=$("#xform");if(i.children("input").remove(),i.prop("action",e.xfer),e.paymentId&&""!=e.paymentId)i.append($('<input type="hidden" name="PaymentID" value="'+e.paymentId+'"/>'));else{if(!e.cardId||""==e.cardId)return void flagAlertMessage("PaymentId and CardId are missing!",!0);i.append($('<input type="hidden" name="CardID" value="'+e.cardId+'"/>'))}i.submit()}e.warning&&flagAlertMessage(e.warning,!0),e.idReserv&&(a.idReserv=parseInt(e.idReserv,10)),"Saving >>>>"===$("#btnDone").val()&&$("#btnDone").val(t),e&&injectSlot(e),e.resCh?resvPicker(e,$("#resDialog")):e.receipt&&""!==e.receipt&&showReceipt("#pmtRcpt",e.receipt,"Payment Receipt")}function loadGuest(e,t,a,i){"use strict";var r=reserv,s=e.id,d="pg";if(hideAlertMessage(),t&&""!=t||(t="g"),"p"==t&&(d="h_"),r.role=t,r.patStaying=i,s>0)for(var o=0;o<r.members.length;o++){var n=r.members[o];if(n.idName==s&&"p"===t&&!1===n.isPatient)return flagAlertMessage("To make the guest also the "+r.patientLabel+", set this Guest's "+r.patientLabel+" Relationship to "+r.patientLabel+".",!0),void $("#pgselPatRel").addClass("ui-state-highlight")}var l={cmd:"getResv",id:s,rid:r.idReserv,idPrefix:d,idPsg:a,role:t,patStay:i};$.getJSON("ws_ckin.php",l,function(e){var t=reserv;if(e){if(e.error)return e.gotopage&&window.open(e.gotopage,"_self"),void flagAlertMessage(e.error,!0);if(e.warning&&flagAlertMessage(e.warning,!0),e.resCh)resvPicker(e,$("#resDialog"));else{if(e.choosePsg)return t.idGuest=e.idGuest,void psgChooser(e.choosePsg);if(injectSlot(e),e.static&&"y"===e.static)$("input#btnDone").hide(),$("input#btnDelete").show();else{if($("input#btnDone").show(),t.idReserv>0&&$("input#btnDelete").show(),e.patient&&""!==e.patient){var a=$("div#patientSection #h_idName").val(),i=parseInt(""==a?0:a,10);if(!1===isNaN(i)&&i>-1){var r=new Mpanel("h_",i);r.isPatient=!0,r.isPG=!1,t.members.push(r)}}if(null!==e.idName){var s=parseInt(e.idName,10);if(!1===isNaN(s)&&s>-1){var d=new Mpanel(e.idPrefix,s);d.isPG=!0,t.members.push(d)}}}$("input#gstSearch").val(""),$("input#pggstDate").focus()}}else alert("Bad Reply from Server")})}function psgChooser(e){"use strict";var t=reserv;$("#psgDialog").children().remove().end().append($(e)).dialog("option","buttons",{Open:function(){if(0==$("#cbpstayy").prop("checked")&&0==$("#cbpstayn").prop("checked"))return $("#spnstaymsg").text("Choose Yes or No"),void $(".pstaytd").addClass("ui-state-highlight");t.idPsg=$("#psgDialog input[name=cbselpsg]:checked").val(),loadGuest({id:t.idGuest},t.role,t.idPsg,$("#cbpstayy").prop("checked")),$("#psgDialog").dialog("close")},Cancel:function(){$("#gstSearch").val(""),$("#psgDialog").dialog("close")}}).dialog("option","title","Patient Details").dialog("open")}function resvPicker(e,t){"use strict";var a=reserv,i={};t.children().remove(),t.append($(e.resCh)),t.children().find("input:button").button(),t.children().find(".hhk-checkinNow").click(function(){window.open("CheckIn.php?rid="+$(this).data("rid")+"&gid="+e.id,"_self")}),e.addtnlRoom&&(i["Additional Room"]=function(){var t={cmd:"addResv",id:e.id,rid:0,psg:e.idPsg,arr:e.arr,dep:e.dep,addRoom:!0};$.post("ws_ckin.php",t,function(e){try{e=$.parseJSON(e)}catch(e){return void alert("Parser error - "+e.message)}{if(e)return e.error?(e.gotopage&&window.open(e.gotopage,"_self"),void flagAlertMessage(e.error,!0)):($("#txtAddGuest").val(""),e.newRoom&&e.newRoom>1?($("#submitButtons").hide(),void flagAlertMessage('<a href="Referral.php?rid='+e.newRoom+'">View '+e.newButtonLabel+"</a>",!1)):void 0);alert("Bad Reply from Server")}}),$(this).dialog("close")}),e.newPatient&&(i[e.newPatient]=function(){$(this).dialog("close"),a.idGuest=e.idGuest,psgChooser(e.newPsgChooser)}),e.newButtonLabel&&(i[e.newButtonLabel]=function(){a.idReserv=-1,$(this).dialog("close"),loadGuest(e,a.role,e.idPsg,a.patStaying)}),i.Exit=function(){$(this).dialog("close")},t.dialog("option","buttons",i),t.dialog("option","title",e.title),t.dialog("open")}function verifyDone(e){"use strict";var t=e,a=!1,i=$("#selResvStatus");if(hideAlertMessage(),"c"===i.val()||"td"===i.val()||"ns"===i.val()||"h"===i.val())return!0;if(0===t.members.length)return flagAlertMessage("Use 'Add Guest' to enter a Guest.",!0,0),$("#gstSearch").addClass("ui-state-highlight").show("blind"),!1;if(""!==$("#selResource").val()&&"0"!==$("#selResource").val()||(i.val("w"),i.change()),$("#hospitalSection").find(".ui-state-error").each(function(){$(this).removeClass("ui-state-error")}),$("#selHospital").length>0&&$("#hospitalSection:visible").length>0&&""==$("#selHospital").val())return $("#selHospital").addClass("ui-state-error"),flagAlertMessage("Select a hospital.",!0,0),$("#divhospDetail").show("blind"),$("#divhospHdr").removeClass("ui-corner-all").addClass("ui-corner-top"),!1;$("#divhospDetail").hide("blind"),$("#divhospHdr").removeClass("ui-corner-top").addClass("ui-corner-all"),$("#guestAccordion").find(".ui-state-error").each(function(){$(this).removeClass("ui-state-error")});for(var r=0;r<e.members.length;r++){var s,d,o=e.members[r],n=$("#"+o.idPrefix+"memMsg");if(n.text(""),"h_"===o.idPrefix&&(a=!0,e.patientBirthDate)){if($("#h_txtBirthDate").removeClass("ui-state-error"),""===$("#h_txtBirthDate").val())return flagAlertMessage(t.patientLabel+" needs a birth date.",!0),n.text("Birth date"),$("#h_txtBirthDate").addClass("ui-state-error"),!1;if(new Date($("#h_txtBirthDate").val())>new Date)return flagAlertMessage("The "+t.patientLabel+" birth date cannot be in the future.",!0),n.text("Birth date"),$("#h_txtBirthDate").addClass("ui-state-error"),!1}var l=!1,p=$("span#"+o.idPrefix+"hdrFirstName").text()+" "+$("span#"+o.idPrefix+"hdrLastName").text();if(""==$("#"+o.idPrefix+"txtFirstName").val()&&($("#"+o.idPrefix+"txtFirstName").addClass("ui-state-error"),l=!0),""==$("#"+o.idPrefix+"txtLastName").val()&&($("#"+o.idPrefix+"txtLastName").addClass("ui-state-error"),l=!0),l)return flagAlertMessage("Enter a first and last name for the "+("h_"===o.idPrefix?t.patientLabel:"Primary Guest")+".",!0),n.text("Incomplete Name"),$("#"+o.idPrefix+"divGstpnl").show("blind"),$("#"+o.idPrefix+"divGsthdr").removeClass("ui-corner-all").addClass("ui-corner-top"),!1;if(!1===$("#"+o.idPrefix+"incomplete").prop("checked")&&($("."+o.idPrefix+"hhk-addr-val").each(function(){""===$(this).val()&&($(this).hasClass("bfh-states")||(n.text("Incomplete Address"),$(this).addClass("ui-state-error"),l=!0))}),l))return flagAlertMessage(("h_"===o.idPrefix?t.patientLabel:"Primary Guest")+" ("+p+") is missing some or all of their address.",!0),$("#"+o.idPrefix+"divGstpnl").show("blind"),$("#"+o.idPrefix+"divGsthdr").removeClass("ui-corner-all").addClass("ui-corner-top"),!1;l=!1;var c="",u=/^([\(]{1}[0-9]{3}[\)]{1}[\.| |\-]{0,1}|^[0-9]{3}[\.|\-| ]?)?[0-9]{3}(\.|\-| )?[0-9]{4}$/;if($('.hhk-phoneInput[id^="'+o.idPrefix+'txtPhone"]').each(function(){""!=$.trim($(this).val())&&!1===u.test($(this).val())&&($(this).addClass("ui-state-error"),c+=("h_"===o.idPrefix?t.patientLabel:"Primary Guest")+" ("+p+") has an invalid phone number.  ",l=!0)}),l)return flagAlertMessage(c,!0),$("#"+o.idPrefix+"divGstpnl").show("blind"),$("#"+o.idPrefix+"divGsthdr").removeClass("ui-corner-all").addClass("ui-corner-top"),$("#"+o.idPrefix+"phEmlTabs").tabs("option","active",1),!1;if(""===$("#"+o.idPrefix+"selPatRel").val())return $("#"+o.idPrefix+"selPatRel").addClass("ui-state-error"),n.text("Set Primary Guest - "+t.patientLabel+" Relationship"),flagAlertMessage("Primary Guest ("+p+") is missing their relationship to the "+t.patientLabel+".",!0),$("#"+o.idPrefix+"divGstpnl").show("blind"),$("#"+o.idPrefix+"divGsthdr").removeClass("ui-corner-all").addClass("ui-corner-top"),!1;if("slf"===$("#"+o.idPrefix+"selPatRel").val()&&(a=!0,e.patientBirthDate&&($("#"+o.idPrefix+"txtBirthDate").removeClass("ui-state-error"),""===$("#"+o.idPrefix+"txtBirthDate").val())))return flagAlertMessage(t.patientLabel+" needs a birth date.",!0),n.text("Birth date"),$("#"+o.idPrefix+"txtBirthDate").addClass("ui-state-error"),!1;if($("#"+o.idPrefix+"gstDate").length>0){if(""==$("#"+o.idPrefix+"gstDate").val())return $("#"+o.idPrefix+"gstDate").addClass("ui-state-error"),n.text("Enter guest check in date."),flagAlertMessage(("h_"===o.idPrefix?t.patientLabel:"Primary Guest")+" ("+p+") is missing their check-in date.",!0),!1;if(s=new Date($("#"+o.idPrefix+"gstDate").val()),isNaN(s.getTime()))return $("#"+o.idPrefix+"gstDate").addClass("ui-state-error"),n.text("Guest check-in date error."),flagAlertMessage(("h_"===o.idPrefix?t.patientLabel:"Primary Guest")+" ("+p+") is missing their check-in date.",!0),!1}if($("#"+o.idPrefix+"gstCoDate").length>0){if(""==$("#"+o.idPrefix+"gstCoDate").val())return $("#"+o.idPrefix+"gstCoDate").addClass("ui-state-error"),n.text("Enter guest check out date."),flagAlertMessage(("h_"===o.idPrefix?t.patientLabel:"Primary Guest")+" ("+p+") is missing their Expected Departure date.",!0),!1;if(d=new Date($("#"+o.idPrefix+"gstCoDate").val()),isNaN(d.getTime()))return $("#"+o.idPrefix+"gstCoDate").addClass("ui-state-error"),n.text("Guest Expected Departure date error."),flagAlertMessage(("h_"===o.idPrefix?t.patientLabel:"Primary Guest")+" ("+p+") is missing their Expected Departure date",!0),!1;if(s>d)return $("#"+o.idPrefix+"gstDate").addClass("ui-state-error"),n.text("Check in date is after check out date."),flagAlertMessage(("h_"===o.idPrefix?t.patientLabel:"Primary Guest")+" ("+p+") check in date is after their expected departure date.",!0),!1}$("#"+o.idPrefix+"divGstpnl").hide("blind"),$("#"+o.idPrefix+"divGsthdr").removeClass("ui-corner-top").addClass("ui-corner-all")}return!1!==a||(flagAlertMessage("A "+t.patientLabel+" is not selected",!0),!1)}function Mpanel(e,t){var a=this;a.idPrefix=e,a.isPG,a.isPatient,a.idName=t}function Reserv(){"use strict";var e=this;e.idReserv,e.members=[],e.idPsg=0,e.adrPurpose="1",e.gpnl,e.Total=0,e.rateList,e.isFixed,e.resources,e.visitFees,e.patStaying,e.patAsGuest,e.role,e.patSection}$(document).ready(function(){"use strict";function e(e){loadGuest(e,"g",a.idPsg,a.patStaying)}function t(e){a.patAsGuest?(void 0===e.fullName&&(e.fullName="the "+a.patientLabel),$("#hhk-patPromptQuery").text("Is "+e.fullName+" staying the FIRST night (or longer)?"),$("#patientPrompt").dialog("option","buttons",{Yes:function(){loadGuest(e,"p",a.idPsg,!0),$("#patientPrompt").dialog("close")},No:function(){loadGuest(e,"p",a.idPsg,!1),$("#patientPrompt").dialog("close")}}).dialog("open")):loadGuest(e,"p",a.idPsg,!1)}var a=reserv;$(window).bind("beforeunload",function(){if("Saving >>>>"!==$("#btnDone").val()){var e=!1;return $("#guestAccordion").find("input[type='text']").not(".ignrSave").each(function(){$(this).val()!==$(this).prop("defaultValue")&&(e=!0)}),$("#rescList").find("input[type='checkbox']").each(function(){$(this).prop("checked")!=$(this).prop("defaultChecked")&&(e=!0)}),$("#rescList").find("select").not(".ignrSave").each(function(){$(this).children("option").each(function(){this.defaultSelected!=this.selected&&(e=!0)})}),!0===e?"You have unsaved changes.":void 0}}),$("#btnDone, #btnCkinForm, #btnDelete").button(),$("#btnCkinForm").click(function(){$(this).data("rid")>0&&window.open("ShowRegForm.php?rid="+$(this).data("rid"),"_blank")}),$("#btnDelete").click(function(){if("Deleting >>>>"!==$(this).val()&&confirm("Delete this "+resvTitle+"?")){var e="&cmd=delResv&rid="+a.idReserv;$(this).val("Deleting >>>>"),$.post("ws_ckin.php",e,function(e){try{e=$.parseJSON(e)}catch(e){flagAlertMessage(e.message,!0),$("form#form1").remove()}e.error&&(e.gotopage&&window.open(e.gotopage,"_self"),flagAlertMessage(e.error,!0),$("form#form1").remove()),$("#btnDelete").val("Delete"),e.warning&&flagAlertMessage(e.warning,!0),e.result&&($("form#form1").remove(),flagAlertMessage(e.result+' <a href="register.php">Continue</a>',!0))})}}),$("#pmtRcpt").dialog({autoOpen:!1,resizable:!0,modal:!0,title:"Payment Receipt"}),$("#confirmDialog").dialog({autoOpen:!1,resizable:!0,width:850,modal:!0,title:"Confirmation Form",close:function(){$("div#submitButtons").show(),$("#frmConfirm").children().remove()},buttons:{"Download MS Word":function(){var e=$("form#frmConfirm");e.append($('<input name="hdnCfmRid" type="hidden" value="'+$("#btnShowCnfrm").data("rid")+'"/>')),e.submit()},"Send Email":function(){$.post("ws_ckin.php",{cmd:"confrv",rid:$("#btnShowCnfrm").data("rid"),eml:"1",eaddr:$("#confEmail").val(),amt:$("#spnAmount").text(),notes:$("#tbCfmNotes").val()},function(e){(e=$.parseJSON(e)).gotopage&&window.open(e.gotopage,"_self"),flagAlertMessage(e.mesg,!0)}),$(this).dialog("close")},Cancel:function(){$(this).dialog("close")}}}),$("#faDialog").dialog({autoOpen:!1,resizable:!0,width:650,modal:!0,title:"Income Chooser",close:function(e,t){$("div#submitButtons").show()},open:function(e,t){$("div#submitButtons").hide()},buttons:{Save:function(){$.post("ws_ckin.php",$("#formf").serialize()+"&cmd=savefap&rid="+a.idReserv,function(e){try{e=$.parseJSON(e)}catch(e){return void alert("Bad JSON Encoding")}if(e.gotopage&&window.open(e.gotopage,"_self"),e.rstat&&1==e.rstat){var t=$("#selRateCategory");e.rcat&&""!=e.rcat&&t.length>0&&(t.val(e.rcat),t.change())}}),$(this).dialog("close")},Exit:function(){$(this).dialog("close")}}}),$("#psgDialog").dialog({autoOpen:!1,resizable:!0,width:500,modal:!0,title:a.patientLabel+" Support Group Chooser",close:function(e,t){$("div#submitButtons").show()},open:function(e,t){$("div#submitButtons").hide()}}),$("#activityDialog").dialog({autoOpen:!1,resizable:!0,width:900,modal:!0,title:"Reservation Activity Log",close:function(e,t){$("div#submitButtons").show()},open:function(e,t){$("div#submitButtons").hide()},buttons:{Exit:function(){$(this).dialog("close")}}}),$("#resDialog").dialog({autoOpen:!1,resizable:!0,width:900,modal:!0,title:"Reservtion Chooser",buttons:{Exit:function(){$(this).dialog("close")}}}),$("#patientPrompt").dialog({autoOpen:!1,resizable:!0,width:470,modal:!0,title:""}),""!==pmtMkup&&$("#paymentMessage").html(pmtMkup).show("pulsate",{},400),""!==rctMkup&&showReceipt("#pmtRcpt",rctMkup,"Payment Receipt"),$.datepicker.setDefaults({yearRange:"-0:+02",changeMonth:!0,changeYear:!0,autoSize:!0,dateFormat:"M d, yy"}),$("div#guestAccordion, div#patientSection").on("click",".hhk-addrCopy",function(){var e=$(this).attr("name"),t="h_";if(a.addr&&""!=a.addr.adraddress1&&$("#"+e+"adraddress1"+a.adrPurpose).val()!=a.addr.adraddress1)return $("#"+e+"adraddress1"+a.adrPurpose).val(a.addr.adraddress1),$("#"+e+"adraddress2"+a.adrPurpose).val(a.addr.adraddress2),$("#"+e+"adrcity"+a.adrPurpose).val(a.addr.adrcity),$("#"+e+"adrcounty"+a.adrPurpose).val(a.addr.adrcounty),$("#"+e+"adrstate"+a.adrPurpose).val(a.addr.adrstate),$("#"+e+"adrcountry"+a.adrPurpose).val(a.addr.adrcountry),void $("#"+e+"adrzip"+a.adrPurpose).val(a.addr.adrzip);"h_"===e&&(t="pg"),""!=$("#"+t+"adrcity1").val()&&($("#"+e+"adraddress1"+a.adrPurpose).val($("#"+t+"adraddress11").val()),$("#"+e+"adraddress2"+a.adrPurpose).val($("#"+t+"adraddress21").val()),$("#"+e+"adrcity"+a.adrPurpose).val($("#"+t+"adrcity1").val()),$("#"+e+"adrcounty"+a.adrPurpose).val($("#"+t+"adrcounty1").val()),$("#"+e+"adrstate"+a.adrPurpose).val($("#"+t+"adrstate1").val()),$("#"+e+"adrcountry"+a.adrPurpose).val($("#"+t+"adrcountry1").val()),$("#"+e+"adrzip"+a.adrPurpose).val($("#"+t+"adrzip1").val()))}),$("div#guestAccordion, div#patientSection").on("click",".hhk-addrErase",function(){var e=$(this).attr("name");$("#"+e+"adraddress11").val(""),$("#"+e+"adraddress21").val(""),$("#"+e+"adrcity1").val(""),$("#"+e+"adrcounty1").val(""),$("#"+e+"adrstate1").val(""),$("#"+e+"adrcountry1").val(""),$("#"+e+"adrzip1").val(""),$("#"+e+"adrbad1").prop("checked",!1)}),verifyAddrs("div#guestAccordion, #hospitalSection, div#patientSection"),$("div#guestAccordion, div#patientSection").on("change","input.hhk-lastname",function(){$("span#"+$(this).data("prefix")+"hdrLastName").text(" "+$(this).val())}),$("div#guestAccordion, div#patientSection").on("change","input.hhk-firstname",function(){$("span#"+$(this).data("prefix")+"hdrFirstName").text(" "+$(this).val())}),$("div#hospitalSection").on("click",".hhk-agentSearch, .hhk-docSearch",function(){$("#txtAgentSch").val(""),$("#txtDocSch").val("")}),$("div#hospitalSection").on("change","#selHospital, #selAssoc",function(){var e=$("#selAssoc").find("option:selected").text();""!=e&&(e+="/ "),$("span#spnHospName").text(e+$("#selHospital").find("option:selected").text())}),$("#selHospital").change(),$("#closeDP").click(function(){$("#dtpkrDialog").hide()}),$("#btnDone").click(function(){if("Saving >>>>"!==$(this).val()&&($("#divPayMessage").remove(),!0===verifyDone(a))){var e=$(this).val(),t="&cmd=makeResv&idPsg="+a.idPsg+"&rid="+a.idReserv+"&patStay="+a.patStaying;$(this).val("Saving >>>>"),$.post("ws_ckin.php",$("#form1").serialize()+t,function(t){loadResources(t,e)})}}),createAutoComplete($("#gstSearch"),3,{cmd:"role",gp:"1"},e),createAutoComplete($("#gstphSearch"),4,{cmd:"role",gp:"1"},e),$("#gstSearch").keypress(function(e){$(this).removeClass("ui-state-highlight")}),createAutoComplete($("#h_Search"),3,{cmd:"role",gp:"1"},t),createAutoComplete($("#h_phSearch"),4,{cmd:"role",gp:"1"},t),a.gpnl&&""!==a.gpnl&&loadGuest({id:a.gpnl},"g",a.idPsg),$("#gstSearch").focus()});
